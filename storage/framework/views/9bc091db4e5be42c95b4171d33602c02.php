<?php $__env->startSection('title', 'النتائج'); ?>

<?php
    use App\Models\QuizResult;
    use App\Models\SkillProgress;
    use App\Models\Lesson;

    $user = Auth::user();
    $allResults = QuizResult::where('user_id', $user->id)
        ->with('quiz')
        ->latest()
        ->get();
    $totalQuizzes = $allResults->count();
    $avgScore = $allResults->avg('score_percentage');
    $passedCount = $allResults->where('passed', true)->count();
    $totalCorrect = $allResults->sum('correct_count');
    $totalQuestions = $allResults->sum('total_questions');
    $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

    // Aggregate skill progress
    $skillProgress = SkillProgress::where('user_id', $user->id)
        ->with('skill.section.subject')
        ->orderBy('mastery')
        ->get();
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto" x-data="{ selectedResult: null, showSkillDetails: null }">
    <h1 class="text-2xl font-black text-gray-900 mb-2">النتائج</h1>
    <p class="text-gray-500 text-sm mb-6">جميع نتائج اختباراتك وتحليل المهارات</p>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">إجمالي الاختبارات</p>
            <p class="text-3xl font-black text-gray-900 mt-1"><?php echo e($totalQuizzes); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">متوسط النتيجة</p>
            <p class="text-3xl font-black <?php echo e($avgScore >= 70 ? 'text-emerald-600' : ($avgScore >= 50 ? 'text-amber-600' : 'text-red-600')); ?> mt-1"><?php echo e($avgScore ? number_format($avgScore, 1) : '—'); ?>%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">نسبة النجاح</p>
            <p class="text-3xl font-black <?php echo e($passedCount/$totalQuizzes >= 0.7 ? 'text-emerald-600' : ($passedCount/$totalQuizzes >= 0.5 ? 'text-amber-600' : 'text-red-600')); ?> mt-1"><?php echo e($totalQuizzes > 0 ? round(($passedCount/$totalQuizzes)*100) : 0); ?>%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">الدقة</p>
            <p class="text-3xl font-black text-blue-600 mt-1"><?php echo e($accuracy ?: '—'); ?>%</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-lg font-bold text-gray-900">آخر النتائج</h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="font-bold text-gray-900"><?php echo e($result->quiz->title_ar ?? $result->quiz->title ?? 'اختبار'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($result->created_at->format('Y/m/d h:i A')); ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-black <?php echo e($result->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($result->score_percentage, 0)); ?>%</span>
                            <span class="text-xs px-2 py-1 rounded-full font-bold <?php echo e($result->passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>"><?php echo e($result->passed ? 'ناجح' : 'راسب'); ?></span>
                        </div>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-4">
                        <div class="h-full rounded-full <?php echo e($result->score_percentage >= 70 ? 'bg-emerald-500' : ($result->score_percentage >= 50 ? 'bg-amber-500' : 'bg-red-500')); ?>" style="width: <?php echo e($result->score_percentage); ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex gap-4 text-gray-500">
                            <span>✓ <?php echo e($result->correct_count); ?> صح</span>
                            <span>✗ <?php echo e($result->incorrect_count); ?> خطأ</span>
                            <span>— <?php echo e($result->unanswered_count); ?> لم تجب</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('student.quiz.result', ['quiz' => $result->quiz_id, 'attempt' => $result->attempt_id])); ?>" class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                مراجعة
                            </a>
                        </div>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($result->skill_breakdown)): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-sm font-bold text-gray-700 mb-3">تحليل المهارات</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $result->skill_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $m = $sb['mastery'] ?? 0;
                                        $bar = $m >= 80 ? 'bg-emerald-500' : ($m >= 60 ? 'bg-amber-500' : 'bg-red-500');
                                    ?>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="w-16 truncate shrink-0 text-gray-600"><?php echo e($sb['skill_name']); ?></span>
                                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full <?php echo e($bar); ?>" style="width: <?php echo e($m); ?>%"></div>
                                        </div>
                                        <span class="font-bold w-8 text-left <?php echo e($m >= 80 ? 'text-emerald-600' : ($m >= 60 ? 'text-amber-600' : 'text-red-600')); ?>"><?php echo e(number_format($m, 0)); ?>%</span>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-400 text-lg">لا توجد نتائج بعد. قم بحل بعض الاختبارات!</p>
                    <a href="<?php echo e(route('student.quiz.list')); ?>" class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700">اذهب للاختبارات</a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skillProgress->isNotEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">تقدم المهارات</h2>
                    <?php
                        $strong = $skillProgress->filter(fn($s) => $s->status === 'mastered')->count();
                        $good = $skillProgress->filter(fn($s) => $s->status === 'good')->count();
                        $avg = $skillProgress->filter(fn($s) => $s->status === 'average')->count();
                        $weak = $skillProgress->filter(fn($s) => $s->status === 'weak')->count();
                    ?>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700"><?php echo e($strong); ?> متقن</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700"><?php echo e($good); ?> جيد</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700"><?php echo e($avg); ?> متوسط</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700"><?php echo e($weak); ?> ضعيف</span>
                    </div>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $skillProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $barColor = match($sp->status) {
                                    'mastered' => 'bg-emerald-500',
                                    'good' => 'bg-blue-500',
                                    'average' => 'bg-amber-500',
                                    default => 'bg-red-500',
                                };
                                $textColor = match($sp->status) {
                                    'mastered' => 'text-emerald-700',
                                    'good' => 'text-blue-700',
                                    'average' => 'text-amber-700',
                                    default => 'text-red-700',
                                };
                            ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-900"><?php echo e($sp->skill->name_ar ?? 'مهارة'); ?></span>
                                    <span class="font-bold <?php echo e($textColor); ?>"><?php echo e(number_format($sp->mastery, 0)); ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?php echo e($barColor); ?>" style="width: <?php echo e($sp->mastery); ?>%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-0.5">
                                    <span><?php echo e($sp->skill?->section?->subject?->name_ar ?? ''); ?></span>
                                    <span><?php echo e($sp->total_attempts); ?> محاولة</span>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                
                <?php
                    $weakSkills = $skillProgress->filter(fn($s) => $s->status === 'weak' || $s->status === 'average')->take(5);
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($weakSkills->isNotEmpty()): ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">المهارات الأقل إتقاناً</h2>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $weakSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $lessons = Lesson::whereHas('course', fn($q) => $q->where('skill_id', $sp->skill_id))
                                        ->where('is_published', true)
                                        ->take(2)
                                        ->get();
                                ?>
                                <div class="p-4 bg-red-50 rounded-2xl">
                                    <p class="font-bold text-gray-900 text-sm"><?php echo e($sp->skill->name_ar ?? 'مهارة'); ?></p>
                                    <p class="text-xs text-gray-500 mb-2">الإتقان: <?php echo e(number_format($sp->mastery, 0)); ?>% — <?php echo e($sp->total_attempts); ?> محاولة</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lessons->isNotEmpty()): ?>
                                        <p class="text-xs font-bold text-gray-700 mb-1">دروس مقترحة:</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <a href="<?php echo e(route('student.lesson.show', ['course' => $lesson->course_id, 'lesson' => $lesson->id])); ?>" class="block text-xs text-blue-600 hover:text-blue-800 mb-1">
                                                ← <?php echo e($lesson->title_ar ?? $lesson->title); ?>

                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <a href="<?php echo e(route('student.quiz.list')); ?>" class="inline-block mt-2 text-xs font-bold text-red-600 hover:text-red-800">
                                        حل اختبارات لهذه المهارة ←
                                    </a>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
                    <p class="text-gray-400">لم يتم تحليل المهارات بعد.<br>قم بحل اختبار لبدء التحليل.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'results'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\results.blade.php ENDPATH**/ ?>