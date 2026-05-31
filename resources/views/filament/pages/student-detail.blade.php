<x-filament-panels::page>
    {{-- User Info Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8 flex items-center gap-6">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-2xl font-black text-white shrink-0">
                {{ mb_substr($student->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-black text-gray-900">{{ $student->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $student->email }}</p>
                <div class="flex gap-3 mt-2">
                    @if($student->phone)
                        <span class="text-xs text-gray-400">📱 {{ $student->phone }}</span>
                    @endif
                    @if($student->school)
                        <span class="text-xs text-gray-400">🏫 {{ $student->school->name_ar }}</span>
                    @endif
                    <span class="text-xs text-gray-400">📅 {{ $student->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
            <div class="text-center">
                <p class="text-3xl font-black {{ ($student->quiz_results_avg_score_percentage ?? 0) >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ number_format($student->quiz_results_avg_score_percentage ?? 0, 1) }}%
                </p>
                <p class="text-xs text-gray-500">متوسط النتيجة</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Stats Cards --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-academic-cap" class="w-6 h-6 text-blue-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $student->enrolled_courses_count }}</p>
                <p class="text-xs text-gray-500">كورسات مسجل فيها</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-check-circle" class="w-6 h-6 text-emerald-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $student->completed_lessons_count }}</p>
                <p class="text-xs text-gray-500">دروس مكتملة</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-6 h-6 text-amber-600"/>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $student->quiz_attempts_count }}</p>
                <p class="text-xs text-gray-500">اختبارات</p>
            </div>
        </div>
    </div>

    {{-- Enrolled Courses --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">الكورسات المسجل فيها</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <th class="text-right px-5 py-3">الكورس</th>
                        <th class="text-center px-5 py-3">تاريخ التسجيل</th>
                        <th class="text-center px-5 py-3">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $courses = \App\Models\Course::whereIn('id', \App\Models\AccessGrant::where('user_id', $student->id)->pluck('course_id'))->get(); @endphp
                    @forelse($courses as $course)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-bold text-gray-900">{{ $course->title_ar ?? $course->title }}</td>
                            <td class="px-5 py-4 text-center text-gray-500">{{ $course->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">مسجل</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">لا يوجد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Quiz Results --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">آخر نتائج الاختبارات</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <th class="text-right px-5 py-3">الاختبار</th>
                        <th class="text-center px-5 py-3">النتيجة</th>
                        <th class="text-center px-5 py-3">التاريخ</th>
                        <th class="text-center px-5 py-3">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $results = \App\Models\QuizResult::with('quiz')->where('user_id', $student->id)->latest()->take(10)->get(); @endphp
                    @forelse($results as $result)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-bold text-gray-900">{{ $result->quiz?->title_ar ?? $result->quiz?->title ?? '—' }}</td>
                            <td class="px-5 py-4 text-center font-bold {{ $result->score_percentage >= 70 ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($result->score_percentage, 1) }}%</td>
                            <td class="px-5 py-4 text-center text-gray-500">{{ $result->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $result->passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $result->passed ? 'ناجح' : 'راسب' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">لا توجد نتائج</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
