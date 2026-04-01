<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetLog extends Model
{
    protected $fillable = [
        'target_user_id',
        'reset_by_user_id',
        'temporary_password_issued',
    ];

    protected function casts(): array
    {
        return [
            'temporary_password_issued' => 'boolean',
        ];
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function resetBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reset_by_user_id');
    }
}
