<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'job_code',
        'job_title',
        'department',
        'designation',
        'vacancies',
        'employment_type',
        'experience_min',
        'experience_max',
        'salary_min',
        'salary_max',
        'job_description',
        'required_qualifications',
        'required_skills',
        'published_at',
        'application_deadline',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'published_at' => 'datetime',
            'application_deadline' => 'datetime',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'vacancies' => 'integer',
            'experience_min' => 'integer',
            'experience_max' => 'integer',
        ];
    }

    /**
     * Generate unique job opening code.
     */
    public static function generateJobCode(): string
    {
        $year = date('Y');
        $lastJob = static::withTrashed()
            ->where('job_code', 'like', "JOB-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastJob && preg_match('/JOB-\d{4}-(\d+)/', $lastJob->job_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = static::withTrashed()->count() + 1;
        }

        return sprintf('JOB-%s-%03d', $year, $nextNumber);
    }

    /**
     * Check if job vacancy has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (in_array($this->status, [self::STATUS_EXPIRED, self::STATUS_CLOSED], true)) {
            return true;
        }

        if ($this->application_deadline && $this->application_deadline->isPast() && !$this->application_deadline->isToday()) {
            return true;
        }

        return false;
    }

    /**
     * Formatted salary string.
     */
    public function getSalaryFormattedAttribute(): string
    {
        if ($this->salary_min && $this->salary_max) {
            return '$' . number_format($this->salary_min, 0) . ' - $' . number_format($this->salary_max, 0);
        } elseif ($this->salary_min) {
            return 'From $' . number_format($this->salary_min, 0);
        } elseif ($this->salary_max) {
            return 'Up to $' . number_format($this->salary_max, 0);
        }

        return 'Competitive / Negotiable';
    }

    /**
     * Formatted experience string.
     */
    public function getExperienceFormattedAttribute(): string
    {
        if ($this->experience_min !== null && $this->experience_max !== null) {
            if ($this->experience_min === 0 && $this->experience_max === 0) {
                return 'Fresher (No experience needed)';
            }
            return "{$this->experience_min} - {$this->experience_max} Years";
        } elseif ($this->experience_min !== null) {
            return "Min {$this->experience_min}+ Years";
        } elseif ($this->experience_max !== null) {
            return "Up to {$this->experience_max} Years";
        }

        return 'Any Experience / Freshers Welcome';
    }

    /**
     * Days remaining until application deadline.
     */
    public function getDaysRemainingAttribute(): ?string
    {
        if (!$this->application_deadline) {
            return 'Open indefinitely';
        }

        if ($this->is_expired) {
            return 'Expired';
        }

        if ($this->application_deadline->isToday()) {
            return 'Closes today';
        }

        $days = Carbon::today()->diffInDays($this->application_deadline, false);
        if ($days == 1) {
            return '1 day remaining';
        }

        return "{$days} days remaining";
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_ACTIVE])
            ->where(function (Builder $q) {
                $q->whereNull('application_deadline')
                  ->orWhere('application_deadline', '>=', Carbon::today());
            });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('status', [self::STATUS_EXPIRED, self::STATUS_CLOSED])
              ->orWhere(function (Builder $sub) {
                  $sub->whereNotNull('application_deadline')
                      ->where('application_deadline', '<', Carbon::today());
              });
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_ACTIVE]);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_opening_id');
    }
}
