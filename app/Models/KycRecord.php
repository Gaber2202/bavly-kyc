<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KycRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_name',
        'client_full_name',
        'age',
        'passport_job_title',
        'other_job_title',
        'service_type',
        'assigned_to',
        'has_bank_statement',
        'available_balance',
        'expected_balance',
        'marital_status',
        'children_count',
        'has_relatives_abroad',
        'nationality_type',
        'nationality',
        'residency_status',
        'governorate',
        'consultation_method',
        'email',
        'phone_number',
        'whatsapp_number',
        'previous_rejected',
        'rejection_numbers',
        'rejection_reason',
        'rejection_country',
        'has_previous_visas',
        'previous_visa_countries',
        'recommendation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'children_count' => 'integer',
            'available_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->can_view_all_kyc) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('client_full_name', 'like', $like)
                ->orWhere('phone_number', 'like', $like)
                ->orWhere('whatsapp_number', 'like', $like);
        });
    }
}
