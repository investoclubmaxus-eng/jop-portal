<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEWED = 'interviewed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIRED = 'hired';

    public const CRM_PENDING = 'pending';
    public const CRM_TRANSFERRED = 'transferred';
    public const CRM_COMPLETED = 'completed';
    public const CRM_FAILED = 'failed';

    protected $fillable = [
        'application_code',
        'job_opening_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'current_location',
        'highest_qualification',
        'total_experience_years',
        'current_ctc',
        'expected_ctc',
        'resume_path',
        'source',
        'status',
        'screening_remarks',
        'submitted_at',
        'crm_transfer_status',
        'crm_application_id',
        'transferred_at',
        'crm_transfer_error',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'transferred_at' => 'datetime',
            'total_experience_years' => 'decimal:1',
            'current_ctc' => 'decimal:2',
            'expected_ctc' => 'decimal:2',
        ];
    }

    /**
     * Generate unique candidate application code.
     */
    public static function generateApplicationCode(): string
    {
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $count = static::count() + 1;

        return sprintf('APP-%s-%04d-%s', $year, $count, $random);
    }

    /**
     * Get the applicant full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scopes
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereDate('submitted_at', Carbon::today())
              ->orWhere(function (Builder $sub) {
                  $sub->whereNull('submitted_at')
                      ->whereDate('created_at', Carbon::today());
              });
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function scopeShortlisted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SHORTLISTED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeTransferredToCrm(Builder $query): Builder
    {
        return $query->whereIn('crm_transfer_status', [
            self::CRM_TRANSFERRED,
            self::CRM_COMPLETED,
            'success',
        ]);
    }

    /**
     * Relationships
     */
    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id')->latest('created_at');
    }
}
