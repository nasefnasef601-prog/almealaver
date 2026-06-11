<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        <div class="no-print flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <label class="block min-w-56">
                <span class="mb-1 block text-sm font-bold text-gray-700">المدرسة</span>
                <select wire:model.live="schoolId" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name_ar ?: $school->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block min-w-56">
                <span class="mb-1 block text-sm font-bold text-gray-700">الفصل</span>
                <select wire:model.live="groupId" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">كل الفصول</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block w-40">
                <span class="mb-1 block text-sm font-bold text-gray-700">حد الضعف</span>
                <input type="number" wire:model.live.debounce.500ms="weakThreshold" min="1" max="100" class="w-full rounded-lg border-gray-300 text-sm">
            </label>
            <button type="button" onclick="window.print()" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-bold text-white">
                طباعة التقرير
            </button>
            <button type="button" wire:click="exportSkillHotspotsCsv" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-bold text-white">تصدير المهارات</button>
            <button type="button" wire:click="exportWeakStudentsCsv" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">
                تصدير CSV
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-gray-500">إجمالي الطلاب</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($studentsCount) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-rose-700">طلاب يحتاجون دعم</p>
                <p class="mt-2 text-3xl font-black text-rose-700">{{ number_format($weakStudentsCount) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-amber-700">مهارات ضعيفة</p>
                <p class="mt-2 text-3xl font-black text-amber-700">{{ number_format($weakSkillsCount) }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-blue-700">متوسط النتائج</p>
                <p class="mt-2 text-3xl font-black text-blue-700">{{ $averageScore }}%</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-700">طلاب بلا اختبار</p>
                <p class="mt-2 text-3xl font-black text-slate-700">{{ number_format($untestedStudentsCount) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-4">
                    <h2 class="font-black text-gray-900">الطلاب الأضعف حسب متوسط الاختبارات</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-right">الطالب</th>
                                <th class="px-4 py-3 text-center">المتوسط</th>
                                <th class="px-4 py-3 text-center">محاولات</th>
                                <th class="px-4 py-3 text-right">إجراء علاجي مقترح</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($weakStudents as $student)
                                <tr>
                                    <td class="px-4 py-3 font-bold">{{ $student->name }}<br><span class="text-xs font-normal text-gray-500">{{ $student->email }}</span></td>
                                    <td class="px-4 py-3 text-center font-black text-rose-600">{{ number_format($student->quiz_results_avg_score_percentage ?? 0, 1) }}%</td>
                                    <td class="px-4 py-3 text-center">{{ $student->quiz_results_count }}</td>
                                    <td class="px-4 py-3 text-gray-600">جلسة علاجية قصيرة + واجب مهارات + إعادة اختبار بعد 7 أيام</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">لا توجد بيانات ضعف كافية حاليا</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-4">
                    <h2 class="font-black text-gray-900">المهارات الأضعف</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-right">الطالب</th>
                                <th class="px-4 py-3 text-right">المهارة</th>
                                <th class="px-4 py-3 text-center">الإتقان</th>
                                <th class="px-4 py-3 text-center">الأسئلة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($weakSkills as $progress)
                                <tr>
                                    <td class="px-4 py-3 font-bold">{{ $progress->user?->name }}</td>
                                    <td class="px-4 py-3">{{ $progress->skill?->name_ar ?: $progress->skill?->name }}</td>
                                    <td class="px-4 py-3 text-center font-black text-amber-600">{{ number_format((float) $progress->mastery, 1) }}%</td>
                                    <td class="px-4 py-3 text-center">{{ $progress->total_questions }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">لا توجد مهارات ضعيفة مسجلة حاليا</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-4">
                    <h2 class="font-black text-gray-900">المهارات الأكثر تكرارا في الضعف</h2>
                    <p class="mt-1 text-xs text-gray-500">أولوية الشرح الجماعي وخطط الدعم داخل المدرسة.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-right">المهارة</th>
                                <th class="px-4 py-3 text-center">عدد الطلاب</th>
                                <th class="px-4 py-3 text-center">متوسط الإتقان</th>
                                <th class="px-4 py-3 text-center">أسئلة مقاسة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($skillHotspots as $hotspot)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-gray-900">{{ $hotspot['skill_name'] }}</span>
                                        @if($hotspot['subject_name'])
                                            <br><span class="text-xs text-gray-500">{{ $hotspot['subject_name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold">{{ $hotspot['students_count'] }}</td>
                                    <td class="px-4 py-3 text-center font-black text-amber-600">{{ $hotspot['average_mastery'] }}%</td>
                                    <td class="px-4 py-3 text-center">{{ $hotspot['total_questions'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">لا توجد بيانات مهارات كافية حاليا</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-4">
                    <h2 class="font-black text-gray-900">خطط علاجية قابلة للطباعة</h2>
                    <p class="mt-1 text-xs text-gray-500">ملخص سريع يساعد المشرف على متابعة الطلاب الأضعف.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($treatmentPlans as $plan)
                        <article class="p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-gray-900">{{ $plan['student_name'] }}</h3>
                                    <p class="text-xs text-gray-500">{{ $plan['email'] }}</p>
                                </div>
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-black text-rose-700">{{ number_format((float) $plan['average_score'], 1) }}%</span>
                            </div>
                            @if($plan['weak_skills']->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($plan['weak_skills'] as $skill)
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $skill['name'] }} - {{ number_format((float) $skill['mastery'], 1) }}%</span>
                                    @endforeach
                                </div>
                            @endif
                            <ul class="mt-3 list-disc space-y-1 pr-5 text-sm text-gray-700">
                                @foreach($plan['plan'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ul>
                            <button
                                type="button"
                                wire:click="createTreatmentPlanFor({{ $plan['student_id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="createTreatmentPlanFor({{ $plan['student_id'] }})"
                                class="mt-4 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-60"
                            >
                                إنشاء خطة داخل حساب الطالب
                            </button>
                        </article>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-400">لا توجد خطط علاجية حاليا</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 p-4">
                <h2 class="font-black text-gray-900">آخر النتائج المنخفضة</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-right">الطالب</th>
                            <th class="px-4 py-3 text-right">الاختبار</th>
                            <th class="px-4 py-3 text-center">النتيجة</th>
                            <th class="px-4 py-3 text-center">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentLowResults as $result)
                            <tr>
                                <td class="px-4 py-3 font-bold">{{ $result->user?->name }}</td>
                                <td class="px-4 py-3">{{ $result->quiz?->title_ar ?: $result->quiz?->title }}</td>
                                <td class="px-4 py-3 text-center font-black text-rose-600">{{ number_format((float) $result->score_percentage, 1) }}%</td>
                                <td class="px-4 py-3 text-center">{{ optional($result->completed_at)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">لا توجد نتائج منخفضة حديثة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <style>
        @media print {
            .fi-sidebar, .fi-topbar, .no-print { display: none !important; }
            .fi-main { margin: 0 !important; padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
</x-filament-panels::page>
