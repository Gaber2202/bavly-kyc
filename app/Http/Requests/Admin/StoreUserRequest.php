<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User as UserModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', UserModel::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
            'can_view_all_kyc' => ['sometimes', 'boolean'],
            'can_view_reports' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'username' => 'اسم المستخدم',
            'email' => 'البريد',
            'password' => 'كلمة المرور',
            'role' => 'الدور',
            'is_active' => 'مفعّل',
            'can_view_all_kyc' => 'رؤية كل سجلات KYC',
            'can_view_reports' => 'رؤية التقارير',
        ];
    }
}
