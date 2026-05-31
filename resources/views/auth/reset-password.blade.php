@extends('layouts.app')

@section('title', 'تعيين كلمة مرور جديدة')

@section('content')
<main class="min-h-[calc(100vh-8rem)] bg-gray-50 px-4 py-10" dir="rtl">
    <section class="mx-auto w-full max-w-md">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <a href="{{ url('/') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                العودة للرئيسية
            </a>

            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">تعيين كلمة مرور جديدة</h1>
                    <p class="mt-1 text-sm text-gray-500">استخدم الرمز الذي وصلك ثم اختر كلمة مرور قوية.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-xl border border-red-100">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="mb-1 block text-sm font-bold text-gray-700">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', request()->email) }}" required readonly
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left outline-none bg-gray-50 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        dir="ltr" placeholder="user@example.com">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold text-gray-700">كلمة المرور الجديدة</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        dir="ltr" placeholder="********">
                    <p class="mt-1 text-xs text-gray-400">8 أحرف على الأقل، مع حرف ورقم.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold text-gray-700">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        dir="ltr" placeholder="********">
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white transition hover:bg-indigo-700">
                    حفظ كلمة المرور
                </button>
            </form>
        </div>
    </section>
</main>
@endsection
