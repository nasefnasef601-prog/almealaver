<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <div class="max-w-md">
                <?php echo e($this->form); ?>

            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedQuizId && !empty($questionStats)): ?>
            
            <?php
                $avgAccuracy = collect($this->questionStats)->avg('accuracy');
                $easyCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] >= 80));
                $mediumCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] >= 50 && $q['accuracy'] < 80));
                $hardCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] < 50));
            ?>
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">متوسط الدقة</p>
                    <p class="text-2xl font-black <?php echo e($avgAccuracy >= 70 ? 'text-emerald-600' : ($avgAccuracy >= 50 ? 'text-amber-600' : 'text-red-600')); ?>"><?php echo e(number_format($avgAccuracy, 1)); ?>%</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة سهلة (80%+)</p>
                    <p class="text-2xl font-black text-emerald-600"><?php echo e($easyCount); ?></p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة متوسطة</p>
                    <p class="text-2xl font-black text-amber-600"><?php echo e($mediumCount); ?></p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة صعبة (&lt;50%)</p>
                    <p class="text-2xl font-black text-red-600"><?php echo e($hardCount); ?></p>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900"><?php echo e($this->quizTitle); ?> — <?php echo e(count($this->questionStats)); ?> سؤال، <?php echo e($this->questionStats[0]['total'] ?? 0); ?> محاولة</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                <th class="text-right px-5 py-3 w-12">#</th>
                                <th class="text-right px-5 py-3">السؤال</th>
                                <th class="text-center px-5 py-3">صحيح ✓</th>
                                <th class="text-center px-5 py-3">خطأ ✗</th>
                                <th class="text-center px-5 py-3">لم يجب —</th>
                                <th class="text-center px-5 py-3">الدقة</th>
                                <th class="text-center px-5 py-3">مستوى الصعوبة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->questionStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $qs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-bold text-gray-400"><?php echo e($i + 1); ?></td>
                                    <td class="px-5 py-4 font-medium text-gray-900 max-w-xs truncate"><?php echo e($qs['text']); ?></td>
                                    <td class="px-5 py-4 text-center font-bold text-emerald-600"><?php echo e($qs['correct']); ?></td>
                                    <td class="px-5 py-4 text-center font-bold text-red-500"><?php echo e($qs['incorrect']); ?></td>
                                    <td class="px-5 py-4 text-center text-gray-400"><?php echo e($qs['unanswered']); ?></td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                            <?php echo e($qs['accuracy'] >= 80 ? 'bg-emerald-100 text-emerald-700' : ($qs['accuracy'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')); ?>">
                                            <?php echo e($qs['accuracy']); ?>%
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full <?php echo e($qs['accuracy'] >= 80 ? 'bg-emerald-500' : ($qs['accuracy'] >= 50 ? 'bg-amber-500' : 'bg-red-500')); ?>"
                                                 style="width: <?php echo e($qs['accuracy']); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif($selectedQuizId): ?>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد بيانات كافية</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\filament\pages\question-analytics.blade.php ENDPATH**/ ?>