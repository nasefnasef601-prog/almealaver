<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $redirect = $this->getDashboardRoute($user);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['redirect' => $redirect]);
            }

            return redirect()->intended($redirect);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['error' => 'بيانات الدخول غير صحيحة.'], 422);
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $name = $data['name'] ?? explode('@', $data['email'])[0];

        $user = User::create([
            'name' => $name,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        $user->assignRole('student');

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = '/student/dashboard';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['redirect' => $redirect]);
        }

        return redirect($redirect);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return $status === Password::RESET_LINK_SENT
                ? response()->json(['message' => 'تم إرسال تعليمات الاستعادة إذا كان البريد مسجلاً لدينا.'])
                : response()->json(['error' => 'تعذر إرسال طلب الاستعادة الآن.'], 422);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => 'تم إرسال تعليمات الاستعادة إذا كان البريد مسجلاً لدينا.'])
            : back()->withErrors(['email' => 'تعذر إرسال طلب الاستعادة الآن.']);
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', ['token' => $request->token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return $status === Password::PASSWORD_RESET
                ? response()->json(['message' => 'تم تحديث كلمة المرور. يمكنك تسجيل الدخول الآن.', 'redirect' => route('login')])
                : response()->json(['error' => 'الرمز غير صالح أو منتهي الصلاحية.'], 422);
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'تم تحديث كلمة المرور.')
            : back()->withErrors(['email' => 'الرمز غير صالح أو منتهي الصلاحية.']);
    }

    public function showProfile()
    {
        return view('student.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        Auth::user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    private function getDashboardRoute($user)
    {
        if ($user->hasRole('admin')) return '/admin';
        if ($user->hasRole('student')) return '/student/dashboard';
        if ($user->hasRole('teacher')) return '/teacher/dashboard';
        if ($user->hasRole('supervisor')) return '/supervisor/dashboard';
        if ($user->hasRole('parent')) return '/parent/dashboard';
        return '/';
    }
}
