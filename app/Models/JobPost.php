<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by_user_id',
        'title',
        'slug',
        'department',
        'location',
        'city',
        'province',
        'country',
        'employment_type',
        'work_arrangement',
        'salary_min',
        'salary_max',
        'currency',
        'description',
        'requirements',
        'benefits',
        'application_deadline',
        'status',
        'published_at',
        'last_confirmed_at', 'confirmation_due_at', 'auto_paused_at', 'closed_at',
        'closure_type', 'closed_reason', 'report_count',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'application_deadline' => 'date',
        'published_at' => 'datetime',
        'last_confirmed_at' => 'datetime', 'confirmation_due_at' => 'datetime',
        'auto_paused_at' => 'datetime', 'closed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function reports()
    {
        return $this->hasMany(JobReport::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeOpenForApplication(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('application_deadline')
                ->orWhereDate('application_deadline', '>=', now()->toDateString());
        });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function isOpenForApplication(): bool
    {
        return ! $this->confirmation_due_at?->isPast() && ($this->application_deadline === null
            || $this->application_deadline->isToday()
            || $this->application_deadline->isFuture());
    }

    public function isConfirmationOverdue(): bool
    {
        return $this->confirmation_due_at?->isPast() ?? false;
    }

    public function salaryRangeLabel(): string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return 'Salary not disclosed';
        }

        $min = $this->salary_min ? number_format((float) $this->salary_min, 0, ',', '.') : '-';
        $max = $this->salary_max ? number_format((float) $this->salary_max, 0, ',', '.') : '-';

        return "{$this->currency} {$min} - {$max}";
    }
}
