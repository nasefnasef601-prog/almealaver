<?php
    $tab = request('tab', 'overview');
?>


<?php $__env->startSection('title', 'لوحة ولي الأمر'); ?>

<?php
    use App\Models\User;
    use App\Models\QuizResult;
    use App\Models\QuizAttempt;
    use App\Models\LessonCompletion;
    use App\Models\PaymentRequest;
    use App\Models\AccessGrant;
    use Carbon\Carbon;

    $user = Auth::user();
    $studentIds = \DB::table('parent_student')->where('parent_id', $user->id)->pluck('student_id');
    $students = User::whereIn('id', $studentIds)->get();

    // Aggregate across all children
    $totalQuizResults = 0;
    $totalLessonsCompleted = 0;
    $totalAvgScore = 0;
    $studentData = [];
    $scoreOverTime = collect();
    foreach ($students as $s) {
        $results = QuizResult::where('user_id', $s->id);
        $rCount = (clone $results)->count();
        $rAvg = (clone $results)->avg('score_percentage');
        $lCount = LessonCompletion::where('user_id', $s->id)->count();
        $totalQuizResults += $rCount;
        $totalLessonsCompleted += $lCount;
        $studentData[$s->id] = ['results_count' => $rCount, 'avg_score' => $rAvg, 'lessons' => $lCount];

        // Gather last 7 days scores
        $weekResults = (clone $results)->where('created_at', '>=', now()->subDays(7))->get()
            ->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
            ->map(fn($day) => round($day->avg('score_percentage'), 1));
        foreach ($weekResults as $date => $sc) {
            if (!$scoreOverTime->has($date)) $scoreOverTime[$date] = [];
            $scoreOverTime[$date][] = $sc;
        }
    }
    $studentCount = $students->count();
    $overallAvg = $studentCount > 0 ? collect($studentData)->avg('avg_score') : 0;

    // Daily avg for chart
    $chartDays = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $scores = $scoreOverTime[$date] ?? [];
        $chartDays[] = [
            'label' => now()->subDays($i)->format('D'),
            'score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null,
        ];
    }

    // Payments made by parent
    $myPayments = PaymentRequest::where('user_id', $user->id)->latest()->take(10)->get();

    // Children enrolled courses
    $childrenCourses = [];
    foreach ($students as $s) {
        $enrolledIds = AccessGrant::where('user_id', $s->id)->where('status', 'active')->pluck('course_id');
        $childrenCourses[$s->id] = \App\Models\Course::whereIn('id', $enrolledIds)->get();
    }
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl" x-data="{ tab: '<?php echo e($tab); ?>' }">

    
    <div class="flex gap-2 overflow-x-auto pb-2 mb-6 scrollbar-thin">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الرئيسية</button>
        <button @click="tab = 'children'" :class="tab === 'children' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الأبناء</button>
        <button @click="tab = 'results'" :class="tab === 'results' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">النتائج</button>
        <button @click="tab = 'reports'" :class="tab === 'reports' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التقارير</button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدفوعات</button>
    </div>

    
    <div x-show="tab === 'overview'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">لوحة ولي الأمر</h1>
        <p class="text-gray-500 text-sm mb-6">تابع تقدم أبنائك في التعلم</p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->isEmpty()): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                <p class="text-gray-500 text-lg mb-2">لا يوجد أبناء مرتبطين</p>
                <p class="text-gray-400 text-sm">تواصل مع الإدارة لربط أبنائك بحسابك</p>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl p-6 shadow-lg">
                    <p class="text-blue-100 text-sm font-medium">الأبناء</p>
                    <p class="text-3xl font-black mt-1"><?php echo e($studentCount); ?></p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl p-6 shadow-lg">
                    <p class="text-emerald-100 text-sm font-medium">اختبارات</p>
                    <p class="text-3xl font-black mt-1"><?php echo e($totalQuizResults); ?></p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-6 shadow-lg">
                    <p class="text-amber-100 text-sm font-medium">المتوسط العام</p>
                    <p class="text-3xl font-black mt-1"><?php echo e($overallAvg ? number_format($overallAvg, 1) : '—'); ?>%</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-2xl p-6 shadow-lg">
                    <p class="text-purple-100 text-sm font-medium">دروس مكتملة</p>
                    <p class="text-3xl font-black mt-1"><?php echo e($totalLessonsCompleted); ?></p>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(collect($chartDays)->filter(fn($d) => $d['score'] !== null)->isNotEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-6">
                <h3 class="font-bold text-gray-900 text-sm mb-3">متوسط أداء الأبناء (آخر 7 أيام)</h3>
                <div class="flex items-end gap-2 h-20">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $chartDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $h = $cd['score'] !== null ? max(($cd['score'] / 100) * 72, 4) : 2;
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full rounded-md transition-all <?php echo e($cd['score'] !== null ? ($cd['score'] >= 70 ? 'bg-emerald-500' : ($cd['score'] >= 50 ? 'bg-amber-500' : 'bg-red-500')) : 'bg-gray-100'); ?>"
                                 style="height: <?php echo e($h); ?>px; min-height: 2px;"></div>
                            <span class="text-[10px] text-gray-400"><?php echo e($cd['label']); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $sd = $studentData[$student->id] ?? []; ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                                <?php echo e(mb_substr($student->name, 0, 1)); ?>

                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900"><?php echo e($student->name); ?></h2>
                                <p class="text-sm text-gray-400"><?php echo e($student->email); ?></p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black <?php echo e(($sd['avg_score'] ?? 0) >= 70 ? 'text-emerald-600' : 'text-amber-600'); ?>"><?php echo e(isset($sd['avg_score']) ? number_format($sd['avg_score'], 0) : '—'); ?>%</p>
                                <p class="text-xs text-gray-500">المتوسط</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-blue-600"><?php echo e($sd['results_count'] ?? 0); ?></p>
                                <p class="text-xs text-gray-500">اختبارات</p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-purple-600"><?php echo e(\App\Models\QuizResult::where('user_id', $student->id)->where('passed', true)->count()); ?></p>
                                <p class="text-xs text-gray-500">ناجح</p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-emerald-600"><?php echo e($sd['lessons'] ?? 0); ?></p>
                                <p class="text-xs text-gray-500">دروس مكتملة</p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-amber-600"><?php echo e($childrenCourses[$student->id]?->count() ?? 0); ?></p>
                                <p class="text-xs text-gray-500">كورسات</p>
                            </div>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($childrenCourses[$student->id] ?? collect())->isNotEmpty()): ?>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-bold text-gray-600 mb-2">الكورسات المسجل فيها:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $childrenCourses[$student->id]->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php
                                            $allLessons = $cc->modules->flatMap(fn($m) => $m->lessons);
                                            $doneL = LessonCompletion::where('user_id', $student->id)->whereIn('lesson_id', $allLessons->pluck('id'))->count();
                                            $pct = $allLessons->count() > 0 ? round(($doneL / $allLessons->count()) * 100) : 0;
                                        ?>
                                        <span class="inline-flex items-center gap-1 bg-gray-50 px-3 py-1.5 rounded-full text-xs text-gray-600">
                                            <?php echo e($cc->title_ar ?? $cc->title); ?>

                                            <span class="font-bold <?php echo e($pct >= 80 ? 'text-emerald-600' : 'text-blue-600'); ?>">(<?php echo e($pct); ?>%)</span>
                                        </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php
                            $weakSkills = \App\Models\SkillProgress::where('user_id', $student->id)
                                ->with('skill.section.subject')
                                ->orderBy('mastery')
                                ->take(2)
                                ->get();
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($weakSkills->isNotEmpty()): ?>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-bold text-red-600 mb-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    مهارات تحتاج إلى تطوير عاجل:
                                </p>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $weakSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ws): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="bg-red-50/50 border border-red-100 rounded-2xl p-3">
                                            <div class="flex justify-between items-start gap-2 mb-2">
                                                <span class="font-bold text-gray-800 text-xs"><?php echo e($ws->skill->name_ar ?? $ws->skill->name); ?></span>
                                                <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full"><?php echo e($ws->mastery); ?>% إتقان</span>
                                            </div>
                                            <div class="text-[11px] text-gray-500 space-y-1">
                                                <p><strong class="text-gray-700">اليوم الأول:</strong> مشاهدة شرح الدرس وتأسيس المفاهيم.</p>
                                                <p><strong class="text-gray-700">اليوم الثاني:</strong> حل 15 سؤال تدريبي بتركيز.</p>
                                                <p><strong class="text-gray-700">اليوم الثالث:</strong> إعادة اختبار المهارة لتحقيق 80%+.</p>
                                            </div>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php
                            $msgText = "مرحباً، أود مشاركة تقرير ابننا {$student->name} في منصة المئة: أكمل {$sd['lessons']} درس، ومتوسط أدائه {$sd['results_count']} اختبار هو " . number_format($sd['avg_score'] ?? 0, 0) . "%." . ($weakSkills->isNotEmpty() ? " ونعمل حالياً على تقوية مهارة (" . ($weakSkills->first()->skill->name_ar ?? $weakSkills->first()->skill->name) . ")." : "");
                        ?>
                        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-400 font-bold">رسالة المتابعة المخصصة لولي الأمر:</p>
                                <p class="text-xs text-gray-600 truncate mt-1">"<?php echo e($msgText); ?>"</p>
                            </div>
                            <button onclick="navigator.clipboard.writeText('<?php echo e(addslashes($msgText)); ?>'); alert('تم نسخ رسالة المتابعة بنجاح!');" 
                                    class="shrink-0 inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                نسخ الرسالة
                            </button>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'children'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">أبنائي</h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->isEmpty()): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-500">لا يوجد أبناء مرتبطين</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $courseCount = $childrenCourses[$student->id]?->count() ?? 0;
                        $enrolledLessonIds = \App\Models\Lesson::whereIn('course_id', AccessGrant::where('user_id', $student->id)->pluck('course_id'))->pluck('id');
                        $completedCount = LessonCompletion::where('user_id', $student->id)->whereIn('lesson_id', $enrolledLessonIds)->count();
                        $relation = \DB::table('parent_student')->where('parent_id', $user->id)->where('student_id', $student->id)->value('relationship');
                    ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-lg">
                                <?php echo e(mb_substr($student->name, 0, 1)); ?>

                            </div>
                            <div>
                                <p class="font-bold text-gray-900"><?php echo e($student->name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($student->email); ?> <?php echo e($relation ? '· ' . $relation : ''); ?></p>
                            </div>
                        </div>
                        <div class="flex gap-2 text-xs text-gray-500">
                            <span class="bg-blue-50 px-3 py-1.5 rounded-full font-bold text-blue-700"><?php echo e($courseCount); ?> كورس</span>
                            <span class="bg-emerald-50 px-3 py-1.5 rounded-full font-bold text-emerald-700"><?php echo e($completedCount); ?> درس مكتمل</span>
                            <span class="bg-amber-50 px-3 py-1.5 rounded-full font-bold text-amber-700"><?php echo e($sd['results_count'] ?? 0); ?> اختبار</span>
                        </div>
                        <?php
                            $lastResult = QuizResult::where('user_id', $student->id)->latest()->first();
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastResult): ?>
                            <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                                آخر اختبار: <span class="font-bold <?php echo e($lastResult->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($lastResult->score_percentage, 0)); ?>%</span>
                                <span class="mx-1">·</span>
                                <span><?php echo e($lastResult->created_at->diffForHumans()); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'results'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">النتائج</h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->isEmpty()): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-500">لا يوجد أبناء مرتبطين</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $sr = QuizResult::where('user_id', $student->id)->with('quiz')->latest()->take(10)->get(); ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold"><?php echo e(mb_substr($student->name, 0, 1)); ?></div>
                            <h2 class="font-bold text-gray-900"><?php echo e($student->name); ?></h2>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sr->isNotEmpty()): ?>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                                        <span class="text-sm text-gray-600 truncate ml-2"><?php echo e($r->quiz->title_ar ?? $r->quiz->title ?? 'اختبار'); ?></span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold <?php echo e($r->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($r->score_percentage, 0)); ?>%</span>
                                            <span class="text-xs text-gray-400"><?php echo e($r->created_at->format('Y/m/d')); ?></span>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-400 text-sm">لم يبدأ الاختبارات بعد.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'reports'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">التقارير</h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->isEmpty()): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-500">لا يوجد أبناء مرتبطين</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $sd = $studentData[$student->id] ?? [];
                        $allResults = QuizResult::where('user_id', $student->id)->get();
                        $passedCount = $allResults->where('passed', true)->count();
                        $totalQ = $allResults->sum('total_questions');
                        $correctQ = $allResults->sum('correct_count');
                        $accuracy = $totalQ > 0 ? round(($correctQ / $totalQ) * 100) : 0;
                    ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold"><?php echo e(mb_substr($student->name, 0, 1)); ?></div>
                            <p class="font-bold text-gray-900"><?php echo e($student->name); ?></p>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">عدد الاختبارات</span>
                                <span class="font-bold text-gray-900"><?php echo e($allResults->count()); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">المتوسط العام</span>
                                <span class="font-bold <?php echo e($allResults->avg('score_percentage') >= 70 ? 'text-emerald-600' : 'text-amber-600'); ?>"><?php echo e($allResults->avg('score_percentage') ? number_format($allResults->avg('score_percentage'), 0).'%' : '—'); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الاختبارات الناجحة</span>
                                <span class="font-bold text-emerald-600"><?php echo e($passedCount); ?> / <?php echo e($allResults->count() ?: '—'); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الدقة الإجمالية</span>
                                <span class="font-bold text-blue-600"><?php echo e($accuracy); ?>%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الدروس المكتملة</span>
                                <span class="font-bold text-purple-600"><?php echo e($sd['lessons'] ?? 0); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الكورسات المسجل فيها</span>
                                <span class="font-bold text-amber-600"><?php echo e($childrenCourses[$student->id]?->count() ?? 0); ?></span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <a href="<?php echo e(route('student.results')); ?>?user_id=<?php echo e($student->id); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800">عرض التفاصيل ←</a>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'payments'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">المدفوعات</h1>
        <p class="text-gray-500 text-sm mb-6">سجل مشترياتك واشتراكاتك</p>

        <?php
            $subscriptions = \App\Models\AccessGrant::where('user_id', $user->id)
                ->with('course')
                ->where('status', 'active')
                ->latest()
                ->get();
        ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscriptions->isNotEmpty()): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl p-5 shadow-lg">
                        <p class="text-emerald-100 text-xs font-medium mb-1">اشتراك نشط</p>
                        <p class="font-bold text-lg"><?php echo e($grant->course->title_ar ?? $grant->course->title ?? 'كورس'); ?></p>
                        <p class="text-emerald-200 text-xs mt-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grant->expires_at): ?>
                                ينتهي في <?php echo e($grant->expires_at->format('Y/m/d')); ?>

                            <?php else: ?>
                                غير محدد المدة
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">سجل المدفوعات</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                            <th class="text-right px-5 py-3">البيان</th>
                            <th class="text-center px-5 py-3">المبلغ</th>
                            <th class="text-center px-5 py-3">الحالة</th>
                            <th class="text-center px-5 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900"><?php echo e($p->item_name ?? 'اشتراك'); ?></td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900"><?php echo e(number_format($p->amount, 0)); ?> ريال</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                        <?php if($p->status === 'approved'): ?> bg-emerald-100 text-emerald-700
                                        <?php elseif($p->status === 'rejected'): ?> bg-red-100 text-red-700
                                        <?php elseif(in_array($p->status, ['pending', 'pending_manual_review'])): ?> bg-amber-100 text-amber-700
                                        <?php else: ?> bg-gray-100 text-gray-600 <?php endif; ?>">
                                        <?php echo e($p->status === 'approved' ? 'مقبول' : ($p->status === 'rejected' ? 'مرفوض' : 'قيد المراجعة')); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center text-gray-500"><?php echo e($p->created_at->format('Y-m-d')); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400">لا توجد مدفوعات بعد</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscriptions->isEmpty() && $myPayments->isEmpty()): ?>
            <div class="mt-4">
                <a href="<?php echo e(route('pricing')); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">
                    اشتراك في باقة <span>←</span>
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => $tab], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views/parent/dashboard.blade.php ENDPATH**/ ?>