@extends('layouts.student', ['activeTab' => 'quizzes'])

@section('title', 'مركز الاختبارات')

@section('content')
<div class="max-w-6xl" x-data="{ search: '' }">
    <h1 class="text-2xl font-black text-gray-900 mb-2">مركز الاختبارات</h1>
    <p class="text-gray-500 text-sm mb-6">اختبر مستواك وتابع تقدمك</p>

    <div class="relative mb-6">
        <input type="text" x-model="search" placeholder="ابحث عن اختبار..."
               class="w-full pr-10 pl-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>

    @if($quizzes->isEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-gray-500 text-lg mb-2">لا توجد اختبارات متاحة</p>
            <p class="text-gray-400 text-sm">اشترك في الكورسات لبدء الاختبارات</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($quizzes as $quiz)
                @php
                    $questionsCount = $quiz->questions->count();
                    $userAttempts = \App\Models\QuizAttempt::where('user_id', auth()->id())
                        ->where('quiz_id', $quiz->id)->count();
                    $bestResult = \App\Models\QuizResult::where('user_id', auth()->id())
                        ->where('quiz_id', $quiz->id)->max('score_percentage');
                    $quizTitle = $quiz->title_ar ?? $quiz->title;
                    $courseTitle = $quiz->course->title_ar ?? $quiz->course->title ?? '';
                @endphp
                <div x-show="search === '' || '{{ strtolower($quizTitle) }}'.includes(search.toLowerCase()) || '{{ strtolower($courseTitle) }}'.includes(search.toLowerCase())"
                     x-transition class="bg-white border border-gray-200 rounded-3xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">{{ $quizTitle }}</h3>
                    @if($quiz->course)
                        <p class="text-xs text-gray-400 mb-3">{{ $courseTitle }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2 mb-4 text-xs text-gray-500">
                        <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $questionsCount }} سؤال</span>
                        @if($quiz->time_limit)
                            <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $quiz->time_limit }} دقيقة</span>
                        @endif
                        @if($quiz->difficulty)
                            <span class="px-2.5 py-1 rounded-full {{ $quiz->difficulty === 'hard' ? 'bg-red-100 text-red-700' : ($quiz->difficulty === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب'][$quiz->difficulty] ?? $quiz->difficulty }}
                            </span>
                        @endif
                    </div>
                    @if($bestResult !== null)
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xs text-gray-500">أفضل نتيجة:</span>
                            <span class="text-sm font-bold {{ $bestResult >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">{{ number_format($bestResult, 0) }}%</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-400">{{ $userAttempts }}/{{ $quiz->max_attempts ?? '∞' }} محاولات</span>
                        <a href="{{ route('student.quiz.show', $quiz->id) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">ابدأ ←</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
