<?php $__env->startSection('title', 'دوراتي'); ?>

<?php
    use App\Models\Course;
    use App\Models\AccessGrant;
    use App\Models\LessonCompletion;

    $user = Auth::user();
    $enrolledIds = AccessGrant::where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('course_id')
        ->toArray();
    $freeIds = Course::where('price', 0)->where('is_published', true)->pluck('id')->toArray();
    $allIds = array_unique(array_merge($enrolledIds, $freeIds));

    $courses = Course::with(['modules.lessons' => fn($q) => $q->where('is_published', true)])
        ->whereIn('id', $allIds)
        ->latest()
        ->get();
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl" x-data="{ search: '' }">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">دوراتي</h1>
            <p class="text-gray-500 text-sm">تابع تعلمك من حيث توقفت</p>
        </div>
        <a href="<?php echo e(route('courses')); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800">تصفح الكورسات ←</a>
    </div>

    
    <div class="relative mb-6">
        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="search" placeholder="ابحث عن كورس..." class="w-full px-12 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <?php
        $completedAll = $courses->filter(fn($c) => $c->modules->flatMap(fn($m) => $m->lessons)->isNotEmpty() && LessonCompletion::where('user_id', $user->id)->whereIn('lesson_id', $c->modules->flatMap(fn($m) => $m->lessons)->pluck('id'))->count() === $c->modules->flatMap(fn($m) => $m->lessons)->count());
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($courses->isEmpty()): ?>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-gray-500 text-lg mb-2">لم تسجل في أي كورس بعد</p>
            <p class="text-gray-400 text-sm mb-6">تصفح الكورسات المتاحة وابدأ رحلة التعلم</p>
            <a href="<?php echo e(route('courses')); ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-colors">
                تصفح الكورسات <span>←</span>
            </a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
                    $totalLessons = $allLessons->count();
                    $completedLessonsCount = LessonCompletion::where('user_id', $user->id)
                        ->whereIn('lesson_id', $allLessons->pluck('id'))
                        ->count();
                    $progress = $totalLessons > 0 ? round(($completedLessonsCount / $totalLessons) * 100) : 0;
                    $isComplete = $totalLessons > 0 && $completedLessonsCount >= $totalLessons;
                    $searchText = ($course->title_ar ?? $course->title) . ' ' . ($course->subject?->name_ar ?? '');
                ?>
                <div x-show="search === '' || '<?php echo e(str_replace("'", "\'", $searchText)); ?>'.includes(search)"
                     x-transition
                     class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 group">
                    <div class="h-40 bg-gradient-to-br <?php echo e($loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600'); ?> flex items-center justify-center text-white text-2xl font-black relative">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->price == 0): ?>
                            <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">مجاني</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isComplete): ?>
                            <div class="absolute top-3 right-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                مكتمل
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="drop-shadow-lg"><?php echo e(mb_substr($course->title_ar ?? $course->title, 0, 2)); ?></span>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 mb-1 group-hover:text-amber-600 transition-colors"><?php echo e($course->title_ar ?? $course->title); ?></h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->subject): ?>
                            <p class="text-xs text-gray-400 mb-3"><?php echo e($course->subject->name_ar ?? $course->subject->name); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full <?php echo e($isComplete ? 'bg-emerald-500' : 'bg-blue-500'); ?>" style="width: <?php echo e($progress); ?>%"></div>
                            </div>
                            <span class="text-xs font-bold <?php echo e($isComplete ? 'text-emerald-600' : 'text-gray-500'); ?>"><?php echo e($progress); ?>%</span>
                        </div>
                        <a href="<?php echo e(route('student.course-detail', $course->id)); ?>" class="block text-center py-2.5 rounded-xl font-bold text-sm transition-colors <?php echo e($isComplete ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isComplete): ?> عرض الكورس <?php elseif($progress > 0): ?> متابعة التعلم <?php else: ?> بدء التعلم <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </a>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'my-courses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\courses.blade.php ENDPATH**/ ?>