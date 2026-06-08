@extends('layouts.app')

@section('title', 'اتصل بنا')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-black text-gray-900 mb-4">اتصل بنا</h1>
        <p class="text-lg text-gray-500">نحن هنا للإجابة على استفساراتك</p>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 font-bold text-center">
            &#x2705; {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('contact.send') }}" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">الاسم</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 mb-2">الموضوع</label>
            <input type="text" name="subject" value="{{ old('subject') }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">الرسالة</label>
            <textarea name="message" rows="5" required
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none resize-none">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 rounded-xl transition-colors text-lg">
            إرسال الرسالة
        </button>
    </form>
</div>
@endsection
