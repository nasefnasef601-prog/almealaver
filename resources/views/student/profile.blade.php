@extends('layouts.student', ['activeTab' => 'overview'])

@section('title', 'الملف الشخصي')

@section('content')
<div class="max-w-4xl" x-data="{ tab: 'info' }">
    <h1 class="text-2xl font-black text-gray-900 mb-2">الملف الشخصي</h1>
    <p class="text-gray-500 text-sm mb-6">إدارة بيانات حسابك وكلمة المرور</p>

    <div class="flex gap-2 mb-6">
        <button @click="tab = 'info'" :class="tab === 'info' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">المعلومات الأساسية</button>
        <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">تغيير كلمة المرور</button>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-200 font-bold text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Info Tab --}}
    <div x-show="tab === 'info'" x-transition>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ Auth::user()->name }}</h2>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">رقم الجوال</label>
                        <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">تاريخ التسجيل</label>
                        <input type="text" value="{{ Auth::user()->created_at->format('Y/m/d') }}" disabled
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500">
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-colors">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Password Tab --}}
    <div x-show="tab === 'password'" x-transition>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">تغيير كلمة المرور</h2>
            <form method="POST" action="{{ route('student.profile.password') }}" class="space-y-5 max-w-md">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">كلمة المرور الحالية</label>
                    <input type="password" name="current_password" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                    @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">كلمة المرور الجديدة</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-left" dir="ltr">
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm transition-colors">تغيير كلمة المرور</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
