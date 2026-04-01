<?php

namespace App\Support;

final class KycOptions
{
    public const SERVICE_TYPES = ['بافلي', 'ترانس روفر', 'أخرى'];

    public const YES_NO = ['نعم', 'لا'];

    public const CONSULTATION_METHODS = ['مقابلة', 'فون', 'فيديوكول'];

    public const NATIONALITY_TYPES = ['مصري', 'غير مصري'];

    public const MARITAL_STATUSES = ['أعزب', 'متزوج', 'مطلق', 'أرمل'];

    public const STATUSES = [
        'جديد',
        'قيد المراجعة',
        'قيد الاستكمال',
        'مكتمل',
        'مرفوض',
    ];

    public static function assignedToForService(string $serviceType): ?string
    {
        return match ($serviceType) {
            'بافلي' => 'أحمد الشيخ',
            'ترانس روفر' => 'محمود الشيخ',
            default => null,
        };
    }
}
