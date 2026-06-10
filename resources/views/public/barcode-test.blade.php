<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $test->title }} - منصة المئة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto min-h-screen max-w-4xl px-4 py-8 sm:py-12">
        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-blue-700">اختبار باركود عام</p>
            <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ $test->title }}</h1>
            @if($test->description)
                <p class="mt-3 leading-7 text-slate-600">{{ $test->description }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-2 text-sm text-slate-600">
                <span class="rounded-full bg-slate-100 px-3 py-1">عدد الأسئلة: {{ $questions->count() }}</span>
                <span class="rounded-full bg-slate-100 px-3 py-1">درجة النجاح: {{ $test->settingsValue('passingScore', 60) }}%</span>
            </div>
        </section>

        @if($submission)
            <section class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-bold text-emerald-700">تم تسجيل الإجابة</p>
                @if($test->show_result_to_student)
                    <h2 class="mt-2 text-2xl font-extrabold">
                        نتيجتك {{ number_format((float) $submission->score_percentage, 2) }}%
                    </h2>
                    <p class="mt-2 text-slate-700">
                        صحيح: {{ $submission->correct_count }}،
                        خطأ: {{ $submission->incorrect_count }}،
                        بدون إجابة: {{ $submission->unanswered_count }}
                    </p>
                @else
                    <h2 class="mt-2 text-xl font-extrabold">تم إرسال الاختبار بنجاح.</h2>
                @endif
            </section>
        @endif

        @if(!$submission)
            <form method="POST" action="{{ route('public.barcode-test.submit', $test->slug) }}" class="space-y-5">
                @csrf
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="block">
                            <span class="mb-1 block text-sm font-bold">اسم الطالب</span>
                            <input name="student_name" value="{{ old('student_name') }}" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
                            @error('student_name')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        @if($test->collect_school)
                            <label class="block">
                                <span class="mb-1 block text-sm font-bold">المدرسة</span>
                                <input name="school_name" value="{{ old('school_name') }}" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
                                @error('school_name')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                            </label>
                        @endif
                        @if($test->collect_classroom)
                            <label class="block">
                                <span class="mb-1 block text-sm font-bold">الفصل</span>
                                <input name="classroom" value="{{ old('classroom') }}" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
                                @error('classroom')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                            </label>
                        @endif
                    </div>
                </section>

                @forelse($questions as $index => $question)
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-bold text-slate-500">السؤال {{ $index + 1 }}</p>
                        <h2 class="mt-2 text-lg font-extrabold leading-8">{{ $question->question_text_ar ?: $question->question_text }}</h2>
                        <div class="mt-4 grid gap-3">
                            @php
                                $options = $question->question_type === 'true_false'
                                    ? ['true' => 'صح', 'false' => 'خطأ']
                                    : ($question->options ?? []);
                            @endphp
                            @foreach($options as $optionIndex => $option)
                                @php
                                    $optionText = is_array($option) ? ($option['text_ar'] ?? $option['text'] ?? '') : $option;
                                @endphp
                                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 p-3 hover:border-blue-300 hover:bg-blue-50">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionIndex }}" class="h-4 w-4">
                                    <span>{{ $optionText }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <section class="rounded-lg border border-amber-200 bg-amber-50 p-5">
                        لا توجد أسئلة متاحة لهذا الاختبار حاليا.
                    </section>
                @endforelse

                @if($questions->isNotEmpty())
                    <button type="submit" class="w-full rounded-md bg-blue-700 px-5 py-3 font-extrabold text-white hover:bg-blue-800">
                        إرسال الإجابات
                    </button>
                @endif
            </form>
        @elseif($test->show_result_to_student && $test->settingsValue('showAnswers', true))
            <section class="space-y-4">
                @foreach(($submission->review ?? []) as $index => $item)
                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="font-extrabold">السؤال {{ $index + 1 }}</h3>
                            <span class="rounded-full px-3 py-1 text-sm font-bold {{ $item['is_correct'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $item['is_correct'] ? 'صحيح' : 'غير صحيح' }}
                            </span>
                        </div>
                        <p class="mt-2 leading-7">{{ $item['text'] }}</p>
                        @if(!empty($item['explanation']) && $test->settingsValue('showExplanations', true))
                            <p class="mt-3 rounded-md bg-slate-50 p-3 text-sm leading-7 text-slate-700">{{ $item['explanation'] }}</p>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif
    </main>
</body>
</html>
