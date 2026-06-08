<?php $__env->startSection('title', 'نتيجة الاختبار'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @keyframes confettiFall {
        0% { transform: translateY(-10px) rotate(0deg); opacity: 0.9; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
</style>
<div class="max-w-4xl mx-auto" x-data="{
    showReview: false,
    showConfetti: <?php echo e($result->score_percentage >= 80 ? 'true' : 'false'); ?>,
    init() {
        if (this.showConfetti) {
            setTimeout(() => { this.showConfetti = false; }, 4000);
        }
    }
}">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result->score_percentage >= 80): ?>
    <div x-show="showConfetti" x-transition.duration.1000 class="fixed inset-0 pointer-events-none z-50 overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 40; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="absolute w-2.5 h-2.5 rounded-sm opacity-80"
                 style="
                     left: <?php echo e(rand(0, 100)); ?>%;
                     top: -10px;
                     background: <?php echo e(['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'][rand(0,5)]); ?>;
                     animation: confettiFall <?php echo e(rand(2, 4)); ?>s linear <?php echo e(rand(0, 20) / 10); ?>s infinite;
                     animation-delay: <?php echo e(rand(0, 20) / 10); ?>s;
                     transform: rotate(<?php echo e(rand(0, 360)); ?>deg);
                 ">
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-12 text-center mb-6 relative overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result->score_percentage == 100): ?>
        <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-400 text-white text-xs font-bold py-1.5 text-center tracking-wider">
            🏆 نتيجة كاملة! أداء ممتاز!
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl font-black text-white shadow-lg"
             style="background: <?php echo e($result->passed ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'); ?>">
            <?php echo e(number_format($result->score_percentage, 0)); ?>%
        </div>

        <h1 class="text-2xl font-black text-gray-900 mb-2"><?php echo e($quiz->title_ar ?? $quiz->title); ?></h1>
        <p class="text-lg font-bold mb-6 <?php echo e($result->passed ? 'text-emerald-600' : 'text-red-500'); ?>">
            <?php echo e($result->passed ? ($result->score_percentage == 100 ? '🌟 نتيجة مثالية! إتقان تام!' : 'تهانينا! لقد نجحت في الاختبار 🎉') : 'لم تنجح في هذه المحاولة، حاول مرة أخرى'); ?>

        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-2xl font-black text-blue-600"><?php echo e($result->correct_count); ?></p>
                <p class="text-xs text-gray-500">إجابات صحيحة</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-2xl font-black text-red-500"><?php echo e($result->incorrect_count); ?></p>
                <p class="text-xs text-gray-500">إجابات خاطئة</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-2xl font-black text-gray-600"><?php echo e($result->unanswered_count); ?></p>
                <p class="text-xs text-gray-500">لم تتم الإجابة</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-2xl font-black text-amber-600"><?php echo e(gmdate('i:s', $attempt->time_taken_seconds ?? 0)); ?></p>
                <p class="text-xs text-gray-500">الوقت المستغرق</p>
            </div>
        </div>

        <div class="flex justify-center gap-3 flex-wrap">
            <a href="<?php echo e(route('student.quiz.start', $quiz->id)); ?>" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                إعادة الاختبار
            </a>
            <button @click="showReview = !showReview" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span x-text="showReview ? 'إخفاء المراجعة' : 'مراجعة الإجابات'"></span>
            </button>
            <a href="<?php echo e(route('student.skills')); ?>" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                المهارات
            </a>
            <a href="<?php echo e(route('student.quiz.list')); ?>" class="px-6 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                العودة للاختبارات
            </a>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($result->skill_breakdown)): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-6">
        <h2 class="text-xl font-black text-gray-900 mb-6">تحليل المهارات</h2>

        <?php
            $strongCount = count(array_filter($result->skill_breakdown, fn($s) => ($s['status'] ?? 'weak') === 'strong'));
            $avgCount = count(array_filter($result->skill_breakdown, fn($s) => ($s['status'] ?? 'weak') === 'average'));
            $weakCount = count(array_filter($result->skill_breakdown, fn($s) => ($s['status'] ?? 'weak') === 'weak'));
        ?>

        
        <div class="flex gap-3 mb-6">
            <span class="px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm"><?php echo e($strongCount); ?> متقن</span>
            <span class="px-4 py-1.5 rounded-full bg-amber-100 text-amber-700 font-bold text-sm"><?php echo e($avgCount); ?> متوسط</span>
            <span class="px-4 py-1.5 rounded-full bg-red-100 text-red-700 font-bold text-sm"><?php echo e($weakCount); ?> يحتاج تحسين</span>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($result->skill_breakdown) > 1): ?>
        <div class="mb-8 bg-gray-50 rounded-2xl p-4">
            <svg viewBox="0 0 <?php echo e(max(count($result->skill_breakdown) * 80, 200)); ?> 160" class="w-full h-40">
                <?php $maxMastery = 100; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $result->skill_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $m = $skill['mastery'] ?? 0;
                        $x = $i * 80 + 40;
                        $h = max(($m / $maxMastery) * 120, 4);
                        $barColor = match($skill['status'] ?? 'weak') {
                            'strong' => '#10b981',
                            'average' => '#f59e0b',
                            default => '#ef4444',
                        };
                    ?>
                    <rect x="<?php echo e($x - 15); ?>" y="<?php echo e(140 - $h); ?>" width="30" height="<?php echo e($h); ?>" rx="4" fill="<?php echo e($barColor); ?>" opacity="0.85"/>
                    <text x="<?php echo e($x); ?>" y="<?php echo e(140 - $h - 6); ?>" text-anchor="middle" font-size="11" font-weight="bold" fill="<?php echo e($barColor); ?>"><?php echo e(number_format($m, 0)); ?>%</text>
                    <text x="<?php echo e($x); ?>" y="155" text-anchor="middle" font-size="9" fill="#9ca3af">
                                                        <?php echo e(Str::limit($skill['skill_name'], 10)); ?>

                                                    </text>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <line x1="0" y1="140" x2="100%" y2="140" stroke="#e5e7eb" stroke-width="1"/>
            </svg>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="space-y-5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $result->skill_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $mastery = $skill['mastery'] ?? 0;
                    $status = $skill['status'] ?? 'weak';
                    $barColor = match($status) {
                        'strong' => 'bg-emerald-500',
                        'average' => 'bg-amber-500',
                        default => 'bg-red-500',
                    };
                    $textColor = match($status) {
                        'strong' => 'text-emerald-700',
                        'average' => 'text-amber-700',
                        default => 'text-red-700',
                    };
                    $statusLabel = match($status) {
                        'strong' => 'متقن',
                        'average' => 'متوسط',
                        default => 'ضعيف',
                    };
                ?>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-bold text-gray-900"><?php echo e($skill['skill_name']); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skill['subject_name']): ?>
                                <p class="text-xs text-gray-500"><?php echo e($skill['subject_name']); ?> <?php echo e($skill['section_name'] ? '- ' . $skill['section_name'] : ''); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="text-left">
                            <span class="text-lg font-black <?php echo e($textColor); ?>"><?php echo e(number_format($mastery, 0)); ?>%</span>
                            <span class="text-xs text-gray-500 mr-1">(<?php echo e($skill['correct']); ?>/<?php echo e($skill['total']); ?>)</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all duration-700 <?php echo e($barColor); ?>" style="width: <?php echo e($mastery); ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-400"><?php echo e($skill['section_name']); ?></span>
                        <span class="text-xs font-bold <?php echo e($textColor); ?>"><?php echo e($statusLabel); ?></span>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <?php
            $weakSkills = array_filter($result->skill_breakdown, fn($s) => ($s['status'] ?? 'weak') === 'weak');
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($weakSkills) > 0): ?>
            <div class="mt-6 p-5 bg-amber-50 rounded-2xl border border-amber-200">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-amber-800">توصيات للتحسين</span>
                </div>
                <p class="text-sm text-amber-700">
                    تحتاج إلى تحسين في المهارات التالية:
                    <?php
                        $weakNames = array_map(fn($s) => $s['skill_name'], $weakSkills);
                    ?>
                    <?php echo e(implode('، ', $weakNames)); ?>.
                    ننصح بمراجعة الدروس المتعلقة بهذه المهارات والممارسة أكثر.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-show="showReview" x-transition class="space-y-4">
        <h2 class="text-xl font-black text-gray-900 mb-4">مراجعة الإجابات</h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $reviewData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-start gap-3 mb-4">
                    <span class="w-8 h-8 rounded-xl <?php echo e($r['is_correct'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?> flex items-center justify-center font-bold text-sm shrink-0"><?php echo e($idx + 1); ?></span>
                    <p class="font-bold text-gray-900"><?php echo e($r['text']); ?></p>
                </div>

                <div class="space-y-2 mr-11">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r['options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $isSelected = (string) $r['selected'] === (string) $oi;
                            $isCorrect = (string) $r['correct'] === (string) $oi;
                        ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl text-sm
                            <?php echo e($isSelected && $isCorrect ? 'bg-emerald-50 border border-emerald-200' : ''); ?>

                            <?php echo e($isSelected && !$isCorrect ? 'bg-red-50 border border-red-200' : ''); ?>

                            <?php echo e(!$isSelected && $isCorrect ? 'bg-emerald-50/50 border border-emerald-200' : ''); ?>

                            <?php echo e(!$isSelected && !$isCorrect ? 'bg-gray-50 border border-gray-100' : ''); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelected && $isCorrect): ?>
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php elseif($isSelected && !$isCorrect): ?>
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <?php elseif(!$isSelected && $isCorrect): ?>
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php else: ?>
                                <div class="w-5 h-5 shrink-0"></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="<?php echo e(!$isSelected && $isCorrect ? 'text-emerald-700 font-medium' : ($isSelected && !$isCorrect ? 'text-red-700' : 'text-gray-600')); ?>"><?php echo $opt; ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r['explanation']): ?>
                    <div class="mt-4 mr-11 p-4 bg-blue-50 rounded-2xl text-sm text-blue-800">
                        <p class="font-bold mb-1">الشرح:</p>
                        <p><?php echo e($r['explanation']); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'quizzes'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\quiz-result.blade.php ENDPATH**/ ?>