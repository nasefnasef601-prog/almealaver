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
    $avgRating = $course->approvedReviews()->avg('rating') ?? 0;
    $reviewsCount = $course->approvedReviews()->count();
    $myReview = \App\Models\CourseReview::where('user_id', $user->id)
        ->where('course_id', $course->id)->first();
    $reviews = $course->approvedReviews()->with('user')->latest()->take(10)->get();
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
                        @php $courseCompleted = \App\Models\CourseCompletion::where('user_id', $user->id)->where('course_id', $course->id)->first(); @endphp
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="text-emerald-200 font-bold text-sm">&#x2705; أكملت جميع الدروس!</div>
                            @if($courseCompleted)
                            <a href="{{ route('student.certificate', $course->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg font-bold text-xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                الشهادة
                            </a>
                            @endif
                        </div>
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
                    <form method="POST" action="{{ route('student.payment-request') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <input type="hidden" name="amount" value="{{ $course->price }}">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">كود الخصم</label>
                            <input type="text" name="discount_code" value="{{ old('discount_code') }}" dir="ltr"
                                   class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-mono tracking-widest focus:border-amber-400 focus:outline-none"
                                   placeholder="DISCOUNT">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">تحويل بنكي</label>
                            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-600 space-y-1 mb-3">
                                <p><strong>البنك:</strong> @php $paySetting = \App\Models\PaymentSetting::where('payment_method', 'bank_transfer')->first(); @endphp {{ $paySetting?->config['bank_name'] ?? 'البنك الأهلي السعودي' }}</p>
                                <p><strong>الحساب:</strong> {{ $paySetting?->config['account_name'] ?? 'منصة المئة' }}</p>
                                <p><strong>رقم الحساب:</strong> {{ $paySetting?->config['account_number'] ?? 'SA1234567890' }}</p>
                            </div>
                            <label class="block text-sm font-medium text-gray-600 mb-1 cursor-pointer">
                                <input type="file" name="bank_transfer_receipt" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" required>
                            </label>
                            <p class="text-xs text-gray-400 mt-1">أرفق صورة الإيصال (jpg, png - حد أقصى 5MB)</p>
                        </div>
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
                    @if($reviewsCount > 0)
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">التقييم</span>
                        <span class="font-bold text-gray-900 flex items-center gap-1">
                            <span class="text-amber-500">{{ str_repeat('⭐', round($avgRating)) }}</span>
                            <span class="text-xs text-gray-400">({{ $reviewsCount }})</span>
                        </span>
                    </li>
                    @endif
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

    {{-- Reviews Section --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8 mt-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            التقييمات{{ $reviewsCount > 0 ? " ($reviewsCount)" : '' }}
        </h2>

        @if($canAccess)
            @php $courseCompleted = \App\Models\CourseCompletion::where('user_id', $user->id)->where('course_id', $course->id)->first(); @endphp
            @if($courseCompleted || $progress == 100)
                @if(!$myReview)
                <form method="POST" action="{{ route('student.review.submit', $course->id) }}" class="mb-8 p-5 bg-amber-50 rounded-2xl border border-amber-200">
                    @csrf
                    <h3 class="font-bold text-gray-900 mb-3">شاركنا رأيك في هذه الدورة</h3>
                    <div class="mb-4" x-data="{ rating: 0, hover: 0 }">
                        <p class="text-sm text-gray-500 mb-2">التقييم</p>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}"
                                    @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0"
                                    class="text-2xl transition-colors focus:outline-none"
                                    :class="(hover || rating) >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'">
                                ★
                            </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <div class="mb-4">
                        <textarea name="review" rows="3" placeholder="اكتب مراجعتك (اختياري)..."
                                  class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition-all"></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm transition-colors">
                        إرسال التقييم
                    </button>
                </form>
                @else
                <div class="mb-8 p-5 bg-emerald-50 rounded-2xl border border-emerald-200">
                    <p class="text-sm text-emerald-700 font-bold">&#x2705; تم تقييمك: {{ str_repeat('⭐', $myReview->rating) }}</p>
                    @if($myReview->review)
                        <p class="text-sm text-gray-600 mt-1">{{ $myReview->review }}</p>
                    @endif
                    @if(!$myReview->is_approved)
                        <p class="text-xs text-amber-600 mt-2">بانتظار اعتماد الإدارة</p>
                    @endif
                </div>
                @endif
            @endif
        @endif

        {{-- Display Reviews --}}
        @forelse($reviews as $review)
            <div class="flex gap-4 py-4 border-b border-gray-50 last:border-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ mb_substr($review->user->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-gray-900 text-sm">{{ $review->user->name }}</span>
                        <span class="text-amber-500 text-xs">{{ str_repeat('⭐', $review->rating) }}</span>
                        <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->review)
                        <p class="text-sm text-gray-600">{{ $review->review }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-400">
                <p>لا توجد تقييمات بعد</p>
                @if($canAccess)
                <p class="text-sm mt-1">كن أول من يقيم هذه الدورة!</p>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
