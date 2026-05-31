@extends('layouts.app')

@section('title', ($path->name_ar ?? $path->name) . ' — منصة المئة')

@php
    use App\Models\Quiz;
    $subjects = $path->subjects()->where('is_active', true)->orderBy('sort_order')->get();
    $mockExams = Quiz::where('is_published', true)->where('quiz_type', 'mock_exam')->get();
    $user = Auth::user();
@endphp

@section('content')
<section class="bg-gradient-to-b from-blue-900 to-indigo-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-black mb-2">{{ $path->name_ar ?? $path->name }}</h1>
        @if($path->description_ar ?? $path->description)
            <p class="text-blue-200 text-lg">{{ $path->description_ar ?? $path->description }}</p>
        @endif
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Subject Cards --}}
            @foreach($subjects as $subject)
                @php
                    $courseCount = $subject->courses()->where('is_published', true)->count();
                    $skillCount = $subject->skills()->where('is_active', true)->count();
                    $quizCount = Quiz::where('subject_id', $subject->id)->where('is_published', true)->count();
                @endphp
                <a href="{{ route('category.subject', [$path->id, $subject->id]) }}"
                   class="group bg-white rounded-3xl border border-gray-200 shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 overflow-hidden">
                    <div class="h-2 {{ $loop->even ? 'bg-amber-500' : 'bg-blue-600' }}"></div>
                    <div class="p-6">
                        <div class="w-16 h-16 rounded-2xl {{ $loop->even ? 'bg-amber-100' : 'bg-blue-100' }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            @if($subject->icon)
                                <img src="{{ $subject->icon }}" alt="" class="w-8 h-8">
                            @else
                                <span class="text-2xl">{{ $loop->even ? '📝' : '📊' }}</span>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-{{ $loop->even ? 'amber' : 'blue' }}-600 transition-colors">{{ $subject->name_ar ?? $subject->name }}</h2>
                        @if($subject->description_ar ?? $subject->description)
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $subject->description_ar ?? $subject->description }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                            <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $courseCount }} دورة</span>
                            <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $skillCount }} تأسيس</span>
                            <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $quizCount }} اختبار</span>
                        </div>
                    </div>
                </a>
            @endforeach

            {{-- Mock Exams Card --}}
            @if($mockExams->isNotEmpty())
                <div class="group bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-3xl shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 p-6 text-white">
                    <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold mb-1">اختبارات محاكية</h2>
                    <p class="text-indigo-200 text-sm mb-4">{{ $mockExams->count() }} اختبار تجريبي</p>
                    <a href="{{ route('courses') }}?type=mock" class="inline-flex bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors">عرض الاختبارات</a>
                </div>
            @endif

            {{-- Packages Card --}}
            <div class="group bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 p-6 text-white">
                <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <h2 class="text-xl font-bold mb-1">عروض وباقات</h2>
                <p class="text-emerald-200 text-sm mb-4">اختر الباقة المناسبة ووفر أكثر</p>
                <a href="{{ route('pricing') }}" class="inline-flex bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors">عرض الباقات</a>
            </div>

        </div>
    </div>
</section>
@endsection
