<x-filament-panels::page>
    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-academic-cap" class="w-6 h-6 text-blue-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalStudents) }}</p>
                <p class="text-xs text-gray-500">إجمالي الطلاب</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-book-open" class="w-6 h-6 text-emerald-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalCourses) }}</p>
                <p class="text-xs text-gray-500">الكورسات</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-6 h-6 text-amber-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalQuizAttempts) }}</p>
                <p class="text-xs text-gray-500">محاولات الاختبارات</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-currency-dollar" class="w-6 h-6 text-purple-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalRevenue) }}</p>
                <p class="text-xs text-gray-500">الإيرادات (ريال)</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-check-circle" class="w-6 h-6 text-green-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalCompletions) }}</p>
                <p class="text-xs text-gray-500">دروس مكتملة</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-chart-bar" class="w-6 h-6 text-teal-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $avgQuizScore }}%</p>
                <p class="text-xs text-gray-500">متوسط نتيجة الاختبارات</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-light-bulb" class="w-6 h-6 text-indigo-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalSkillsMastered) }}</p>
                <p class="text-xs text-gray-500">مهارات متقنة (80%+)</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-fire" class="w-6 h-6 text-rose-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ number_format($studentsActiveToday) }}</p>
                <p class="text-xs text-gray-500">طلاب نشطون اليوم</p>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1">
            @livewire(\App\Filament\Widgets\CourseEnrollmentsChart::class)
        </div>
        <div class="lg:col-span-2">
            @livewire(\App\Filament\Widgets\QuizCompletionChart::class)
        </div>
    </div>

    {{-- Top Students Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">أفضل 10 طلاب حسب متوسط نتائج الاختبارات</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <th class="text-right px-5 py-3">#</th>
                        <th class="text-right px-5 py-3">الطالب</th>
                        <th class="text-center px-5 py-3">البريد</th>
                        <th class="text-center px-5 py-3">المدرسة</th>
                        <th class="text-center px-5 py-3">متوسط النتيجة</th>
                        <th class="text-center px-5 py-3">اختبارات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $topStudents = \App\Models\User::where('role', 'student')
                            ->withCount('quizResults')
                            ->withAvg('quizResults', 'score_percentage')
                            ->having('quiz_results_count', '>=', 3)
                            ->orderByDesc('quiz_results_avg_score_percentage')
                            ->limit(10)
                            ->get();
                    @endphp
                    @forelse($topStudents as $i => $student)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-4 font-bold text-gray-900">{{ $student->name }}</td>
                            <td class="px-5 py-4 text-gray-500 text-center">{{ $student->email }}</td>
                            <td class="px-5 py-4 text-gray-500 text-center">{{ $student->school?->name_ar }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                    {{ ($student->quiz_results_avg_score_percentage ?? 0) >= 80 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ number_format($student->quiz_results_avg_score_percentage ?? 0, 1) }}%
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-gray-600">{{ $student->quiz_results_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">لا توجد بيانات كافية</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
