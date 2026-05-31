@extends('layouts.student', ['activeTab' => 'reports'])

@section('title', 'التقارير')

@php
    use App\Models\QuizResult;
    use App\Models\LessonCompletion;
    use App\Models\QuizAttempt;

    $user = Auth::user();
    $results = QuizResult::where('user_id', $user->id)->latest()->take(20)->get()->reverse();
    $avgScore = $results->avg('score_percentage');
    $totalQuizzes = $results->count();
    $passed = $results->where('passed', true)->count();
    $failed = $results->where('passed', false)->count();
    $totalCorrect = $results->sum('correct_count');
    $totalQuestions = $results->sum('total_questions');
    $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;
    $completedLessons = LessonCompletion::where('user_id', $user->id)->count();
    $attempts = QuizAttempt::where('user_id', $user->id)->count();
    $lastResult = QuizResult::where('user_id', $user->id)->latest()->first();

    // Aggregate skill breakdown across all results
    $skillAgg = [];
    $allResults = QuizResult::where('user_id', $user->id)->whereNotNull('skill_breakdown')->get();
    foreach ($allResults as $r) {
        foreach ($r->skill_breakdown ?? [] as $sb) {
            $sid = $sb['skill_id'] ?? 0;
            if (!isset($skillAgg[$sid])) {
                $skillAgg[$sid] = [
                    'skill_name' => $sb['skill_name'] ?? 'مهارة',
                    'subject_name' => $sb['subject_name'] ?? '',
                    'correct' => 0,
                    'total' => 0,
                ];
            }
            $skillAgg[$sid]['correct'] += $sb['correct'] ?? 0;
            $skillAgg[$sid]['total'] += $sb['total'] ?? 0;
        }
    }
    $skillSummary = [];
    foreach ($skillAgg as $sid => $s) {
        $mastery = $s['total'] > 0 ? round(($s['correct'] / $s['total']) * 100, 1) : 0;
        $skillSummary[] = [
            'skill_name' => $s['skill_name'],
            'subject_name' => $s['subject_name'],
            'mastery' => $mastery,
            'correct' => $s['correct'],
            'total' => $s['total'],
            'status' => $mastery >= 80 ? 'strong' : ($mastery >= 60 ? 'average' : 'weak'),
        ];
    }
    usort($skillSummary, fn($a, $b) => $a['mastery'] <=> $b['mastery']);
@endphp

@section('content')
<div class="max-w-6xl">
    <h1 class="text-2xl font-black text-gray-900 mb-2">تقارير الأداء</h1>
    <p class="text-gray-500 text-sm mb-6">حلل مستواك وتعرف على نقاط القوة والضعف</p>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">إجمالي الاختبارات</p>
            <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalQuizzes }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">متوسط النتيجة</p>
            <p class="text-3xl font-black {{ $avgScore >= 70 ? 'text-emerald-600' : ($avgScore >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $avgScore ? number_format($avgScore, 1) : '—' }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">دقة الإجابات</p>
            <p class="text-3xl font-black {{ $accuracy >= 70 ? 'text-emerald-600' : ($accuracy >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $accuracy ?: '—' }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">الدروس المكتملة</p>
            <p class="text-3xl font-black text-blue-600 mt-1">{{ $completedLessons }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Score Trend --}}
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">اتجاه الأداء</h2>
            @if($results->count() > 1)
                <div class="relative h-48" x-data="{}">
                    <svg viewBox="0 0 {{ max($results->count() * 60, 200) }} 200" class="w-full h-full">
                        <polyline
                            fill="none"
                            stroke="url(#trendGrad)"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            points="
                                @foreach($results as $i => $r)
                                    {{ ($i * 60) + 30 }},{{ 180 - ($r->score_percentage * 1.6) }}
                                @endforeach
                            "
                        />
                        <defs>
                            <linearGradient id="trendGrad" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="#3b82f6" />
                                <stop offset="100%" stop-color="#10b981" />
                            </linearGradient>
                        </defs>
                        @foreach($results as $i => $r)
                            <circle cx="{{ ($i * 60) + 30 }}" cy="{{ 180 - ($r->score_percentage * 1.6) }}" r="4"
                                    fill="{{ $r->passed ? '#10b981' : '#ef4444' }}" class="hover:r-6 transition-all">
                                <title>{{ number_format($r->score_percentage, 0) }}% - {{ $r->created_at->format('d M') }}</title>
                            </circle>
                        @endforeach
                        {{-- Grid lines --}}
                        <line x1="0" y1="20" x2="100%" y2="20" stroke="#f3f4f6" stroke-width="1"/>
                        <line x1="0" y1="90" x2="100%" y2="90" stroke="#f3f4f6" stroke-width="1"/>
                        <line x1="0" y1="160" x2="100%" y2="160" stroke="#f3f4f6" stroke-width="1"/>
                    </svg>
                    <div class="flex justify-between mt-2 text-xs text-gray-400">
                        <span>{{ $results->first()->created_at->format('d M') }}</span>
                        <span>{{ $results->last()->created_at->format('d M') }}</span>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-center py-16">قم بحل اختبارين على الأقل لرؤية اتجاه الأداء</p>
            @endif

            {{-- Recent Results List --}}
            <div class="mt-6 space-y-3">
                <h3 class="font-bold text-gray-900 text-sm">آخر النتائج</h3>
                @forelse($results->reverse()->take(10) as $result)
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-500 w-20 shrink-0">{{ $result->created_at->format('d M') }}</span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-900 truncate">{{ $result->quiz_title }}</span>
                                <span class="text-sm font-bold {{ $result->passed ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($result->score_percentage, 0) }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $result->score_percentage >= 70 ? 'bg-emerald-500' : ($result->score_percentage >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $result->score_percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">لا توجد نتائج بعد. قم بحل بعض الاختبارات!</p>
                @endforelse
            </div>
        </div>

        {{-- Overall Stats --}}
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
                <h2 class="text-lg font-bold text-gray-900 mb-6">الأداء العام</h2>
                <div class="w-40 h-40 rounded-full bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <div class="w-32 h-32 rounded-full bg-white flex items-center justify-center">
                        <span class="text-4xl font-black {{ $avgScore >= 70 ? 'text-emerald-600' : ($avgScore >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $avgScore ? number_format($avgScore, 0) : '—' }}%</span>
                    </div>
                </div>
                <p class="text-gray-500 text-sm">متوسط النتيجة الإجمالي</p>
                <div class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">ناجح</span>
                        <span class="font-bold text-emerald-600">{{ $passed }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">راسب</span>
                        <span class="font-bold text-red-600">{{ $failed }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">إجمالي الإجابات الصحيحة</span>
                        <span class="font-bold text-gray-900">{{ $totalCorrect }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">إجمالي الأسئلة</span>
                        <span class="font-bold text-gray-900">{{ $totalQuestions }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">محاولات الاختبارات</span>
                        <span class="font-bold text-gray-900">{{ $attempts }}</span>
                    </div>
                </div>
            </div>

            {{-- Skill Analysis Summary --}}
            @if(!empty($skillSummary))
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6">تحليل المهارات</h2>
                <div class="space-y-4">
                    @php
                        $strongCount = count(array_filter($skillSummary, fn($s) => $s['status'] === 'strong'));
                        $weakCount = count(array_filter($skillSummary, fn($s) => $s['status'] === 'weak'));
                        $avgCount = count(array_filter($skillSummary, fn($s) => $s['status'] === 'average'));
                    @endphp
                    <div class="flex gap-3 text-xs mb-4">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium">{{ $strongCount }} متقن</span>
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-medium">{{ $avgCount }} متوسط</span>
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 font-medium">{{ $weakCount }} ضعيف</span>
                    </div>
                    @foreach($skillSummary as $s)
                        @php
                            $barColor = match($s['status']) {
                                'strong' => 'bg-emerald-500',
                                'average' => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                            $textColor = match($s['status']) {
                                'strong' => 'text-emerald-700',
                                'average' => 'text-amber-700',
                                default => 'text-red-700',
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-900">{{ $s['skill_name'] }}</span>
                                <span class="font-bold {{ $textColor }}">{{ number_format($s['mastery'], 0) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $s['mastery'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">إجراءات سريعة</h3>
                <div class="space-y-3">
                    <a href="{{ route('student.quiz.list') }}" class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-gray-900">تجربة اختبار جديد</p>
                            <p class="text-xs text-gray-500">اختبر مستواك الآن</p>
                        </div>
                    </a>
                    <a href="{{ route('student.courses') }}" class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-gray-900">متابعة التعلم</p>
                            <p class="text-xs text-gray-500">ارجع إلى كورساتك</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
