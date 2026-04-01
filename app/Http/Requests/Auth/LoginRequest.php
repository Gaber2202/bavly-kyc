<?php

namespace App\Http\Requests\Auth;

use App\Models\FailedLoginAttempt;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(
            ['username' => $this->string('username')->toString(), 'password' => $this->string('password')->toString(), 'is_active' => true],
            $this->boolean('remember')
        )) {
            FailedLoginAttempt::query()->create([
                'username' => $this->input('username'),
                'ip_address' => $this->ip() ?? '',
                'attempted_at' => now(),
            ]);

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => __('بيانات الدخول غير صحيحة أو الحساب غير مفعّل.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('محاولات كثيرة. أعد المحاولة بعد :seconds ثانية.', ['seconds' => $seconds]),
        ]);
    }

    public function throttleKey(): string
    {
        return 'login|'.strtolower($this->string('username')->toString()).'|'.($this->ip() ?? '');
    }
}
