<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\KycRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'BavlyAdmin!2026Change');

        $admin = User::query()->firstOrNew(['username' => 'superadmin']);
        $admin->fill([
            'name' => 'مشرف النظام',
            'email' => 'admin@example.local',
            'role' => UserRole::Admin,
            'is_active' => true,
            'can_view_all_kyc' => true,
            'can_view_reports' => true,
        ]);
        $admin->password = $adminPassword;
        $admin->must_change_password = false;
        $admin->save();

        $emp1 = User::query()->firstOrNew(['username' => 'ahmed.employee']);
        $emp1->fill([
            'name' => 'أحمد موظف',
            'email' => 'ahmed@example.local',
            'role' => UserRole::Employee,
            'is_active' => true,
            'can_view_all_kyc' => true,
            'can_view_reports' => false,
        ]);
        $emp1->password = Hash::make('Employee!2026Safe');
        $emp1->must_change_password = false;
        $emp1->save();

        $emp2 = User::query()->firstOrNew(['username' => 'sara.employee']);
        $emp2->fill([
            'name' => 'سارة موظفة',
            'email' => null,
            'role' => UserRole::Employee,
            'is_active' => true,
            'can_view_all_kyc' => false,
            'can_view_reports' => true,
        ]);
        $emp2->password = Hash::make('Employee!2026Safe');
        $emp2->must_change_password = false;
        $emp2->save();

        if (KycRecord::query()->count() > 0) {
            return;
        }

        $demo = [
            [
                'employee_name' => $emp1->name,
                'client_full_name' => 'محمد علي حسن',
                'age' => 34,
                'passport_job_title' => 'مدير مبيعات',
                'other_job_title' => null,
                'service_type' => 'بافلي',
                'assigned_to' => 'أحمد الشيخ',
                'has_bank_statement' => 'نعم',
                'available_balance' => 125000.50,
                'expected_balance' => null,
                'marital_status' => 'متزوج',
                'children_count' => 2,
                'has_relatives_abroad' => 'لا',
                'nationality_type' => 'مصري',
                'nationality' => null,
                'residency_status' => null,
                'governorate' => 'القاهرة',
                'consultation_method' => 'فيديوكول',
                'email' => 'client1@example.com',
                'phone_number' => '01001234567',
                'whatsapp_number' => '01001234567',
                'previous_rejected' => 'لا',
                'rejection_numbers' => null,
                'rejection_reason' => null,
                'rejection_country' => null,
                'has_previous_visas' => 'نعم',
                'previous_visa_countries' => 'الإمارات، المملكة العربية السعودية',
                'recommendation' => 'ملف منظم',
                'status' => 'قيد المراجعة',
                'created_by' => $emp1->id,
                'updated_by' => $emp1->id,
            ],
            [
                'employee_name' => $emp2->name,
                'client_full_name' => 'لينا كمال',
                'age' => 28,
                'passport_job_title' => 'مصممة',
                'other_job_title' => null,
                'service_type' => 'ترانس روفر',
                'assigned_to' => 'محمود الشيخ',
                'has_bank_statement' => 'لا',
                'available_balance' => null,
                'expected_balance' => 80000,
                'marital_status' => 'أعزب',
                'children_count' => null,
                'has_relatives_abroad' => 'نعم',
                'nationality_type' => 'غير مصري',
                'nationality' => 'أردنية',
                'residency_status' => 'إقامة عمل',
                'governorate' => null,
                'consultation_method' => 'مقابلة',
                'email' => 'client2@example.com',
                'phone_number' => '01009876543',
                'whatsapp_number' => null,
                'previous_rejected' => 'نعم',
                'rejection_numbers' => 'مرتين',
                'rejection_reason' => 'نقص مستندات',
                'rejection_country' => 'بريطانيا',
                'has_previous_visas' => 'لا',
                'previous_visa_countries' => null,
                'recommendation' => 'يحتاج متابعة',
                'status' => 'جديد',
                'created_by' => $emp2->id,
                'updated_by' => $emp2->id,
            ],
            [
                'employee_name' => $emp1->name,
                'client_full_name' => 'خالد ياسر',
                'age' => 41,
                'passport_job_title' => 'مهندس',
                'other_job_title' => null,
                'service_type' => 'أخرى',
                'assigned_to' => null,
                'has_bank_statement' => 'لا',
                'available_balance' => null,
                'expected_balance' => 95000,
                'marital_status' => 'متزوج',
                'children_count' => 3,
                'has_relatives_abroad' => 'نعم',
                'nationality_type' => 'مصري',
                'nationality' => null,
                'residency_status' => null,
                'governorate' => 'الإسكندرية',
                'consultation_method' => 'فون',
                'email' => 'client3@example.com',
                'phone_number' => '01005551234',
                'whatsapp_number' => '01005551234',
                'previous_rejected' => 'لا',
                'rejection_numbers' => null,
                'rejection_reason' => null,
                'rejection_country' => null,
                'has_previous_visas' => 'لا',
                'previous_visa_countries' => null,
                'recommendation' => 'يتم متابعته أسبوعيًا',
                'status' => 'قيد الاستكمال',
                'created_by' => $emp1->id,
                'updated_by' => $admin->id,
            ],
            [
                'employee_name' => $emp2->name,
                'client_full_name' => 'نور الدين',
                'age' => 31,
                'passport_job_title' => 'محاسب',
                'other_job_title' => null,
                'service_type' => 'بافلي',
                'assigned_to' => 'أحمد الشيخ',
                'has_bank_statement' => 'نعم',
                'available_balance' => 43000,
                'expected_balance' => null,
                'marital_status' => 'أعزب',
                'children_count' => null,
                'has_relatives_abroad' => 'لا',
                'nationality_type' => 'غير مصري',
                'nationality' => 'سودانية',
                'residency_status' => 'زيارة',
                'governorate' => null,
                'consultation_method' => 'فيديوكول',
                'email' => 'client4@example.com',
                'phone_number' => '01007778899',
                'whatsapp_number' => null,
                'previous_rejected' => 'نعم',
                'rejection_numbers' => 'مرة واحدة',
                'rejection_reason' => 'مقابلة غير مقنعة',
                'rejection_country' => 'كندا',
                'has_previous_visas' => 'نعم',
                'previous_visa_countries' => 'قطر',
                'recommendation' => 'يعاد تقييمه بعد استكمال المستندات',
                'status' => 'مكتمل',
                'created_by' => $emp2->id,
                'updated_by' => $emp2->id,
            ],
        ];

        foreach ($demo as $row) {
            $kyc = new KycRecord;
            $kyc->forceFill($row);
            $kyc->save();
        }
    }
}
