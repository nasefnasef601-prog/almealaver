@extends('layouts.student', ['activeTab' => 'mock-exams'])

@section('title', 'مركز الاختبارات المحاكية')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    {{-- Header Section --}}
    <header class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-10 text-center text-white rounded-3xl shadow-lg mb-10 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
        <div class="relative z-10">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-md shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <h1 class="text-3xl font-black leading-tight sm:text-4xl">الاختبارات المحاكية</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm sm:text-base font-medium text-indigo-100 leading-relaxed">
                اختر المسار المناسب، وابدأ التدرب على محاكاة حقيقية لاختبارات قياس المعتمدة بمؤقت وأقسام متطابقة.
            </p>
        </div>
    </header>

    {{-- Main Contents --}}
    <main class="mx-auto max-w-6xl px-4">
        @if(count($mockPathsData) > 0)
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($mockPathsData as $data)
                    @php
                        $path = $data['path'];
                        $isReady = $data['is_ready'];
                    @endphp
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between h-full relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50/50 rounded-full translate-x-8 -translate-y-8 z-0"></div>
                        <div class="relative z-10 flex items-start gap-4 mb-6">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100">
                                @if(str_contains($path->name_ar ?? $path->name, 'تحصيلي'))
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h2 class="text-xl font-black text-gray-900 leading-tight">{{ $path->name_ar ?? $path->name }}</h2>
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold {{ $isReady ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isReady ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $isReady ? 'جاهز' : 'قريبًا' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs font-bold text-gray-400">
                                    {{ $isReady ? 'محاكيات منشورة للمسار' : 'سيظهر هنا عند نشر أول اختبار محاكي للمسار' }}
                                </p>
                            </div>
                        </div>

                        <div class="relative z-10 mt-auto flex flex-col gap-4">
                            @if($isReady)
                                <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                                    <div class="grid grid-cols-3 gap-3 text-center">
                                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-50">
                                            <div class="text-lg font-black text-gray-900">{{ $data['exams_count'] }}</div>
                                            <div class="text-[10px] font-bold text-gray-400">اختبار</div>
                                        </div>
                                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-50">
                                            <div class="text-lg font-black text-emerald-600">{{ $data['total_questions'] }}</div>
                                            <div class="text-[10px] font-bold text-gray-400">سؤال</div>
                                        </div>
                                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-50">
                                            <div class="text-lg font-black text-amber-600">{{ $data['total_time'] }}</div>
                                            <div class="text-[10px] font-bold text-gray-400">دقيقة</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center gap-3">
                                @if($isReady)
                                    <a href="{{ route('student.dashboard') }}?tab=quizzes"
                                       class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 text-sm font-bold shadow-md hover:shadow-lg transition-all">
                                        <span>فتح المحاكيات</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transform rotate-180"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </a>
                                @else
                                    <button disabled
                                            class="flex-1 inline-flex items-center justify-center rounded-xl bg-gray-100 text-gray-400 px-4 py-3 text-sm font-bold cursor-not-allowed">
                                        قريباً جداً
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <p class="text-gray-500 text-lg mb-2">لا توجد مسارات نشطة حاليًا</p>
                <p class="text-gray-400 text-sm">سيتم تفعيل المسارات وقسم المحاكيات قريباً.</p>
            </div>
        @endif
    </main>
</div>
@endsection
