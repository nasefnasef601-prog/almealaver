@php
    $tab = request('tab', 'overview');
@endphp
@extends('layouts.student', ['activeTab' => $tab])

@section('title', 'لوحة المشرف')

@php
    use App\Models\User;
    use App\Models\School;
    use App\Models\Course;
    use App\Models\QuizResult;
    use App\Models\QuizAttempt;
    use App\Models\LessonCompletion;
    use App\Models\PaymentRequest;
    use App\Models\AccessGrant;
    use App\Models\Quiz;
    use Carbon\Carbon;

    $totalSchools = School::count();
    $totalUsers = User::count();
    $totalStudents = User::role('student')->count();
    $totalTeachers = User::role('teacher')->count();
    $totalCourses = Course::count();
    $totalQuizzes = Quiz::count();
    $totalQuizAttempts = QuizAttempt::count();
    $totalCompletions = LessonCompletion::count();
    $totalRevenue = (int) PaymentRequest::where('status', 'approved')->sum('amount');
    $pendingPayments = PaymentRequest::whereIn('status', ['pending', 'pending_manual_review'])->count();
    $activeEnrollments = AccessGrant::where('status', 'active')->count();

    // Registrations chart data (last 14 days)
    $chartLabels = [];
    $chartData = [];
    for ($i = 13; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $chartLabels[] = now()->subDays($i)->format('M d');
        $chartData[] = User::whereDate('created_at', $date)->count();
    }
    $maxReg = max($chartData) ?: 1;

    // Course enrollment data
    $topCourses = Course::withCount('accessGrants')
        ->orderByDesc('access_grants_count')
        ->limit(5)
        ->get();

    // Schools with count
    $schools = School::withCount('users')->latest()->get();

    // Teachers with course count
    $teachers = User::role('teacher')
        ->withCount(['courses as course_count' => fn($q) => $q->where('is_published', true)])
        ->latest()
        ->take(50)
        ->get();

    // Students with stats
    $students = User::role('student')
        ->withCount(['quizResults as quiz_count', 'lessonCompletions as lesson_count'])
        ->latest()
        ->take(50)
        ->get();

    // Latest users
    $latestUsers = User::latest()->take(10)->get();

    // Recent payments
    $recentPayments = PaymentRequest::with('user', 'course')->latest()->take(10)->get();

    // Quiz pass rate
    $totalResults = QuizResult::count();
    $passedResults = QuizResult::where('passed', true)->count();
    $passRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100) : 0;

    // Today's stats
    $todayUsers = User::whereDate('created_at', today())->count();
    $todayCompletions = LessonCompletion::whereDate('created_at', today())->count();
    $todayQuizAttempts = QuizAttempt::whereDate('created_at', today())->count();
    $todayPayments = PaymentRequest::whereDate('created_at', today())->count();
@endphp

@section('content')
<div class="max-w-6xl" x-data="{ tab: '{{ $tab }}' }">

    {{-- Tab Navigation --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-6 scrollbar-thin">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الرئيسية</button>
        <button @click="tab = 'schools'" :class="tab === 'schools' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدارس</button>
        <button @click="tab = 'teachers'" :class="tab === 'teachers' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المعلمون</button>
        <button @click="tab = 'students'" :class="tab === 'students' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الطلاب</button>
        <button @click="tab = 'reports'" :class="tab === 'reports' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التقارير</button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدفوعات</button>
    </div>

    {{-- ==================== OVERVIEW ==================== --}}
    <div x-show="tab === 'overview'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">لوحة المشرف</h1>
        <p class="text-gray-500 text-sm mb-6">نظرة عامة على المنصة</p>

        {{-- Main Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-blue-100 text-sm">{{ $totalStudents }}</p>
                <p class="text-3xl font-black mt-1">الطلاب</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-emerald-100 text-sm">{{ $totalTeachers }}</p>
                <p class="text-3xl font-black mt-1">المعلمون</p>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-amber-100 text-sm">{{ $totalCourses }}</p>
                <p class="text-3xl font-black mt-1">الكورسات</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-purple-100 text-sm">{{ $totalSchools }}</p>
                <p class="text-3xl font-black mt-1">المدارس</p>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-500">إجمالي المستخدمين</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-500">اختبارات</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $totalQuizzes }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-500">محاولات اختبارات</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $totalQuizAttempts }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-500">دروس مكتملة</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $totalCompletions }}</p>
            </div>
        </div>

        {{-- Today's Activity --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-4">
                <p class="text-xs text-emerald-600 font-bold">🚀 اليوم</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $todayUsers }}</p>
                <p class="text-xs text-gray-500">مستخدمين جدد</p>
            </div>
            <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4">
                <p class="text-xs text-blue-600 font-bold">📚 اليوم</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $todayCompletions }}</p>
                <p class="text-xs text-gray-500">دروس مكتملة</p>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 p-4">
                <p class="text-xs text-amber-600 font-bold">📝 اليوم</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $todayQuizAttempts }}</p>
                <p class="text-xs text-gray-500">اختبارات</p>
            </div>
            <div class="bg-purple-50 rounded-2xl border border-purple-100 p-4">
                <p class="text-xs text-purple-600 font-bold">💰 اليوم</p>
                <p class="text-xl font-black text-gray-900 mt-1">{{ $todayPayments }}</p>
                <p class="text-xs text-gray-500">مدفوعات</p>
            </div>
        </div>

        {{-- Registration chart + Top courses --}}
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 text-sm mb-3">تسجيلات المستخدمين (آخر 14 يوم)</h3>
                <div class="flex items-end gap-1.5 h-24">
                    @foreach($chartLabels as $i => $label)
                        @php $h = $maxReg > 0 ? max(($chartData[$i] / $maxReg) * 96, 2) : 2; @endphp
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-[9px] font-bold {{ $chartData[$i] > 0 ? 'text-blue-600' : 'text-gray-300' }}">{{ $chartData[$i] }}</span>
                            <div class="w-full rounded-sm bg-blue-500 transition-all" style="height: {{ $h }}px;"></div>
                            <span class="text-[8px] text-gray-400">{{ Str::after($label, ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 text-sm mb-3">أكثر 5 كورسات في التسجيل</h3>
                @forelse($topCourses as $tc)
                    <div class="flex items-center gap-2 mb-2 last:mb-0">
                        <span class="text-xs text-gray-400 w-5">{{ $loop->iteration }}.</span>
                        <div class="flex-1">
                            <div class="flex justify-between text-xs mb-0.5">
                                <span class="text-gray-700 truncate">{{ $tc->title_ar ?? $tc->title }}</span>
                                <span class="font-bold text-gray-900">{{ $tc->access_grants_count }}</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ $topCourses->max('access_grants_count') > 0 ? ($tc->access_grants_count / $topCourses->max('access_grants_count')) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">لا توجد بيانات</p>
                @endforelse
            </div>
        </div>

        {{-- Latest Users Table --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">آخر المستخدمين المسجلين</h3>
                <a href="/admin/users" class="text-xs font-bold text-blue-600 hover:text-blue-800">← إدارة المستخدمين</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">الاسم</th>
                            <th class="text-right px-5 py-3">البريد</th>
                            <th class="text-center px-5 py-3">الدور</th>
                            <th class="text-center px-5 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($latestUsers as $u)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $u->name }}</td>
                                <td class="px-5 py-4 text-gray-500">{{ $u->email }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                        @if($u->hasRole('admin')) bg-red-100 text-red-700
                                        @elseif($u->hasRole('student')) bg-blue-100 text-blue-700
                                        @elseif($u->hasRole('teacher')) bg-emerald-100 text-emerald-700
                                        @elseif($u->hasRole('supervisor')) bg-purple-100 text-purple-700
                                        @elseif($u->hasRole('parent')) bg-amber-100 text-amber-700
                                        @else bg-gray-100 text-gray-600 @endif">
                                        {{ $u->roles->first()?->name ?? $u->role }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center text-gray-500 text-xs">{{ $u->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== SCHOOLS ==================== --}}
    <div x-show="tab === 'schools'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">المدارس</h1>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($schools as $school)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <p class="font-bold text-gray-900">{{ $school->name_ar ?? $school->name }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $school->users_count ?? 0 }} مستخدم</p>
                    <a href="/admin/schools/{{ $school->id }}/edit" class="inline-block mt-3 text-xs font-bold text-blue-600 hover:text-blue-800">إدارة ←</a>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد مدارس مسجلة</p>
                    <a href="/admin/schools/create" class="inline-block mt-3 text-sm font-bold text-blue-600 hover:text-blue-800">إضافة مدرسة جديدة ←</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== TEACHERS ==================== --}}
    <div x-show="tab === 'teachers'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">المعلمون</h1>
            <a href="/admin/users/create" class="text-sm font-bold text-blue-600 hover:text-blue-800">إضافة معلم ←</a>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">الاسم</th>
                            <th class="text-right px-5 py-3">البريد</th>
                            <th class="text-center px-5 py-3">الكورسات</th>
                            <th class="text-center px-5 py-3">الحالة</th>
                            <th class="text-center px-5 py-3">التسجيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($teachers as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $t->name }}</td>
                                <td class="px-5 py-4 text-gray-500">{{ $t->email }}</td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900">{{ $t->course_count ?? 0 }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $t->is_active ?? true ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $t->is_active ?? true ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500">{{ $t->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">لا يوجد معلمون</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== STUDENTS ==================== --}}
    <div x-show="tab === 'students'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">الطلاب</h1>
            <a href="/admin/users/create" class="text-sm font-bold text-blue-600 hover:text-blue-800">إضافة طالب ←</a>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">الاسم</th>
                            <th class="text-right px-5 py-3">البريد</th>
                            <th class="text-center px-5 py-3">اختبارات</th>
                            <th class="text-center px-5 py-3">دروس</th>
                            <th class="text-center px-5 py-3">التسجيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $s->name }}</td>
                                <td class="px-5 py-4 text-gray-500 text-xs">{{ $s->email }}</td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900">{{ $s->quiz_count ?? 0 }}</td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900">{{ $s->lesson_count ?? 0 }}</td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500">{{ $s->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">لا يوجد طلاب</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== REPORTS ==================== --}}
    <div x-show="tab === 'reports'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">التقارير والإحصائيات</h1>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p class="text-xs text-gray-500">نسبة النجاح في الاختبارات</p>
                <p class="text-3xl font-black {{ $passRate >= 70 ? 'text-emerald-600' : ($passRate >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $passRate }}%</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p class="text-xs text-gray-500">إجمالي الإيرادات</p>
                <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($totalRevenue) }} <span class="text-sm font-normal text-gray-400">ريال</span></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p class="text-xs text-gray-500">اشتراكات نشطة</p>
                <p class="text-3xl font-black text-blue-600 mt-1">{{ $activeEnrollments }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p class="text-xs text-gray-500">مدفوعات معلقة</p>
                <p class="text-3xl font-black {{ $pendingPayments > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $pendingPayments }}</p>
            </div>
        </div>

        {{-- Registration chart --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-6">
            <h3 class="font-bold text-gray-900 text-sm mb-3">تسجيلات المستخدمين (آخر 14 يوم)</h3>
            <div class="flex items-end gap-1.5 h-28">
                @foreach($chartLabels as $i => $label)
                    @php $h = $maxReg > 0 ? max(($chartData[$i] / $maxReg) * 112, 2) : 2; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[9px] font-bold {{ $chartData[$i] > 0 ? 'text-blue-600' : 'text-gray-300' }}">{{ $chartData[$i] }}</span>
                        <div class="w-full rounded-sm {{ $chartData[$i] > 0 ? 'bg-gradient-to-t from-blue-500 to-blue-400' : 'bg-gray-100' }}" style="height: {{ $h }}px;"></div>
                        <span class="text-[8px] text-gray-400">{{ Str::after($label, ' ') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top Courses Table --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">أفضل 10 كورسات حسب عدد المسجلين</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">#</th>
                            <th class="text-right px-5 py-3">الكورس</th>
                            <th class="text-center px-5 py-3">الطلاب المسجلين</th>
                            <th class="text-center px-5 py-3">الدروس</th>
                            <th class="text-center px-5 py-3">السعر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $allCourses = \App\Models\Course::withCount(['accessGrants', 'lessons' => fn($q) => $q->where('is_published', true)])
                                ->orderByDesc('access_grants_count')
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($allCourses as $ci => $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-bold text-gray-400">{{ $ci + 1 }}</td>
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $c->title_ar ?? $c->title }}</td>
                                <td class="px-5 py-4 text-center font-bold text-blue-600">{{ $c->access_grants_count }}</td>
                                <td class="px-5 py-4 text-center text-gray-600">{{ $c->lessons_count }}</td>
                                <td class="px-5 py-4 text-center font-bold {{ $c->price == 0 ? 'text-emerald-600' : 'text-gray-900' }}">{{ $c->price == 0 ? 'مجاني' : number_format($c->price, 0) . ' ريال' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">لا توجد كورسات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== PAYMENTS ==================== --}}
    <div x-show="tab === 'payments'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">المدفوعات</h1>
            <a href="/admin/payment-requests" class="text-sm font-bold text-blue-600 hover:text-blue-800">إدارة المدفوعات ←</a>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-5">
                <p class="text-2xl font-black text-emerald-600">{{ number_format($totalRevenue) }} <span class="text-sm font-normal">ريال</span></p>
                <p class="text-xs text-gray-500 mt-1">إجمالي الإيرادات</p>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5">
                <p class="text-2xl font-black text-amber-600">{{ $pendingPayments }}</p>
                <p class="text-xs text-gray-500 mt-1">معلقة</p>
            </div>
            <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
                <p class="text-2xl font-black text-blue-600">{{ $activeEnrollments }}</p>
                <p class="text-xs text-gray-500 mt-1">اشتراكات نشطة</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">آخر 10 معاملات</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">الطالب</th>
                            <th class="text-right px-5 py-3">الكورس</th>
                            <th class="text-center px-5 py-3">المبلغ</th>
                            <th class="text-center px-5 py-3">الحالة</th>
                            <th class="text-center px-5 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentPayments as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $p->user?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-gray-500">{{ $p->course?->title_ar ?? $p->course?->title ?? '—' }}</td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900">{{ number_format($p->amount, 0) }} ريال</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                        {{ $p->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $p->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ in_array($p->status, ['pending', 'pending_manual_review']) ? 'bg-amber-100 text-amber-700' : '' }}">
                                        {{ $p->status === 'approved' ? 'مقبول' : ($p->status === 'rejected' ? 'مرفوض' : 'قيد المراجعة') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500">{{ $p->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">لا توجد معاملات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endSection
