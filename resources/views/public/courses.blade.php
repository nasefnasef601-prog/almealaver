@extends('layouts.app')

@section('title', 'الكورسات')

@php
    use App\Models\Course;
    use App\Models\Subject;
    use App\Models\Path;

    $search = request('search');
    $subjectId = request('subject');
    $pathId = request('path');
    $difficulty = request('difficulty');
    $sort = request('sort', 'newest');

    $courses = Course::with('subject.path', 'modules.lessons')
        ->where('is_published', true);

    if ($search) {
        $courses->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('title_ar', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('description_ar', 'like', "%{$search}%");
        });
    }
    if ($subjectId) {
        $courses->where('subject_id', $subjectId);
    }
    if ($pathId) {
        $courses->whereHas('subject', function($q) use ($pathId) { $q->where('path_id', $pathId); });
    }
    if ($difficulty) {
        $courses->where('difficulty_level', $difficulty);
    }

    if ($sort === 'oldest') { $courses = $courses->oldest(); }
    elseif ($sort === 'price-asc') { $courses = $courses->orderBy('price'); }
    elseif ($sort === 'price-desc') { $courses = $courses->orderByDesc('price'); }
    elseif ($sort === 'alpha') { $courses = $courses->orderBy('title_ar')->orderBy('title'); }
    else { $courses = $courses->latest(); }
    $courses = $courses->paginate(12)->withQueryString();

    $paths = Path::with('subjects')->where('is_active', true)->orderBy('sort_order')->get();
    $subjects = Subject::where('is_active', true)->orderBy('sort_order')->get();

    $difficultyLabels = ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'];
    $sortOptions = [
        'newest' => 'الأحدث',
        'oldest' => 'الأقدم',
        'price-asc' => 'السعر: الأقل أولاً',
        'price-desc' => 'السعر: الأعلى أولاً',
        'alpha' => 'ترتيب أبجدي',
    ];
@endphp

@section('content')
<section class="bg-gradient-to-b from-blue-900 to-indigo-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-black mb-4">جميع <span class="text-amber-400">الكورسات</span></h1>
        <p class="text-blue-200 text-lg">اختر المسار المناسب لك وابدأ التعلم اليوم</p>
    </div>
</section>

<section class="py-10 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">

        {{-- Filters --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-8">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">بحث</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث عن كورس..."
                               class="w-full pr-10 pl-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                {{-- Path --}}
                <div class="min-w-[160px]">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">المسار</label>
                    <select name="path" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">كل المسارات</option>
                        @foreach($paths as $path)
                            <option value="{{ $path->id }}" @selected($pathId == $path->id)>{{ $path->name_ar ?? $path->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div class="min-w-[160px]">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">الموضوع</label>
                    <select name="subject" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">كل المواضيع</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name_ar ?? $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Difficulty --}}
                <div class="min-w-[140px]">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">المستوى</label>
                    <select name="difficulty" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">كل المستويات</option>
                        @foreach($difficultyLabels as $key => $label)
                            <option value="{{ $key }}" @selected($difficulty === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort --}}
                <div class="min-w-[150px]">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">الترتيب</label>
                    <select name="sort" onchange="this.form.submit()"
                            class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($sortOptions as $key => $label)
                            <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($search || $subjectId || $pathId || $difficulty || $sort !== 'newest')
                    <div class="flex items-end pb-0.5">
                        <a href="{{ route('courses') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-bold transition-colors">إعادة تعيين</a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Results count --}}
        <div class="flex items-center justify-between mb-6">
            <p class="text-gray-500 text-sm">{{ $courses->total() }} كورس</p>
        </div>

        {{-- Course Grid --}}
        @if($courses->isEmpty())
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-gray-500 text-lg mb-2">لا توجد كورسات مطابقة</p>
                <p class="text-gray-400 text-sm mb-4">حاول تغيير معايير البحث أو إعادة تعيين الفلاتر</p>
                <a href="{{ route('courses') }}" class="inline-flex px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700">عرض كل الكورسات</a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                    @php
                        $totalLessons = $course->modules->sum(fn($m) => $m->lessons->count());
                        $difficultyBadge = match ($course->difficulty_level) {
                            'beginner' => ['label' => 'مبتدئ', 'class' => 'bg-emerald-100 text-emerald-700'],
                            'intermediate' => ['label' => 'متوسط', 'class' => 'bg-amber-100 text-amber-700'],
                            'advanced' => ['label' => 'متقدم', 'class' => 'bg-red-100 text-red-700'],
                            default => null,
                        };
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 group">
                        <div class="h-44 bg-gradient-to-br {{ $loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-white text-3xl font-black relative">
                            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width%3D%2240%22 height%3D%2240%22 viewBox%3D%220 0 40 40%22 xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg fill%3D%22%23fff%22 fill-opacity%3D%220.2%22%3E%3Cpath d%3D%22M2 0v40M0 2h40%22/%3E%3C/g%3E%3C/svg%3E")'></div>
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail }}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            @endif
                            @if($difficultyBadge)
                                <span class="absolute top-3 right-3 text-xs font-bold px-2.5 py-0.5 rounded-full {{ $difficultyBadge['class'] }}">{{ $difficultyBadge['label'] }}</span>
                            @endif
                            @if($course->price > 0)
                                <span class="absolute top-3 left-3 bg-white/90 text-amber-600 text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm">{{ number_format($course->price, 0) }} ريال</span>
                            @else
                                <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">مجاني</span>
                            @endif
                            <span class="relative z-10">{{ mb_substr($course->title_ar ?? $course->title, 0, 2) }}</span>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-amber-600 transition-colors">{{ $course->title_ar ?? $course->title }}</h3>
                            @if($course->short_description ?? ($course->description_ar ?? $course->description))
                                <p class="text-gray-500 text-sm mb-4 line-clamp-2">{{ $course->short_description ?? ($course->description_ar ?? $course->description) }}</p>
                            @endif
                            <div class="flex items-center gap-3 text-xs text-gray-400 mb-4">
                                @if($course->subject)
                                    <span class="bg-gray-100 px-2.5 py-1 rounded-full">{{ $course->subject->name_ar ?? $course->subject->name }}</span>
                                @endif
                                <span>{{ $totalLessons }} درس</span>
                                @if($course->duration_minutes)
                                    <span>{{ ceil($course->duration_minutes / 60) }} ساعة</span>
                                @endif
                            </div>
                            <a href="{{ route('course-detail', $course->id) }}" class="block text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">عرض التفاصيل</a>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- Pagination --}}
            <div class="mt-8">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
