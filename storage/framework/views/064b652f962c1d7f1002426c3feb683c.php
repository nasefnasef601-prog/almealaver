<?php $__env->startSection('title', 'مركز المهارات'); ?>

<?php
    use App\Models\SkillProgress;
    $user = Auth::user();
    $progress = SkillProgress::where('user_id', $user->id)
        ->with('skill.section.subject')
        ->orderByRaw("CASE status WHEN 'weak' THEN 1 WHEN 'average' THEN 2 WHEN 'good' THEN 3 WHEN 'mastered' THEN 4 ELSE 5 END")
        ->orderBy('mastery')
        ->get();

    $untestedSkills = \App\Models\Skill::where('is_active', true)
        ->whereDoesntHave('progress', fn($q) => $q->where('user_id', $user->id))
        ->with('section.subject')
        ->take(10)
        ->get();

    $strong = $progress->filter(fn($s) => $s->status === 'mastered')->count();
    $good = $progress->filter(fn($s) => $s->status === 'good')->count();
    $avg = $progress->filter(fn($s) => $s->status === 'average')->count();
    $weak = $progress->filter(fn($s) => $s->status === 'weak')->count();
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-black text-gray-900 mb-2">مركز المهارات</h1>
    <p class="text-gray-500 text-sm mb-6">تتبع إتقانك للمهارات واعرف نقاط القوة والضعف</p>

    
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-emerald-50 rounded-2xl p-5 text-center border border-emerald-100">
            <p class="text-3xl font-black text-emerald-600"><?php echo e($strong); ?></p>
            <p class="text-xs text-emerald-700 font-medium">متقن</p>
        </div>
        <div class="bg-blue-50 rounded-2xl p-5 text-center border border-blue-100">
            <p class="text-3xl font-black text-blue-600"><?php echo e($good); ?></p>
            <p class="text-xs text-blue-700 font-medium">جيد</p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-5 text-center border border-amber-100">
            <p class="text-3xl font-black text-amber-600"><?php echo e($avg); ?></p>
            <p class="text-xs text-amber-700 font-medium">متوسط</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-5 text-center border border-red-100">
            <p class="text-3xl font-black text-red-600"><?php echo e($weak); ?></p>
            <p class="text-xs text-red-700 font-medium">ضعيف</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">المهارات المتابعة</h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progress->isNotEmpty()): ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $progress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                            $badgeColor = match($sp->status) {
                                'mastered' => 'bg-emerald-100 text-emerald-700',
                                'good' => 'bg-blue-100 text-blue-700',
                                'average' => 'bg-amber-100 text-amber-700',
                                default => 'bg-red-100 text-red-700',
                            };
                        ?>
                        <a href="<?php echo e(route('student.skill.detail', $sp->skill_id)); ?>" class="block p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-gray-50 hover:border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-bold text-gray-900"><?php echo e($sp->skill->name_ar ?? 'مهارة'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($sp->skill?->section?->subject?->name_ar ?? ''); ?></p>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full font-bold <?php echo e($badgeColor); ?>"><?php echo e($sp->status === 'mastered' ? 'متقن' : ($sp->status === 'good' ? 'جيد' : ($sp->status === 'average' ? 'متوسط' : 'ضعيف'))); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?php echo e($barColor); ?>" style="width: <?php echo e($sp->mastery); ?>%"></div>
                                </div>
                                <span class="text-sm font-bold <?php echo e($textColor); ?>"><?php echo e(number_format($sp->mastery, 0)); ?>%</span>
                            </div>
                            <div class="flex gap-3 mt-2 text-xs text-gray-400">
                                <span><?php echo e($sp->total_attempts); ?> محاولة</span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sp->last_attempt_at): ?>
                                    <span>آخر محاولة: <?php echo e($sp->last_attempt_at->diffForHumans()); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400 text-center py-12">لم يتم تسجيل أي تقدم في المهارات بعد.<br>قم بحل اختبار لبدء التتبع!</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($weak > 0): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">تحتاج إلى تحسين</h2>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $progress->filter(fn($s) => $s->status === 'weak')->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e(route('student.skill.detail', $sp->skill_id)); ?>" class="flex items-center justify-between p-3 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">
                            <div>
                                <p class="font-bold text-sm text-gray-900"><?php echo e($sp->skill->name_ar ?? 'مهارة'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($sp->skill?->section?->subject?->name_ar ?? ''); ?></p>
                            </div>
                            <span class="text-sm font-bold text-red-600"><?php echo e(number_format($sp->mastery, 0)); ?>%</span>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($untestedSkills->isNotEmpty()): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">مهارات لم تختبر بعد</h2>
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $untestedSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div>
                                <p class="font-bold text-sm text-gray-900"><?php echo e($skill->name_ar ?? $skill->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($skill->section?->subject?->name_ar ?? ''); ?></p>
                            </div>
                            <a href="<?php echo e(route('student.quiz.list')); ?>" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">اختبر</a>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'skills'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\skills.blade.php ENDPATH**/ ?>