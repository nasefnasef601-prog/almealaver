@extends('layouts.app')

@section('title', ($subject->name_ar ?? $subject->name) . ' — ' . ($path->name_ar ?? $path->name) . ' — منصة المئة')

@php
    use App\Models\Quiz;
    use App\Models\Lesson;
    $user = Auth::user();

    // Courses
    $courses = $subject->courses()->where('is_published', true)->with(['modules.lessons', 'lessonCompletions'])->latest()->get();

    // Skills (Foundation topics)
    $skills = $subject->skills()->where('skills.is_active', true)->orderBy('skills.sort_order')->get();

    // Training quizzes (quiz_type = 'training' or 'quiz')
    $trainingQuizzes = Quiz::where('subject_id', $subject->id)->where('is_published', true)
        ->whereIn('quiz_type', ['quiz', 'training'])->get();

    // Mock exams
    $mockExams = Quiz::where('subject_id', $subject->id)->where('is_published', true)
        ->where('quiz_type', 'mock_exam')->get();

    // Library (published lessons with content)
    $libraryLessons = Lesson::whereHas('course', fn($q) => $q->where('subject_id', $subject->id))
        ->where('is_published', true)
        ->whereNotNull('content_url')
        ->latest()
        ->get();

    $tabAliases = [
        'foundation' => 'skills',
        'questions' => 'training',
        'banks' => 'training',
        'exams' => 'tests',
    ];
    $requestedTab = request('tab', 'skills');
    $requestedTab = $tabAliases[$requestedTab] ?? $requestedTab;
    $requestedTab = in_array($requestedTab, ['courses', 'skills', 'training', 'tests', 'library'], true) ? $requestedTab : 'skills';

    $firstSkill = $skills->first();
    $firstTrainingQuiz = $trainingQuizzes->first();
    $firstMockExam = $mockExams->first();
    $firstCourse = $courses->first();
    $firstLibraryLesson = $libraryLessons->first();

    $nextAction = null;
    if ($requestedTab === 'skills' && $firstSkill) {
        $nextAction = [
            'label' => 'ابدأ التأسيس',
            'title' => $firstSkill->name_ar ?? $firstSkill->name,
            'description' => 'ابدأ بأول مهارة متاحة، ثم انتقل للتدريب بعد المراجعة.',
            'href' => auth()->check() ? route('student.skill.detail', $firstSkill->id) : route('login'),
            'tone' => 'blue',
        ];
    } elseif ($requestedTab === 'training' && $firstTrainingQuiz) {
        $nextAction = [
            'label' => 'ابدأ التدريب',
            'title' => $firstTrainingQuiz->title_ar ?? $firstTrainingQuiz->title,
            'description' => 'حل تدريبًا قصيرًا ثم راجع النتيجة والمهارات الضعيفة.',
            'href' => auth()->check() ? route('student.quiz.show', $firstTrainingQuiz->id) : route('login'),
            'tone' => 'emerald',
        ];
    } elseif ($requestedTab === 'tests' && $firstMockExam) {
        $nextAction = [
            'label' => 'ابدأ الاختبار',
            'title' => $firstMockExam->title_ar ?? $firstMockExam->title,
            'description' => 'اختبر جاهزيتك في تجربة أقرب للاختبار الحقيقي.',
            'href' => auth()->check() ? route('student.quiz.show', $firstMockExam->id) : route('login'),
            'tone' => 'amber',
        ];
    } elseif ($requestedTab === 'courses' && $firstCourse) {
        $nextAction = [
            'label' => 'افتح الدورة',
            'title' => $firstCourse->title_ar ?? $firstCourse->title,
            'description' => 'تابع محتوى منظمًا بالدروس والاختبارات المرتبطة.',
            'href' => route('course-detail', $firstCourse->id),
            'tone' => 'indigo',
        ];
    } elseif ($requestedTab === 'library' && $firstLibraryLesson?->content_url) {
        $nextAction = [
            'label' => 'افتح الملف',
            'title' => $firstLibraryLesson->title_ar ?? $firstLibraryLesson->title,
            'description' => 'راجع ملف دعم سريع قبل التدريب أو الاختبار.',
            'href' => $firstLibraryLesson->content_url,
            'tone' => 'rose',
        ];
    }
@endphp

@section('content')
<div
    x-data="{
        tab: @js($requestedTab),
        init() {
            this.$watch('tab', (value) => this.syncTabUrl(value));
        },
        setTab(value) {
            this.tab = value;
            this.syncTabUrl(value);
        },
        syncTabUrl(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', value);
            window.history.replaceState({}, '', url.toString());
        }
    }"
    class="bg-gray-50 min-h-screen"
>
    {{-- Header --}}
    <section class="bg-gradient-to-b from-blue-900 to-indigo-900 text-white py-10">
        <div class="max-w-7xl mx-auto px-4">
            <a href="{{ route('category', $path->id) }}" class="inline-flex items-center gap-1 text-sm text-blue-200 hover:text-white mb-3 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                العودة للمسار
            </a>
            <h1 class="text-3xl font-black">{{ $subject->name_ar ?? $subject->name }}</h1>
            <p class="text-blue-200 text-sm mt-1">{{ $path->name_ar ?? $path->name }}</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-8">
        @if($nextAction)
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-black text-gray-400">الخطوة التالية</p>
                        <h2 class="mt-1 text-lg font-black text-gray-900">{{ $nextAction['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $nextAction['description'] }}</p>
                    </div>
                    <a href="{{ $nextAction['href'] }}"
                       class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-black text-white transition-colors
                           {{ $nextAction['tone'] === 'emerald' ? 'bg-emerald-600 hover:bg-emerald-700' : '' }}
                           {{ $nextAction['tone'] === 'amber' ? 'bg-amber-500 hover:bg-amber-600' : '' }}
                           {{ $nextAction['tone'] === 'indigo' ? 'bg-indigo-600 hover:bg-indigo-700' : '' }}
                           {{ $nextAction['tone'] === 'rose' ? 'bg-rose-600 hover:bg-rose-700' : '' }}
                           {{ $nextAction['tone'] === 'blue' ? 'bg-blue-600 hover:bg-blue-700' : '' }}"
                       @if($requestedTab === 'library') target="_blank" rel="noopener" @endif>
                        {{ $nextAction['label'] }}
                    </a>
                </div>
            </div>
        @endif
        {{-- Tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-8">
            <button @click="tab = 'courses'" :class="tab === 'courses' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الدورات</button>
            <button @click="tab = 'skills'" :class="tab === 'skills' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التأسيس</button>
            <button @click="tab = 'training'" :class="tab === 'training' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التدريب</button>
            <button @click="tab = 'tests'" :class="tab === 'tests' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الاختبارات</button>
            <button @click="tab = 'library'" :class="tab === 'library' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المكتبة</button>
        </div>

        {{-- Courses Tab --}}
        <div x-show="tab === 'courses'" x-transition>
            @if($courses->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد دورات بعد</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($courses as $course)
                        <a href="{{ route('course-detail', $course->id) }}" class="group bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="h-36 bg-gradient-to-br {{ $loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-white text-xl font-black relative">
                                @if($course->thumbnail)
                                    <img src="{{ $course->thumbnail }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                                @endif
                                @if($course->price > 0)
                                    <span class="absolute top-3 left-3 bg-white/90 text-amber-600 text-xs font-bold px-3 py-1 rounded-full">{{ number_format($course->price, 0) }} ريال</span>
                                @else
                                    <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">مجاني</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition-colors">{{ $course->title_ar ?? $course->title }}</h3>
                                <p class="text-xs text-gray-400 mt-2">{{ $course->modules->sum(fn($m) => $m->lessons->count()) }} درس</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Skills Tab (Foundation) --}}
        <div x-show="tab === 'skills'" x-transition>
            @if($skills->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد مواضيع تأسيس بعد</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($skills as $skill)
                        @php
                            $lessonCount = $skill->courses->sum(fn($c) => $c->lessons->count());
                            $questionCount = $skill->questions->count();
                            $skillUrl = auth()->check()
                                ? route('student.skill.detail', $skill->id)
                                : route('login');
                        @endphp
                        <a href="{{ $skillUrl }}" class="block bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg hover:border-blue-200 transition-all group">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mb-3 group-hover:from-blue-200 group-hover:to-indigo-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 12h10m-6 0v4"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors">{{ $skill->name_ar ?? $skill->name }}</h3>
                            @if($skill->description_ar ?? $skill->description)
                                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $skill->description_ar ?? $skill->description }}</p>
                            @endif
                            <div class="flex gap-2 text-xs text-gray-500">
                                <span class="bg-gray-100 px-2 py-0.5 rounded-full">{{ $lessonCount }} شرح</span>
                                <span class="bg-gray-100 px-2 py-0.5 rounded-full">{{ $questionCount }} سؤال</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Training Tab --}}
        <div x-show="tab === 'training'" x-transition>
            @if($trainingQuizzes->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد تدريبات بعد</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($trainingQuizzes as $quiz)
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $quiz->title_ar ?? $quiz->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $quiz->questions()->count() }} سؤال</p>
                                </div>
                            </div>
                            <a href="{{ route('student.quiz.show', $quiz->id) }}" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-xl font-bold text-sm transition-colors">ابدأ التدريب</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tests Tab (Mock Exams) --}}
        <div x-show="tab === 'tests'" x-transition>
            @if($mockExams->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد اختبارات محاكية بعد</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($mockExams as $exam)
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $exam->title_ar ?? $exam->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $exam->questions()->count() }} سؤال · {{ $exam->time_limit }} دقيقة</p>
                                </div>
                            </div>
                            <a href="{{ route('student.quiz.show', $exam->id) }}" class="block text-center bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-xl font-bold text-sm transition-colors">ابدأ الاختبار</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Library Tab --}}
        <div x-show="tab === 'library'" x-transition>
            @if($libraryLessons->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد ملفات في المكتبة بعد</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($libraryLessons as $lesson)
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $lesson->title_ar ?? $lesson->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' دقيقة' : 'ملف' }}</p>
                                </div>
                            </div>
                            @if($lesson->content_url)
                                <a href="{{ $lesson->content_url }}" target="_blank" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-xl font-bold text-sm transition-colors">فتح الملف</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
