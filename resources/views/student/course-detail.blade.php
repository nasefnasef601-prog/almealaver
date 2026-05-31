@extends('layouts.student', ['activeTab' => 'my-courses'])

@section('title', ($course->title_ar ?? $course->title) . ' — منصة المئة')

@php
    use App\Models\Course;
    use App\Models\CourseModule;
    use App\Models\Lesson;
    use App\Models\AccessGrant;
    use App\Models\LessonCompletion;

    $course = Course::with(['modules.lessons' => fn($q) => $q->where('is_published', true)->orderBy('sort_order')])
        ->with(['lessonCompletions' => fn($q) => $q->where('user_id', Auth::id())])
        ->findOrFail($courseId);
    $user = Auth::user();
    $hasAccess = AccessGrant::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->where('status', 'active')
        ->exists();
    $isFree = $course->price == 0;
    $canAccess = $hasAccess || $isFree;
    $allLessonIds = $course->modules->flatMap(fn($m) => $m->lessons->pluck('id'));
    $totalLessons = $allLessonIds->count();
    $completedLessonIds = $course->lessonCompletions->pluck('lesson_id')->toArray();
    $completedLessons = count($completedLessonIds);
    $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
    $instructor = $course->assignedTeacher ?: $course->creator;
    $nextLessonId = null;
    foreach ($course->modules as $module) {
        foreach ($module->lessons as $lesson) {
            if (!in_array($lesson->id, $completedLessonIds)) {
                $nextLessonId = $lesson->id;
                break 2;
            }
        }
    }
@endphp

@section('content')
<div class="max-w-6xl">
    <a href="{{ route('student.courses') }}" class="inline-flex items-center gap-1 text-sm font-bold text-gray-500 hover:text-blue-600 mb-4 lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
        العودة للدورات
    </a>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="grid lg:grid-cols-5 gap-0">
            <div class="lg:col-span-3 p-6 sm:p-8">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold">{{ $course->subject?->name_ar ?? $course->subject?->name ?? 'عام' }}</span>
                    <span>{{ $totalLessons }} دروس</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900">{{ $course->title_ar ?? $course->title }}</h1>
                    @auth
                        @php $isFav = \App\Models\Favorite::isFavorited(auth()->id(), \App\Models\Course::class, $course->id); @endphp
                        <button x-data="{ fav: {{ $isFav ? 'true' : 'false' }} }"
                                @click="
                                    fetch('{{ route('student.favorite.toggle') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify({ type: 'course', id: {{ $course->id }} })
                                    }).then(r => r.json()).then(d => { fav = d.favorited; });
                                "
                                :class="fav ? 'text-red-500' : 'text-gray-300 hover:text-red-400'"
                                class="p-2 rounded-xl hover:bg-red-50 transition-all shrink-0">
                            <svg class="w-7 h-7" :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    @endauth
                </div>
                @if($course->description_ar ?? $course->description)
                    <p class="text-gray-500 leading-relaxed mb-4">{{ $course->description_ar ?? $course->description }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    @if($course->duration_minutes)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ ceil($course->duration_minutes / 60) }} ساعة
                        </span>
                    @endif
                    @if($course->difficulty_level)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            {{ ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'][$course->difficulty_level] ?? $course->difficulty_level }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 bg-gradient-to-br {{ $isFree ? 'from-emerald-500 to-emerald-700' : 'from-blue-500 to-indigo-700' }} p-6 sm:p-8 flex flex-col justify-center items-center text-white">
                <div class="text-5xl mb-4">
                    @if($canAccess) 🎉 @else 🔒 @endif
                </div>
                @if($canAccess)
                    <p class="text-lg font-bold mb-1">مسجل ✓</p>
                    <p class="text-sm text-blue-200 mb-4">لديك وصول كامل لهذا الكورس</p>
                    <div class="w-full bg-white/20 rounded-full h-2.5 mb-4">
                        <div class="bg-white rounded-full h-2.5" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-sm text-blue-200 mb-4">{{ $progress }}% مكتمل</p>
                    @if($nextLessonId)
                        <a href="{{ route('student.lesson.show', [$course->id, $nextLessonId]) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-lg">
                            {{ $progress > 0 ? 'متابعة التعلم' : 'بدء التعلم' }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <div class="text-emerald-200 font-bold text-sm">🎉 أكملت جميع الدروس!</div>
                    @endif
                @else
                    <p class="text-lg font-bold mb-1">{{ number_format($course->price, 0) }} ريال</p>
                    <p class="text-sm text-blue-200 mb-4">اشترك الآن واحصل على وصول كامل</p>
                    <button @click="document.getElementById('payment-section').scrollIntoView({behavior: 'smooth'})" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">اشتراك الآن</button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-xl font-black text-gray-900 mb-4">محتوى الكورس</h2>
                @forelse($course->modules as $module)
                    <div class="mb-4 last:mb-0" x-data="{ open: true }">
                        <button @click="open = !open" class="w-full flex items-center justify-between p-4 bg-gray-50 rounded-2xl font-bold text-gray-900 text-sm">
                            <span>{{ $module->title_ar ?? $module->title }}</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" class="mt-2 space-y-1 pr-4">
                            @forelse($module->lessons as $lesson)
                                @php $lCompleted = in_array($lesson->id, $completedLessonIds); @endphp
                                <a href="{{ $canAccess ? route('student.lesson.show', [$course->id, $lesson->id]) : '#' }}"
                                   class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors {{ !$canAccess ? 'opacity-60 cursor-not-allowed' : '' }}">
                                    <div class="w-8 h-8 rounded-lg {{ $lCompleted ? 'bg-emerald-100 text-emerald-600' : ($canAccess ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400') }} flex items-center justify-center shrink-0">
                                        @if($lCompleted)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $lesson->title_ar ?? $lesson->title }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $lesson->duration_minutes ? $lesson->duration_minutes . ' دقيقة' : '' }}
                                            {{ $lesson->is_free ? '· مجاني' : '' }}
                                        </p>
                                    </div>
                                    @if($lCompleted)
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </a>
                            @empty
                                <p class="text-gray-400 text-sm py-2">لا توجد دروس في هذا الجزء.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">لم يتم إضافة محتوى للكورس بعد.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            @if(!$canAccess)
                <div id="payment-section" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3">اشتراك في الكورس</h3>
                    <p class="text-3xl font-black text-blue-600 mb-4">{{ number_format($course->price, 0) }} <span class="text-base font-normal text-gray-400">ريال</span></p>
                    <form method="POST" action="{{ route('student.payment-request') }}">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <input type="hidden" name="amount" value="{{ $course->price }}">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition-colors">
                            شراء الكورس
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-3 text-center">سيتم مراجعة طلبك من قبل الإدارة</p>
                </div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-3">معلومات الكورس</h3>
                <ul class="space-y-3 text-sm">
                    @if($course->duration_minutes)
                        <li class="flex items-center justify-between">
                            <span class="text-gray-500">المدة</span>
                            <span class="font-bold text-gray-900">{{ ceil($course->duration_minutes / 60) }} ساعة</span>
                        </li>
                    @endif
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">الدروس</span>
                        <span class="font-bold text-gray-900">{{ $totalLessons }}</span>
                    </li>
                    @if($instructor)
                        <li class="flex items-center justify-between">
                            <span class="text-gray-500">المدرب</span>
                            <span class="font-bold text-gray-900">{{ $instructor->name }}</span>
                        </li>
                    @endif
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">المستوى</span>
                        <span class="font-bold text-gray-900">{{ $course->difficulty_level ? ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'][$course->difficulty_level] : 'جميع المستويات' }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">السعر</span>
                        <span class="font-bold {{ $isFree ? 'text-emerald-600' : 'text-blue-600' }}">{{ $isFree ? 'مجاني' : number_format($course->price, 0) . ' ريال' }}</span>
                    </li>
                </ul>
            </div>

            @if($canAccess)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3">تقدمك</h3>
                    <div class="text-center">
                        <div class="text-4xl font-black text-blue-600 mb-2">{{ $progress }}%</div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
                            <div class="bg-amber-500 rounded-full h-2.5 transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">{{ $completedLessons }} من {{ $totalLessons }} دروس</p>
                        @if($nextLessonId)
                            <a href="{{ route('student.lesson.show', [$course->id, $nextLessonId]) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">
                                {{ $progress > 0 ? 'استمر في التعلم' : 'ابدأ التعلم' }}
                            </a>
                        @else
                            <div class="text-emerald-600 font-bold text-sm">🎉 تهانينا! أكملت الكورس</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
