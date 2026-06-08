<?php $__env->startSection('title', 'لوحة المتصدرين'); ?>

<?php $__env->startSection('content'); ?>
<?php
    use App\Models\QuizResult;
    use App\Models\User;

    $userId = Auth::id();
    $minResults = 3;

    $leaderboard = QuizResult::selectRaw('user_id, AVG(score_percentage) as avg_score, COUNT(*) as total_tests, SUM(passed) as passed_tests')
        ->groupBy('user_id')
        ->havingRaw('COUNT(*) >= ?', [$minResults])
        ->orderByDesc('avg_score')
        ->limit(50)
        ->get();

    $userIds = $leaderboard->pluck('user_id');
    $users = User::whereIn('id', $userIds)->get()->keyBy('id');

    $userRank = $leaderboard->search(fn($r) => $r->user_id === $userId);
    $userTotal = QuizResult::where('user_id', $userId)->count();
    $userAvg = QuizResult::where('user_id', $userId)->avg('score_percentage');
?>

<div class="max-w-5xl">
    <h1 class="text-2xl font-black text-gray-900 mb-2">لوحة المتصدرين</h1>
    <p class="text-gray-500 text-sm mb-8">تصنيف الطلاب حسب متوسط نتائج الاختبارات (بحد أدنى <?php echo e($minResults); ?> اختبارات)</p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userTotal > 0 && $userTotal < $minResults): ?>
        <div class="mb-6 p-4 bg-amber-50 text-amber-700 rounded-2xl border border-amber-200 text-sm font-medium">
            أنت بحاجة إلى <?php echo e($minResults - $userTotal); ?> اختبارات إضافية للظهور في التصنيف.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-center p-4 font-bold text-gray-600 w-16">#</th>
                        <th class="text-right p-4 font-bold text-gray-600">الطالب</th>
                        <th class="text-center p-4 font-bold text-gray-600">المتوسط</th>
                        <th class="text-center p-4 font-bold text-gray-600">الاختبارات</th>
                        <th class="text-center p-4 font-bold text-gray-600">الناجح</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leaderboard->isEmpty()): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-gray-500 mb-1">لا يوجد تصنيف بعد</p>
                                <p class="text-gray-400 text-sm">باشر بحل الاختبارات للظهور في التصنيف</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $user = $users->get($entry->user_id);
                                $isMe = $entry->user_id === $userId;
                                $medal = match($idx) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => null };
                                $avg = round($entry->avg_score, 1);
                            ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors <?php echo e($isMe ? 'bg-blue-50/50 font-bold' : ''); ?>">
                                <td class="p-4 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($medal): ?>
                                        <span class="text-xl"><?php echo e($medal); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 <?php echo e($isMe ? 'text-blue-600 font-black' : ''); ?>"><?php echo e($idx + 1); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full <?php echo e($isMe ? 'bg-blue-600' : 'bg-gray-200'); ?> flex items-center justify-center text-white font-bold text-sm shrink-0">
                                            <?php echo e($isMe ? mb_substr(Auth::user()->name, 0, 1) : mb_substr($user?->name ?? 'مستخدم', 0, 1)); ?>

                                        </div>
                                        <span class="<?php echo e($isMe ? 'text-blue-700' : 'text-gray-900'); ?> truncate">
                                            <?php echo e($isMe ? Auth::user()->name : ($user?->name ?? 'مستخدم')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMe): ?> <span class="text-xs text-blue-500">(أنت)</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-black <?php echo e($avg >= 80 ? 'text-emerald-600' : ($avg >= 60 ? 'text-amber-600' : 'text-red-500')); ?>"><?php echo e($avg); ?>%</span>
                                </td>
                                <td class="p-4 text-center text-gray-600"><?php echo e($entry->total_tests); ?></td>
                                <td class="p-4 text-center">
                                    <span class="font-bold <?php echo e($entry->passed_tests == $entry->total_tests ? 'text-emerald-600' : 'text-amber-600'); ?>"><?php echo e($entry->passed_tests); ?>/<?php echo e($entry->total_tests); ?></span>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userTotal > 0 && $leaderboard->isNotEmpty() && $userRank === false): ?>
        <div class="mt-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-3">ترتيبي</h2>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg"><?php echo e(mb_substr(Auth::user()->name, 0, 1)); ?></div>
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900"><?php echo e(Auth::user()->name); ?></span>
                        <span class="text-gray-400">— من التصنيف</span>
                    </div>
                    <div class="flex gap-4 mt-1 text-sm text-gray-500">
                        <span>المتوسط: <span class="font-bold <?php echo e($userAvg >= 80 ? 'text-emerald-600' : ($userAvg >= 60 ? 'text-amber-600' : 'text-red-500')); ?>"><?php echo e($userAvg ? number_format($userAvg, 1) : '—'); ?>%</span></span>
                        <span>الاختبارات: <span class="font-bold text-gray-700"><?php echo e($userTotal); ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'overview'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views/student/leaderboard.blade.php ENDPATH**/ ?>