@extends('layouts.student', ['activeTab' => request('tab', 'overview')])

@section('title', 'لوحة الطالب')

@section('content')
<div x-data="{ 
    tab: '{{ request('tab', 'overview') }}', 
    showVideo: false, 
    videoUrl: '', 
    bookModal: false,
    favActiveTab: 'favorites',
    favIndex: 0,
    favShowAnswer: false
}" class="max-w-6xl">

    {{-- Tab Navigation --}}
    <div class="flex gap-2 overflow-x-auto pb-3 mb-6 scrollbar-thin">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الرئيسية</button>
        <button @click="tab = 'my-courses'" :class="tab === 'my-courses' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">دوراتي</button>
        <button @click="tab = 'smart-path'" :class="tab === 'smart-path' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0 flex items-center gap-1.5">
            <span>التعلم الذكي</span>
            <span class="flex h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
        </button>
        <button @click="tab = 'sessions'" :class="tab === 'sessions' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">جلساتي</button>
        <button @click="tab = 'saher'" :class="tab === 'saher' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">مركز الاختبارات ساهر</button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">سجل اختباراتي</button>
        <button @click="tab = 'favorites'" :class="tab === 'favorites' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">مركز مراجعة الأسئلة</button>
        <button @click="tab = 'reports'" :class="tab === 'reports' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التقارير</button>
        <button @click="tab = 'plan'" :class="tab === 'plan' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">خطتي</button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدفوعات</button>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 font-bold text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- ==================== OVERVIEW ==================== --}}
    <div x-show="tab === 'overview'" x-transition>
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                <p class="text-blue-100 text-xs font-bold">الكورسات المسجلة</p>
                <p class="text-3xl font-black mt-1">{{ $totalCourses }}</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                <p class="text-emerald-100 text-xs font-bold">الكورسات المكتملة</p>
                <p class="text-3xl font-black mt-1">{{ $completedCourses }}</p>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                <p class="text-amber-100 text-xs font-bold">الاختبارات المحاولة</p>
                <p class="text-3xl font-black mt-1">{{ $attemptsCount }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                <p class="text-purple-100 text-xs font-bold">متوسط النتيجة</p>
                <p class="text-3xl font-black mt-1">{{ $avgScore ? number_format($avgScore, 1) : '—' }}%</p>
            </div>
            <div class="bg-gradient-to-br from-rose-500 to-rose-600 text-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                <p class="text-rose-100 text-xs font-bold">الدروس المكتملة</p>
                <p class="text-3xl font-black mt-1">{{ $completedLessons }}</p>
            </div>
        </div>

        {{-- Weekly Performance Mini Chart --}}
        @if($weeklyScores->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-900 text-sm">أداء آخر 7 أيام</h3>
                @php $trend = $weeklyScores->count() >= 2 ? ($weeklyScores->last() - $weeklyScores->first()) : 0; @endphp
                <span class="text-xs font-bold {{ $trend >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    @if($trend > 0) +{{ number_format($trend, 1) }}% @elseif($trend < 0) {{ number_format($trend, 1) }}% @else — @endif
                </span>
            </div>
            <div class="flex items-end gap-2 h-16">
                @for($i = 6; $i >= 0; $i--)
                    @php
                        $date = now()->subDays($i)->format('Y-m-d');
                        $score = $weeklyScores[$date] ?? null;
                        $height = $score ? max(($score / 100) * 56, 4) : 2;
                        $hasScore = !is_null($score);
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full rounded-md {{ $hasScore ? ($score >= 70 ? 'bg-emerald-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-red-500')) : 'bg-gray-100' }}" style="height: {{ $height }}px; min-height: 2px;"></div>
                        <span class="text-[10px] text-gray-400">{{ now()->subDays($i)->format('D') }}</span>
                    </div>
                @endfor
            </div>
        </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Recent Courses --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">أحدث التقدم في الكورسات</h2>
                    <button @click="tab = 'my-courses'" class="text-sm font-bold text-blue-600 hover:text-blue-800">عرض الكل ←</button>
                </div>
                @forelse($courses->take(4) as $course)
                    @php
                        $total = $course->lessons_count;
                        $completed = $course->lessonCompletions->count();
                        $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-white font-bold shrink-0">
                            {{ mb_substr($course->title_ar ?? $course->title, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $course->title_ar ?? $course->title }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 font-bold">{{ $pct }}%</span>
                            </div>
                        </div>
                        <a href="{{ route('student.course-detail', $course->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 shrink-0">استمر ←</a>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <p class="mb-2">لم تسجل في أي كورس بعد</p>
                        <a href="{{ route('courses') }}" class="text-blue-600 font-bold hover:underline">تصفح الكورسات</a>
                    </div>
                @endforelse
            </div>

            {{-- Learning Streak / Activity --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm text-center">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">استمرارية التعلم</h2>
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <span class="text-6xl">🔥</span>
                        <span class="text-5xl font-black {{ $streak > 0 ? 'text-orange-500' : 'text-gray-300' }}">{{ $streak }}</span>
                    </div>
                    <p class="text-gray-500 text-sm mb-4">{{ $streak > 0 ? "أيام متتالية من التعلم!" : "ابدأ رحلة التعلم اليوم" }}</p>
                    
                    {{-- Mini 7-day calendar --}}
                    <div class="flex justify-center gap-2">
                        @foreach($weekDays as $wd)
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-[10px] text-gray-400 font-bold">{{ $wd['day'] }}</span>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all
                                    {{ $wd['isToday'] ? 'ring-2 ring-blue-400 ring-offset-1' : '' }}
                                    {{ $wd['active'] ? 'bg-gradient-to-br from-orange-400 to-orange-500 text-white shadow-sm' : 'bg-gray-100 text-gray-300' }}">
                                    {{ now()->parse($wd['date'])->format('d') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Weak Skills Recommendation --}}
                @if($weakSkills->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">مهارات تحتاج تحسين</h2>
                    <div class="space-y-3">
                        @foreach($weakSkills as $sp)
                            @php
                                $pct = (float)($sp->mastery ?? 0);
                                $barColor = $pct < 40 ? 'bg-red-500' : ($pct < 60 ? 'bg-amber-500' : 'bg-emerald-500');
                            @endphp
                            <div class="p-3 rounded-xl bg-gray-50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 truncate max-w-[150px]">{{ $sp->skill?->name_ar ?? $sp->skill?->name ?? 'مهارة' }}</span>
                                    <span class="text-xs font-bold {{ $pct < 40 ? 'text-red-600' : ($pct < 60 ? 'text-amber-600' : 'text-emerald-600') }}">{{ number_format($pct, 0) }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== MY COURSES ==================== --}}
    <div x-show="tab === 'my-courses'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">دوراتي</h1>
            <a href="{{ route('courses') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">تصفح المزيد ←</a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courses as $course)
                @php
                    $total = $course->lessons_count;
                    $completed = $course->lessonCompletions->count();
                    $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                @endphp
                <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 group">
                    <div class="h-40 bg-gradient-to-br {{ $loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-white text-2xl font-black relative">
                        @if($course->price == 0)
                            <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">مجاني</span>
                        @else
                            <span class="absolute top-3 left-3 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">مدفوع</span>
                        @endif
                        {{ mb_substr($course->title_ar ?? $course->title, 0, 2) }}
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 mb-1 truncate">{{ $course->title_ar ?? $course->title }}</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">{{ $pct }}%</span>
                        </div>
                        <a href="{{ route('student.course-detail', $course->id) }}" class="block text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">متابعة التعلم</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <p class="mb-2">لم تسجل في أي كورس بعد</p>
                    <a href="{{ route('courses') }}" class="text-blue-600 font-bold hover:underline">تصفح الكورسات المتاحة</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== SMART PATH ==================== --}}
    <div x-show="tab === 'smart-path'" x-transition>
        <div class="text-right mb-6">
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>مسار التعلم الذكي</span>
                <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs px-2.5 py-1 rounded-full font-bold shadow-sm">تحليل AI المباشر</span>
            </h1>
            <p class="text-gray-500 text-sm mt-1">يحلل النظام أداءك في الاختبارات ويقترح الدروس والتدريبات لتغطية الفجوات المعرفية.</p>
        </div>

        @if(empty($smartPathRecommendations))
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">لا توجد توصيات حتى الآن</h3>
                <p class="text-gray-500 text-sm max-w-md mx-auto">عندما تخوض اختبارات أو محاكيات على المنصة، سيقوم النظام تلقائياً بتوليد دروس وتدريبات مخصصة لنقاط ضعفك هنا.</p>
            </div>
        @else
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($smartPathRecommendations as $idx => $rec)
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white {{ $rec['type'] === 'lesson' ? 'bg-blue-500' : 'bg-amber-500' }}">
                                    {{ $rec['type'] === 'lesson' ? 'درس مقترح' : 'اختبار تدريبي' }}
                                </span>
                                <span class="text-xs text-gray-400 font-bold flex items-center gap-1">⏱ {{ $rec['duration'] }}</span>
                            </div>
                            @if($rec['priority'] === 'high')
                                <span class="text-[10px] bg-red-50 text-red-600 px-2 py-0.5 rounded-md font-bold animate-pulse">أولوية قصوى 🔥</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-black text-gray-900 mb-2 truncate">{{ $rec['title'] }}</h3>
                        
                        <div class="bg-indigo-50/50 p-3 rounded-2xl mb-4">
                            <p class="text-xs text-indigo-800 leading-relaxed">💡 {{ $rec['reason'] }}</p>
                        </div>

                        <a href="{{ $rec['link'] }}" class="block text-center w-full bg-gradient-to-l from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-2.5 rounded-xl text-sm transition-all shadow-md group-hover:-translate-y-0.5">
                            {{ $rec['actionLabel'] }} ←
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ==================== SESSIONS ==================== --}}
    <div x-show="tab === 'sessions'" x-transition>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div class="text-right">
                <h1 class="text-2xl font-black text-gray-900">جلساتي المباشرة والخاصة</h1>
                <p class="text-gray-500 text-sm mt-1">احجز جلسات علاجية خاصة، أو ادخل الحصص المباشرة وروابط Zoom المتاحة لك.</p>
            </div>
            <button @click="bookModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-md shadow-indigo-100 flex items-center gap-2">
                ➕ حجز حصة خاصة
            </button>
        </div>

        {{-- Live Sessions list --}}
        <div class="space-y-6">
            @if($liveSessions->isEmpty())
                <div class="bg-white rounded-3xl border border-dashed border-gray-200 p-8 text-center">
                    <h3 class="font-bold text-gray-800 text-sm mb-2">لا توجد حصص بث مباشر حالياً</h3>
                    <p class="text-gray-500 text-xs">عند إعداد اجتماعات Zoom أو بث YouTube Live من المدرسين ستظهر هنا مباشرة.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($liveSessions as $live)
                        <div class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                    🎥
                                </div>
                                <div class="text-right min-w-0">
                                    <h3 class="font-bold text-gray-900 text-sm truncate">{{ $live->title_ar ?? $live->title }}</h3>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $live->course->title_ar ?? $live->course->title }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-xs text-gray-600 border-t border-b border-gray-50 py-3 my-3">
                                <div class="flex justify-between">
                                    <span>الموعد:</span>
                                    <span class="font-bold text-gray-900">{{ $live->meeting_date ? $live->meeting_date->format('Y-m-d h:i A') : 'غير محدد' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>المزود:</span>
                                    <span class="font-bold text-gray-900">{{ strtoupper($live->content_type) }}</span>
                                </div>
                            </div>
                            @if($live->meeting_url)
                                <a href="{{ $live->meeting_url }}" target="_blank" class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-sm transition-colors shadow-sm">
                                    دخول البث الحقيقي 🔗
                                </a>
                            @else
                                <button disabled class="w-full bg-gray-100 text-gray-400 font-bold py-2 rounded-xl text-sm cursor-not-allowed">
                                    بانتظار الرابط
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Booked private sessions log --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 text-base mb-4">طلبات جلساتك الخاصة</h3>
                @forelse($bookedSessions as $session)
                    <div class="flex items-center justify-between border-b border-gray-50 py-3.5 last:border-0">
                        <div class="text-right">
                            <p class="font-bold text-gray-900 text-sm">{{ $session->description }}</p>
                            @if(isset($session->data['date']))
                                <p class="text-xs text-gray-400 mt-1">الموعد المقترح: {{ $session->data['date'] }} | الوقت: {{ $session->data['time'] }}</p>
                            @endif
                        </div>
                        <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full">تحت المراجعة</span>
                    </div>
                @empty
                    <p class="text-center text-gray-400 text-sm py-4">لم تقم بطلب أي جلسة خاصة حتى الآن.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ==================== SAHER TEST CENTER ==================== --}}
    <div x-show="tab === 'saher'" x-transition>
        <div class="text-right mb-6">
            <h1 class="text-2xl font-black text-gray-900">مركز الاختبارات ساهر</h1>
            <p class="text-gray-500 text-sm mt-1">ابدأ قياسًا جديدًا مخصصًا لتحديد مستواك الحقيقي في المواد.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- Self Quiz Form --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-black text-gray-900 text-lg mb-4">ساهر الذاتي (توليد اختبار مخصص)</h3>
                <form action="{{ route('student.saher.generate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2">اختر المسار الدراسي</label>
                        <select name="path_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">كل المسارات</option>
                            @foreach($paths as $path)
                                <option value="{{ $path->id }}">{{ $path->name_ar ?? $path->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2">المادة الدراسية</label>
                        <select name="subject_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">كل المواد</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name_ar ?? $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">مستوى الصعوبة</label>
                            <select name="difficulty" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="easy">سهل</option>
                                <option value="medium" selected>متوسط</option>
                                <option value="hard">صعب</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">عدد الأسئلة</label>
                            <select name="question_count" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="5">5 أسئلة</option>
                                <option value="10">10 أسئلة</option>
                                <option value="15" selected>15 سؤال</option>
                                <option value="20">20 سؤال</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 rounded-xl text-sm transition-colors shadow-md shadow-amber-100">
                        ⚡ توليد اختبار ساهر والبدء الآن
                    </button>
                </form>
            </div>

            {{-- Prepared Quizzes list --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-black text-gray-900 text-lg mb-4">الاختبارات الجاهزة الموجهة لك</h3>
                <div class="space-y-3 max-h-[360px] overflow-y-auto pr-1">
                    @forelse($saherQuizzes as $saher)
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                            <div class="text-right">
                                <p class="font-bold text-gray-900 text-sm">{{ $saher->title_ar ?? $saher->title }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $saher->questions->count() }} سؤال | {{ $saher->time_limit }} دقيقة</p>
                            </div>
                            <a href="{{ route('student.quiz.show', $saher->id) }}" class="bg-white border border-gray-200 hover:border-amber-400 text-amber-600 font-bold px-4 py-2 rounded-xl text-xs transition-colors">
                                دخول ←
                            </a>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 text-sm py-8">لا توجد اختبارات جاهزة حالياً.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== QUIZZES (ATTEMPTS) ==================== --}}
    <div x-show="tab === 'quizzes'" x-transition>
        <div class="text-right mb-6">
            <h1 class="text-2xl font-black text-gray-900">سجل اختباراتي السابقة</h1>
            <p class="text-gray-500 text-sm mt-1">تراجع المحاولات والنتائج وتفاصيل الإجابات لكل اختبار.</p>
        </div>

        @if(empty($attemptsGrouped))
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
                <p class="text-gray-500">لا توجد محاولات مسجلة لك حتى الآن.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($attemptsGrouped as $group)
                    <div x-data="{ open: false }" class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                        <div @click="open = !open" class="p-5 flex items-center justify-between cursor-pointer hover:bg-gray-50/50 transition-colors">
                            <div class="text-right">
                                <h3 class="font-black text-gray-900 text-sm">{{ $group['quiz_title'] }}</h3>
                                <p class="text-xs text-gray-400 mt-1">آخر محاولة: {{ $group['latest_attempt']->created_at->diffForHumans() }} | عدد المحاولات: {{ count($group['attempts']) }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full">أفضل نتيجة: {{ number_format($group['best_score'], 0) }}%</span>
                                <span class="text-gray-400 font-bold text-lg" x-text="open ? '▲' : '▼'"></span>
                            </div>
                        </div>

                        <div x-show="open" x-transition class="border-t border-gray-50 bg-gray-50/50 p-4 space-y-2">
                            @foreach($group['attempts'] as $idx => $att)
                                <div class="flex items-center justify-between bg-white p-3 rounded-2xl border border-gray-100 text-xs">
                                    <div>
                                        <span class="font-bold text-gray-700">محاولة #{{ count($group['attempts']) - $idx }}</span>
                                        <span class="text-gray-400 mr-2">{{ $att->created_at->format('Y-m-d h:i A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="font-bold {{ $att->result && $att->result->passed ? 'text-emerald-600' : 'text-red-500' }}">
                                            الدرجة: {{ $att->result ? number_format($att->result->score_percentage, 0) : '0' }}%
                                        </span>
                                        <a href="{{ route('student.quiz.result.attempt', ['quiz' => $att->quiz_id, 'attempt' => $att->id]) }}" class="text-blue-600 hover:underline">مراجعة الأسئلة ←</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ==================== REVIEW CENTER (FAVORITES) ==================== --}}
    <div x-show="tab === 'favorites'" x-transition>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="text-right">
                <h1 class="text-2xl font-black text-gray-900">مركز مراجعة الأسئلة</h1>
                <p class="text-gray-500 text-sm mt-1">تصفح وراجع الأسئلة المفضلة، الأسئلة للمراجعة لاحقاً، والأسئلة الخاطئة.</p>
            </div>
            
            {{-- Review Sub-tabs --}}
            <div class="grid grid-cols-3 gap-1 rounded-2xl bg-gray-100 p-1">
                <button @click="favActiveTab = 'favorites'; favIndex = 0; favShowAnswer = false" :class="favActiveTab === 'favorites' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">المفضلة</button>
                <button @click="favActiveTab = 'reviewLater'; favIndex = 0; favShowAnswer = false" :class="favActiveTab === 'reviewLater' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">لاحقاً</button>
                <button @click="favActiveTab = 'mistakes'; favIndex = 0; favShowAnswer = false" :class="favActiveTab === 'mistakes' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">الأخطاء</button>
            </div>
        </div>

        {{-- Question Center Logic --}}
        @php
            $favList = $favQuestions->toArray();
            $reviewList = $reviewLaterQuestions->toArray();
            $mistakeList = $mistakeQuestions->toArray();
        @endphp

        <div x-data="{
            getQuestionsList() {
                if (this.favActiveTab === 'favorites') return @json($favList);
                if (this.favActiveTab === 'reviewLater') return @json($reviewList);
                return @json($mistakeList);
            },
            getCurrentQuestion() {
                const list = this.getQuestionsList();
                return list[this.favIndex] || null;
            },
            removeCurrent() {
                const q = this.getCurrentQuestion();
                if (!q) return;
                let route = '';
                let body = {};

                if (this.favActiveTab === 'favorites') {
                    route = '{{ route('student.favorite.toggle') }}';
                    body = { type: 'question', id: q.id };
                } else if (this.favActiveTab === 'reviewLater') {
                    route = '{{ route('student.review-later.toggle') }}';
                    body = { question_id: q.id };
                }

                if (route) {
                    fetch(route, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(body)
                    }).then(r => r.json()).then(() => {
                        window.location.reload(); // Simple reload to refresh database state
                    });
                }
            }
        }">
            <template x-if="getQuestionsList().length === 0">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
                    <p class="text-gray-400 text-sm">لا توجد أسئلة في هذا القسم حالياً.</p>
                </div>
            </template>

            <template x-if="getQuestionsList().length > 0 && getCurrentQuestion()">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="bg-amber-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            السؤال <span x-text="favIndex + 1"></span> من <span x-text="getQuestionsList().length"></span>
                        </span>
                        
                        <div class="flex gap-2">
                            <button @click="removeCurrent()" class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold px-3 py-2 rounded-xl transition-colors">
                                🗑 إزالة من القائمة
                            </button>
                        </div>
                    </div>

                    {{-- Question Card --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-md overflow-hidden">
                        <div class="p-6 md:p-8 min-h-[160px] flex flex-col items-center justify-center border-b border-gray-50">
                            {{-- Image if exists --}}
                            <template x-if="getCurrentQuestion().image_url">
                                <img :src="getCurrentQuestion().image_url" class="max-h-[300px] object-contain rounded-xl mb-4 border border-gray-100">
                            </template>
                            <p class="text-base md:text-lg font-bold text-gray-900 text-center leading-relaxed" x-html="getCurrentQuestion().question_text_ar || getCurrentQuestion().question_text"></p>
                        </div>

                        {{-- Options --}}
                        <div class="p-6 bg-gray-50/50">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <template x-for="(option, idx) in getCurrentQuestion().options" :key="idx">
                                    <div :class="favShowAnswer && (idx == getCurrentQuestion().correct_answer || (typeof option === 'object' && option.is_correct)) ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-gray-200 bg-white'" class="border-2 rounded-2xl p-4 flex justify-between items-center transition-all">
                                        <span class="font-bold text-sm" x-text="typeof option === 'object' ? option.text_ar || option.text : option"></span>
                                        <span :class="favShowAnswer && (idx == getCurrentQuestion().correct_answer || (typeof option === 'object' && option.is_correct)) ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300'" class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs">
                                            ✔
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Explanation if ShowAnswer --}}
                        <template x-if="favShowAnswer && (getCurrentQuestion().explanation_ar || getCurrentQuestion().explanation)">
                            <div class="p-6 bg-amber-50/40 border-t border-gray-100">
                                <p class="text-sm text-gray-700 leading-relaxed"><strong class="text-amber-800">💡 الشرح والتفصيل:</strong> <span x-text="getCurrentQuestion().explanation_ar || getCurrentQuestion().explanation"></span></p>
                            </div>
                        </template>

                        {{-- Navigation bar --}}
                        <div class="bg-white p-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex gap-2">
                                <button @click="if(favIndex > 0) { favIndex--; favShowAnswer = false; }" :disabled="favIndex === 0" class="bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs transition-colors">السابق</button>
                                <button @click="if(favIndex < getQuestionsList().length - 1) { favIndex++; favShowAnswer = false; }" :disabled="favIndex === getQuestionsList().length - 1" class="bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs transition-colors">التالي</button>
                            </div>

                            <div class="flex gap-2">
                                <button @click="favShowAnswer = !favShowAnswer" class="border border-blue-200 bg-blue-50 text-blue-700 font-bold px-4 py-2 rounded-xl text-xs hover:bg-blue-100 transition-colors">
                                    <span x-text="favShowAnswer ? 'إخفاء الحل' : 'إظهار الحل'"></span>
                                </button>
                                <template x-if="getCurrentQuestion().video_url">
                                    <button @click="videoUrl = getCurrentQuestion().video_url; showVideo = true;" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition-colors">
                                        📺 شرح الفيديو
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ==================== REPORTS ==================== --}}
    <div x-show="tab === 'reports'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">تقارير الأداء التفصيلية</h1>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4">متوسط النتيجة الإجمالي</h3>
                <div class="text-center py-6">
                    <div class="w-36 h-36 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-4 shadow-md">
                        <span class="text-4xl font-black text-white">{{ $avgScore ? number_format($avgScore, 0) : '—' }}%</span>
                    </div>
                    <p class="text-gray-500 text-sm">أداء الطالب العام في كافة المحاولات</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4">المهارات الضعيفة التي تحتاج تركيز</h3>
                <div class="space-y-3">
                    @forelse($weakSkills as $sp)
                        <div class="p-3 bg-red-50/50 rounded-2xl border border-red-100 flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-800">{{ $sp->skill?->name_ar ?? $sp->skill?->name }}</span>
                            <span class="bg-red-500 text-white font-bold px-2 py-0.5 rounded">{{ number_format((float)$sp->mastery) }}%</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-6">مستواك ممتاز في جميع المهارات!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== STUDY PLAN ==================== --}}
    <div x-show="tab === 'plan'" x-transition x-data="studyPlan()" x-init="init()">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">خطتي الدراسية</h1>
        </div>

        @if($studyPlans->isNotEmpty())
            <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm p-6 mb-6">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-black text-gray-900">خطط علاج من المدرسة</h2>
                        <p class="text-sm text-gray-500 mt-1">خطط أنشأها المشرف أو مدير المدرسة بناء على نتائجك ومهاراتك الأضعف.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ $studyPlans->count() }} خطة فعالة</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($studyPlans as $plan)
                        <article class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-gray-900">{{ $plan->name }}</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $plan->skill?->name_ar ?: $plan->skill?->name ?: 'خطة عامة' }}
                                        @if($plan->school)
                                            - {{ $plan->school->name_ar ?: $plan->school->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-600">{{ $plan->daily_minutes }} دقيقة/يوم</span>
                            </div>
                            @if($plan->tasks)
                                <ul class="mt-4 list-disc space-y-1 pr-5 text-sm text-gray-700">
                                    @foreach(array_slice($plan->tasks, 0, 5) as $task)
                                        <li>{{ is_array($task) ? ($task['text'] ?? $task['title'] ?? '') : $task }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-bold text-gray-500">
                                @if($plan->starts_at)
                                    <span>من {{ $plan->starts_at->format('Y-m-d') }}</span>
                                @endif
                                @if($plan->ends_at)
                                    <span>إلى {{ $plan->ends_at->format('Y-m-d') }}</span>
                                @endif
                                @if($plan->creator)
                                    <span>بواسطة {{ $plan->creator->name }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Weekly Progress --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4 text-sm">التقدم في مهامي الأسبوعية</h2>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-3xl font-black text-blue-600" x-text="weeklyDone"></div>
                    <p class="text-[10px] text-gray-500 font-bold">مكتمل</p>
                </div>
                <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all" :style="`width: ${weeklyPct}%`"></div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-gray-900" x-text="tasks.length"></div>
                    <p class="text-[10px] text-gray-500 font-bold">إجمالي</p>
                </div>
            </div>
        </div>

        {{-- Add Task --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-6">
            <form @submit.prevent="addTask">
                <div class="flex gap-3">
                    <input type="text" x-model="newTaskText" placeholder="أضف مهمة دراسية جديدة خطوة بخطوة..." required
                           class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select x-model="newTaskDay" class="px-3 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <template x-for="(day, i) in days" :key="i">
                            <option :value="i" x-text="day"></option>
                        </template>
                    </select>
                    <button type="submit" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-colors">إضافة</button>
                </div>
            </form>
        </div>

        {{-- Weekly Schedule --}}
        <div class="grid md:grid-cols-2 gap-4">
            <template x-for="(day, dayIdx) in days" :key="dayIdx">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center justify-between">
                        <span x-text="day"></span>
                        <span class="text-xs text-gray-400 font-bold" x-text="`(${tasksByDay(dayIdx).length} مهام)`"></span>
                    </h3>
                    <div class="space-y-2">
                        <template x-for="(task, tIdx) in tasksByDay(dayIdx)" :key="tIdx">
                            <div class="flex items-center gap-3 p-2.5 rounded-xl transition-all"
                                 :class="task.done ? 'bg-emerald-50/50' : 'hover:bg-gray-50'">
                                <button @click="toggleTask(dayIdx, tIdx)"
                                        class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                        :class="task.done ? 'bg-emerald-500 text-white' : 'border-2 border-gray-300'">
                                    <svg x-show="task.done" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <span class="flex-1 text-xs" :class="task.done ? 'text-gray-400 line-through' : 'text-gray-700 font-bold'" x-text="task.text"></span>
                                <button @click="removeTask(dayIdx, tIdx)" class="text-gray-300 hover:text-red-500 transition-colors">
                                    🗑
                                </button>
                            </div>
                        </template>
                        <p x-show="tasksByDay(dayIdx).length === 0" class="text-gray-300 text-xs text-center py-4">لا توجد مهام مضافة لهذا اليوم.</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ==================== PAYMENTS ==================== --}}
    <div x-show="tab === 'payments'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">طلبات الاشتراك والمدفوعات</h1>
        <p class="text-gray-500 text-sm mb-6">سجل وطلبات الباقات والكورسات الخاصة بك.</p>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-right p-4 font-bold text-gray-600">الكورس أو الباقة</th>
                            <th class="text-center p-4 font-bold text-gray-600">المبلغ</th>
                            <th class="text-center p-4 font-bold text-gray-600">حالة الطلب</th>
                            <th class="text-center p-4 font-bold text-gray-600">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentRequests as $p)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-4 font-bold text-gray-900">{{ $p->course->title_ar ?? $p->course->title ?? 'باقة تعليمية' }}</td>
                                <td class="p-4 text-center font-black text-gray-900">{{ number_format($p->amount, 0) }} ريال</td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($p->status === 'approved') bg-emerald-100 text-emerald-700
                                        @elseif($p->status === 'rejected') bg-red-100 text-red-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        {{ $p->status === 'approved' ? 'مقبول' : ($p->status === 'rejected' ? 'مرفوض' : 'قيد المراجعة') }}
                                    </span>
                                </td>
                                <td class="p-4 text-center text-gray-500 text-xs">{{ $p->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-8 text-center text-gray-400">لا توجد مدفوعات مسجلة بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== MODALS ==================== --}}

    {{-- Book Private Session Modal --}}
    <div x-show="bookModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" x-transition>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 text-right space-y-4" @click.away="bookModal = false">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                <h3 class="font-black text-gray-900 text-lg">طلب حجز حصة خاصة</h3>
                <button @click="bookModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">×</button>
            </div>
            <form action="{{ route('student.sessions.book') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">المادة / مهارة الحصة</label>
                    <select name="target" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none">
                        @foreach($subjects as $sub)
                            <option value="مادة: {{ $sub->name_ar ?? $sub->name }}">{{ $sub->name_ar ?? $sub->name }}</option>
                        @endforeach
                        <option value="أخرى">أخرى (يرجى التوضيح في الملاحظات)</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2">التاريخ المفضل</label>
                        <input type="date" name="date" required min="{{ now()->format('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2">الوقت المفضل</label>
                        <select name="time" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none">
                            <option value="04:00 PM - 05:00 PM">04:00 م - 05:00 م</option>
                            <option value="05:00 PM - 06:00 PM">05:00 م - 06:00 م</option>
                            <option value="08:00 PM - 09:00 PM">08:00 م - 09:00 م</option>
                            <option value="09:00 PM - 10:00 PM">09:00 م - 10:00 م</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات للمدرس (اختياري)</label>
                    <textarea name="notes" rows="3" placeholder="حدد المفاهيم الصعبة التي ترغب في التركيز عليها..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:outline-none resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl text-sm transition-colors shadow-md">
                    تأكيد الحجز وإرسال الطلب
                </button>
            </form>
        </div>
    </div>

    {{-- Video Explanation Modal --}}
    <div x-show="showVideo" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4" x-transition>
        <div class="bg-white rounded-3xl max-w-3xl w-full p-4 text-right space-y-4" @click.away="showVideo = false; videoUrl = '';">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <h3 class="font-bold text-gray-900 text-sm">شرح السؤال فيديو</h3>
                <button @click="showVideo = false; videoUrl = '';" class="text-gray-400 hover:text-gray-600 text-xl font-bold">×</button>
            </div>
            <div class="aspect-video bg-black rounded-2xl overflow-hidden">
                <template x-if="showVideo && videoUrl">
                    <iframe :src="videoUrl.replace('watch?v=', 'embed/')" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                </template>
            </div>
        </div>
    </div>

</div>

{{-- Study Plan LocalStorage Script --}}
<script>
    function studyPlan() {
        return {
            days: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
            tasks: [],
            newTaskText: '',
            newTaskDay: 0,
            storageKey: 'study_plan_{{ auth()->id() }}',

            init() {
                const saved = localStorage.getItem(this.storageKey);
                if (saved) this.tasks = JSON.parse(saved);
            },
            save() {
                localStorage.setItem(this.storageKey, JSON.stringify(this.tasks));
            },
            tasksByDay(dayIdx) {
                return this.tasks.filter(t => t.day === dayIdx);
            },
            get weeklyDone() {
                return this.tasks.filter(t => t.done).length;
            },
            get weeklyPct() {
                return this.tasks.length ? Math.round((this.weeklyDone / this.tasks.length) * 100) : 0;
            },
            addTask() {
                if (!this.newTaskText.trim()) return;
                this.tasks.push({ day: this.newTaskDay, text: this.newTaskText.trim(), done: false });
                this.newTaskText = '';
                this.save();
            },
            toggleTask(dayIdx, tIdx) {
                const task = this.tasksByDay(dayIdx)[tIdx];
                if (task) { task.done = !task.done; this.save(); }
            },
            removeTask(dayIdx, tIdx) {
                const globalIdx = this.tasks.findIndex(t => t === this.tasksByDay(dayIdx)[tIdx]);
                if (globalIdx > -1) { this.tasks.splice(globalIdx, 1); this.save(); }
            }
        };
    }
</script>
@endsection
