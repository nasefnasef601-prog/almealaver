@extends('layouts.student', ['activeTab' => 'quizzes'])

@section('title', $quiz->title)

@php $questionsCount = $quiz->questions()->count(); @endphp

@section('content')
<div class="max-w-3xl mx-auto text-center">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-12">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-2">{{ $quiz->title_ar ?? $quiz->title }}</h1>
        @if($quiz->description_ar ?? $quiz->description)
            <p class="text-gray-500 mb-6">{{ $quiz->description_ar ?? $quiz->description }}</p>
        @endif

        <div class="flex justify-center gap-4 mb-8">
            <div class="bg-gray-50 rounded-2xl p-4 text-center min-w-[100px]">
                <p class="text-2xl font-black text-blue-600">{{ $questionsCount }}</p>
                <p class="text-xs text-gray-500">سؤال</p>
            </div>
            @if($quiz->time_limit)
                <div class="bg-gray-50 rounded-2xl p-4 text-center min-w-[100px]">
                    <p class="text-2xl font-black text-amber-600">{{ $quiz->time_limit }}</p>
                    <p class="text-xs text-gray-500">دقيقة</p>
                </div>
            @endif
            <div class="bg-gray-50 rounded-2xl p-4 text-center min-w-[100px]">
                <p class="text-2xl font-black text-emerald-600">{{ $quiz->passing_score ?? 50 }}%</p>
                <p class="text-xs text-gray-500">حد النجاح</p>
            </div>
        </div>

        @if($lastAttempt)
            <div class="bg-blue-50 rounded-2xl p-4 mb-6 text-right">
                <p class="text-sm text-blue-800 font-medium">آخر محاولة: {{ number_format($bestScore, 0) }}%</p>
                <p class="text-xs text-blue-600">{{ $lastAttempt->created_at->diffForHumans() }}</p>
            </div>
        @endif

        @if(!$canRetake && $attemptsCount >= $quiz->max_attempts)
            <div class="bg-red-50 rounded-2xl p-4 mb-6">
                <p class="text-red-700 font-medium">لقد استنفذت جميع المحاولات المسموحة لهذا الاختبار.</p>
            </div>
            <a href="{{ route('student.quiz.result', $quiz->id) }}" class="inline-flex bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition-colors">عرض النتائج</a>
        @elseif($lastAttempt && $canRetake)
            <a href="{{ route('student.quiz.start', $quiz->id) }}" class="inline-flex bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-xl font-bold text-lg transition-colors mb-3">إعادة الاختبار</a>
        @else
            <a href="{{ route('student.quiz.start', $quiz->id) }}" class="inline-flex bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-xl font-bold text-lg transition-colors mb-3">بدء الاختبار</a>
        @endif

        @if($lastAttempt)
            <div class="mt-4">
                <a href="{{ route('student.quiz.result', $quiz->id) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">عرض آخر نتيجة ←</a>
            </div>
        @endif
    </div>
</div>
@endsection
