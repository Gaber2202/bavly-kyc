<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForcePasswordUpdateRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForcePasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.force-password');
    }

    public function update(ForcePasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->must_change_password = false;
        $user->save();

        ActivityLogger::log($user, 'user.password_changed', $user, ['forced' => true]);

        return redirect()
            ->route('dashboard')
            ->with('toast', ['type' => 'success', 'message' => 'تم تحديث كلمة المرور بنجاح.']);
    }
}
