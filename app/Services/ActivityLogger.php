<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $properties
     */
    public static function log(?User $user, string $action, ?Model $subject = null, ?array $properties = null): void
    {
        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => self::sanitizeProperties($properties),
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $properties
     * @return array<string, mixed>|null
     */
    private static function sanitizeProperties(?array $properties): ?array
    {
        if ($properties === null) {
            return null;
        }

        foreach (array_keys($properties) as $key) {
            $k = Str::lower((string) $key);
            if ($k === 'password' || Str::contains($k, 'password')) {
                unset($properties[$key]);
            }
        }

        return $properties;
    }
}
