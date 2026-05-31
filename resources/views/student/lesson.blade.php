@extends('layouts.student', ['activeTab' => 'my-courses'])

@section('title', ($lesson->title_ar ?? $lesson->title) . ' — ' . ($course->title_ar ?? $course->title))

@php
    $contentIsVideo = $lesson->video_url || $lesson->content_type === 'video';
    $contentIsText = $lesson->content_text || $lesson->content_type === 'text';
    $contentIsPdf = $lesson->content_url && in_array($lesson->content_type, ['pdf', 'file', 'attachment']);
@endphp

@section('content')
<div class="max-w-6xl">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('student.courses') }}" class="hover:text-blue-600 font-medium">دوراتي</a>
        <span>/</span>
        <a href="{{ route('student.course-detail', $course->id) }}" class="hover:text-blue-600 font-medium truncate max-w-[200px]">{{ $course->title_ar ?? $course->title }}</a>
        <span>/</span>
        <span class="text-gray-900 font-bold truncate max-w-[200px]">{{ $lesson->title_ar ?? $lesson->title }}</span>
        @auth
            @php $isFav = \App\Models\Favorite::isFavorited(auth()->id(), \App\Models\Lesson::class, $lesson->id); @endphp
            <button x-data="{ fav: {{ $isFav ? 'true' : 'false' }} }"
                    @click="
                        fetch('{{ route('student.favorite.toggle') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ type: 'lesson', id: {{ $lesson->id }} })
                        }).then(r => r.json()).then(d => { fav = d.favorited; });
                    "
                    :class="fav ? 'text-red-500' : 'text-gray-300 hover:text-red-400'"
                    class="mr-2 p-1.5 rounded-lg hover:bg-red-50 transition-all shrink-0">
                <svg class="w-5 h-5" :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        @endauth
    </div>

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Video / Content --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                @if($contentIsVideo)
                    <div class="aspect-video bg-black">
                        @if(str_contains($lesson->video_url ?? '', 'youtube') || str_contains($lesson->video_url ?? '', 'youtu.be'))
                            <iframe src="{{ str_replace('watch?v=', 'embed/', $lesson->video_url) }}?rel=0" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                        @elseif($lesson->video_url)
                            <video controls class="w-full h-full" poster="{{ $lesson->content_url ?? '' }}">
                                <source src="{{ $lesson->video_url }}" type="video/mp4">
                            </video>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-400">فيديو غير متاح</div>
                        @endif
                    </div>
                @elseif($contentIsPdf)
                    <div class="aspect-video bg-gray-50 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-20 h-20 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <p class="text-gray-500 mb-3">ملف مرفق</p>
                            <a href="{{ $lesson->content_url }}" target="_blank" class="inline-flex bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">فتح الملف</a>
                        </div>
                    </div>
                @endif

                <div class="p-6 sm:p-8">
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mb-3">{{ $lesson->title_ar ?? $lesson->title }}</h1>
                    @if($lesson->description_ar ?? $lesson->description)
                        <p class="text-gray-500 leading-relaxed mb-4">{{ $lesson->description_ar ?? $lesson->description }}</p>
                    @endif

                    {{-- Text content --}}
                    @if($contentIsText && ($lesson->content_text ?? $lesson->content_url))
                        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                            @if($lesson->content_text)
                                {!! nl2br(e($lesson->content_text)) !!}
                            @else
                                <a href="{{ $lesson->content_url }}" target="_blank" class="text-blue-600 font-medium hover:underline">فتح المحتوى</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 flex items-center justify-between">
                <div>
                    @if($isCompleted)
                        <p class="text-emerald-600 font-bold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            تم الإكمال
                        </p>
                    @else
                        <p class="text-gray-500 text-sm">لم يتم إكمال هذا الدرس بعد</p>
                    @endif
                </div>
                <form action="{{ route('student.lesson.complete', [$course->id, $lesson->id]) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl font-bold text-sm transition-colors
                            {{ $isCompleted ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-emerald-500 hover:bg-emerald-600 text-white' }}">
                        {{ $isCompleted ? 'إلغاء الإكمال' : 'تحديد كمكتمل ✓' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Sidebar: Lesson List --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-4 sticky top-20">
                <h3 class="font-bold text-gray-900 text-sm mb-3">دروس الكورس</h3>
                <div class="space-y-1 max-h-[60vh] overflow-y-auto">
                    @foreach($allLessons as $idx => $l)
                        @php
                            $isCur = $l->id === $lesson->id;
                            $lCompleted = \App\Models\LessonCompletion::where('user_id', auth()->id())
                                ->where('lesson_id', $l->id)->exists();
                        @endphp
                        <a href="{{ route('student.lesson.show', [$course->id, $l->id]) }}"
                           class="flex items-center gap-2 p-2.5 rounded-xl text-sm transition-colors
                           {{ $isCur ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 text-xs
                                {{ $lCompleted ? 'bg-emerald-100 text-emerald-600' : ($isCur ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400') }}">
                                @if($lCompleted)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </span>
                            <span class="truncate">{{ $l->title_ar ?? $l->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Prev/Next Navigation --}}
    <div class="flex items-center justify-between mt-6 pb-8">
        @if($prevLesson)
            <a href="{{ route('student.lesson.show', [$course->id, $prevLesson->id]) }}" class="flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $prevLesson->title_ar ?? $prevLesson->title }}
            </a>
        @else
            <div></div>
        @endif

        @if($nextLesson)
            <a href="{{ route('student.lesson.show', [$course->id, $nextLesson->id]) }}" class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors">
                {{ $nextLesson->title_ar ?? $nextLesson->title }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <div></div>
        @endif
    </div>
</div>
@endsection
