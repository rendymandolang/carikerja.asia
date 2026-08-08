<?php

namespace App\Services;

use App\Models\JobPost;
use Carbon\CarbonInterface;

class JobPostingSchemaService
{
    public function for(JobPost $job): array
    {
        $company = $job->company;
        $description = collect([$job->description, $job->requirements, $job->benefits])
            ->filter()
            ->map(fn (string $section) => '<p>'.nl2br(e($section)).'</p>')
            ->implode('');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $description,
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => $company->company_name,
                'value' => (string) $job->id,
            ],
            'datePosted' => $job->published_at->toAtomString(),
            'validThrough' => $this->validThrough($job)->toAtomString(),
            'employmentType' => $this->employmentType($job->employment_type),
            'hiringOrganization' => array_filter([
                '@type' => 'Organization',
                'name' => $company->company_name,
                'sameAs' => $company->website ?: route('companies.show', $company),
            ]),
            'directApply' => true,
            'url' => route('jobs.show', $job),
        ];

        $this->addLocation($schema, $job);
        $this->addSalary($schema, $job);

        return $schema;
    }

    private function validThrough(JobPost $job): CarbonInterface
    {
        $dates = collect([
            $job->application_deadline?->copy()->endOfDay(),
            $job->confirmation_due_at,
        ])->filter();

        return $dates->isNotEmpty()
            ? $dates->sortBy(fn (CarbonInterface $date) => $date->getTimestamp())->first()
            : $job->published_at->copy()->addDays((int) config('hiring.job_confirmation_days', 30));
    }

    private function employmentType(string $type): string
    {
        return [
            'full_time' => 'FULL_TIME',
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'internship' => 'INTERN',
            'freelance' => 'CONTRACTOR',
        ][$type] ?? 'OTHER';
    }

    private function addLocation(array &$schema, JobPost $job): void
    {
        if (in_array($job->work_arrangement, ['remote', 'hybrid'], true)) {
            $schema['jobLocationType'] = 'TELECOMMUTE';
            $schema['applicantLocationRequirements'] = [
                '@type' => 'Country',
                'name' => $job->country ?: 'Indonesia',
            ];
        }

        if ($job->work_arrangement !== 'remote' || $job->city || $job->province || $job->location) {
            $schema['jobLocation'] = [
                '@type' => 'Place',
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $job->location,
                    'addressLocality' => $job->city,
                    'addressRegion' => $job->province,
                    'addressCountry' => $job->country === 'Indonesia' || blank($job->country) ? 'ID' : $job->country,
                ]),
            ];
        }
    }

    private function addSalary(array &$schema, JobPost $job): void
    {
        if (! $job->salary_min && ! $job->salary_max) {
            return;
        }

        $schema['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => $job->currency ?: 'IDR',
            'value' => array_filter([
                '@type' => 'QuantitativeValue',
                'minValue' => $job->salary_min ? (float) $job->salary_min : null,
                'maxValue' => $job->salary_max ? (float) $job->salary_max : null,
                'unitText' => 'MONTH',
            ], fn ($value) => $value !== null),
        ];
    }
}
