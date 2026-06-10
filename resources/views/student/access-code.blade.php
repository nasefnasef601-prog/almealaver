@extends('layouts.student')

@section('title', 'تفعيل كود دخول')

@section('content')
<div class="max-w-2xl">
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-amber-600">باقات المدارس</p>
        <h1 class="mt-2 text-2xl font-black text-gray-900">تفعيل كود دخول</h1>
        <p class="mt-2 text-sm leading-7 text-gray-600">
            أدخل الكود الذي حصلت عليه من المدرسة أو المشرف لإضافة الدورات المتاحة إلى حسابك.
        </p>

        @if(session('error'))
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('student.access-code.redeem') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-bold text-gray-800">الكود</span>
                <input
                    name="code"
                    value="{{ old('code') }}"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left font-mono text-lg tracking-widest outline-none focus:border-amber-400"
                    placeholder="SCHOOL-2026"
                    dir="ltr"
                    required
                >
                @error('code')
                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="rounded-xl bg-amber-500 px-6 py-3 font-black text-white shadow-sm hover:bg-amber-600">
                تفعيل الكود
            </button>
        </form>
    </section>
</div>
@endsection
