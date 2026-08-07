<?php

namespace App\Services;

use App\Jobs\SendMarketingCampaignJob;
use App\Models\EmailUnsubscribe;
use App\Models\MarketingCampaign;
use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\MarketingCampaignNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Throwable;

class MarketingCampaignService
{
    /**
     * @return \Illuminate\Support\Collection<int, array{email: string, name: string, source_type: string, source_id: int|null}>
     */
    public function recipientsFor(string $audience): Collection
    {
        $contacts = collect();

        if (in_array($audience, ['all_contacts', 'candidates'], true)) {
            $contacts = $contacts->merge($this->userContacts('candidate'));
        }

        if (in_array($audience, ['all_contacts', 'recruiters'], true)) {
            $contacts = $contacts->merge($this->userContacts('recruiter'));
        }

        if (in_array($audience, ['all_contacts', 'all_waitlists', 'waitlist_candidates'], true)) {
            $contacts = $contacts->merge($this->waitlistContacts('candidate'));
        }

        if (in_array($audience, ['all_contacts', 'all_waitlists', 'waitlist_recruiters'], true)) {
            $contacts = $contacts->merge($this->waitlistContacts('recruiter'));
        }

        $unsubscribed = EmailUnsubscribe::query()
            ->whereNotNull('unsubscribed_at')
            ->pluck('email')
            ->map(fn (string $email) => EmailUnsubscribe::normalizeEmail($email))
            ->flip();

        return $contacts
            ->map(function (array $contact) {
                $contact['email'] = EmailUnsubscribe::normalizeEmail($contact['email'] ?? '');
                $contact['name'] = trim($contact['name'] ?? '') ?: 'there';

                return $contact;
            })
            ->filter(fn (array $contact) => filter_var($contact['email'], FILTER_VALIDATE_EMAIL))
            ->reject(fn (array $contact) => $unsubscribed->has($contact['email']))
            ->unique('email')
            ->values();
    }

    public function previewCount(string $audience): int
    {
        return $this->recipientsFor($audience)->count();
    }

    public function schedule(MarketingCampaign $campaign, User $sender): MarketingCampaign
    {
        $contacts = $this->recipientsFor($campaign->audience);

        $campaign->forceFill([
            'status' => 'scheduled',
            'recipient_count' => $contacts->count(),
            'sent_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'sent_by_user_id' => $sender->id,
            'queued_at' => null,
            'started_at' => null,
            'sent_at' => null,
            'finished_at' => null,
            'last_error' => null,
        ])->save();

        return $campaign->refresh();
    }

    public function cancelSchedule(MarketingCampaign $campaign): MarketingCampaign
    {
        if (! $campaign->canCancelSchedule()) {
            return $campaign;
        }

        $campaign->forceFill([
            'status' => 'draft',
            'queued_at' => null,
            'started_at' => null,
            'finished_at' => null,
            'last_error' => null,
        ])->save();

        return $campaign->refresh();
    }

    public function queueSend(MarketingCampaign $campaign, ?User $sender = null): MarketingCampaign
    {
        $contacts = $this->recipientsFor($campaign->audience);

        $campaign->recipients()->delete();

        $campaign->forceFill([
            'status' => 'queued',
            'recipient_count' => $contacts->count(),
            'sent_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'sent_by_user_id' => $sender?->id ?: $campaign->sent_by_user_id,
            'queued_at' => now(),
            'started_at' => null,
            'sent_at' => null,
            'finished_at' => null,
            'last_error' => null,
        ])->save();

        SendMarketingCampaignJob::dispatch($campaign->id);

        return $campaign->refresh();
    }

    public function dispatchDueScheduledCampaigns(): int
    {
        $count = 0;

        MarketingCampaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->each(function (MarketingCampaign $campaign) use (&$count) {
                $this->queueSend($campaign, $campaign->sentBy);
                $count++;
            });

        return $count;
    }

    public function send(MarketingCampaign $campaign, ?User $sender = null): MarketingCampaign
    {
        return $this->deliver($campaign, $sender);
    }

    public function deliver(MarketingCampaign $campaign, ?User $sender = null): MarketingCampaign
    {
        $campaign = $campaign->refresh();

        if (! in_array($campaign->status, ['queued', 'sending', 'scheduled'], true)) {
            return $campaign;
        }

        $contacts = $this->recipientsFor($campaign->audience);

        $campaign->recipients()->delete();

        $campaign->forceFill([
            'status' => 'sending',
            'recipient_count' => $contacts->count(),
            'sent_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'sent_by_user_id' => $sender?->id ?: $campaign->sent_by_user_id,
            'started_at' => now(),
            'sent_at' => null,
            'finished_at' => null,
            'last_error' => null,
        ])->save();

        foreach ($contacts as $contact) {
            $recipient = $campaign->recipients()->create([
                'email' => $contact['email'],
                'name' => $contact['name'],
                'source_type' => $contact['source_type'],
                'source_id' => $contact['source_id'],
                'status' => 'queued',
            ]);

            $unsubscribe = EmailUnsubscribe::recordFor($contact['email']);

            if ($unsubscribe->isUnsubscribed()) {
                $recipient->update([
                    'status' => 'skipped',
                    'failure_reason' => 'Recipient unsubscribed.',
                ]);

                continue;
            }

            try {
                Notification::route('mail', $contact['email'])
                    ->notify(new MarketingCampaignNotification(
                        $campaign,
                        $contact['name'],
                        $contact['email'],
                        route('email.unsubscribe', $unsubscribe->token),
                    ));

                $recipient->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (Throwable $exception) {
                $recipient->update([
                    'status' => 'failed',
                    'failure_reason' => $exception->getMessage(),
                ]);
            }
        }

        $sent = $campaign->recipients()->where('status', 'sent')->count();
        $skipped = $campaign->recipients()->where('status', 'skipped')->count();
        $failed = $campaign->recipients()->where('status', 'failed')->count();

        $campaign->forceFill([
            'status' => match (true) {
                $failed > 0 && $sent > 0 => 'sent_with_errors',
                $failed > 0 => 'failed',
                default => 'sent',
            },
            'sent_count' => $sent,
            'skipped_count' => $skipped,
            'failed_count' => $failed,
            'sent_at' => now(),
            'finished_at' => now(),
            'last_error' => $failed > 0 ? 'Some recipients failed. Open recipient log for details.' : null,
        ])->save();

        return $campaign->refresh();
    }

    public function sendTest(MarketingCampaign $campaign, string $email, string $name): void
    {
        $email = EmailUnsubscribe::normalizeEmail($email);
        $unsubscribe = EmailUnsubscribe::recordFor($email);

        Notification::route('mail', $email)
            ->notify(new MarketingCampaignNotification(
                $campaign,
                $name,
                $email,
                route('email.unsubscribe', $unsubscribe->token),
            ));
    }

    private function userContacts(string $role): Collection
    {
        return User::query()
            ->where('role', $role)
            ->where('account_status', 'active')
            ->select(['id', 'name', 'email'])
            ->get()
            ->map(fn (User $user) => [
                'email' => $user->email,
                'name' => $user->name,
                'source_type' => 'user',
                'source_id' => $user->id,
            ]);
    }

    private function waitlistContacts(string $type): Collection
    {
        return Waitlist::query()
            ->where('type', $type)
            ->get()
            ->map(function (Waitlist $waitlist) use ($type) {
                return [
                    'email' => $type === 'candidate' ? $waitlist->email : $waitlist->company_email,
                    'name' => $type === 'candidate'
                        ? ($waitlist->full_name ?: 'Candidate')
                        : ($waitlist->contact_name ?: $waitlist->company_name ?: 'Recruiter'),
                    'source_type' => 'waitlist',
                    'source_id' => $waitlist->id,
                ];
            });
    }
}
