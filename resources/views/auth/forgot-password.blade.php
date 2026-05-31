@extends('layouts.app')

@section('title', 'استعادة كلمة المرور')

@section('content')
<main class="min-h-[calc(100vh-8rem)] bg-gray-50 px-4 py-10" dir="rtl">
    <section class="mx-auto w-full max-w-md">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <a href="{{ url('/') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                العودة للرئيسية
            </a>

            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">استعادة كلمة المرور</h1>
                    <p class="mt-1 text-sm text-gray-500">اكتب بريدك وسنرسل لك تعليمات آمنة للاستعادة.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl border border-emerald-100 font-bold">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-xl border border-red-100">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1 block text-sm font-bold text-gray-700">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        dir="ltr" placeholder="user@example.com">
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-600">
                    إرسال التعليمات
                </button>
            </form>

            <a href="{{ route('login') }}" class="mt-5 block text-center text-sm font-bold text-gray-500 hover:text-emerald-600">
                تذكرت كلمة المرور؟ سجل الدخول
            </a>
        </div>
    </section>
</main>
@endsection
