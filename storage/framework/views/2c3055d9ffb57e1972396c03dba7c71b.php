<?php $__env->startSection('title', $skill->name_ar ?? $skill->name); ?>

<?php
    use App\Models\SkillProgress;
    use App\Models\Course;
    use App\Models\Quiz;

    $user = Auth::user();
    $progress = SkillProgress::where('user_id', $user->id)
        ->where('skill_id', $skill->id)
        ->first();

    $courses = Course::where('skill_id', $skill->id)
        ->where('is_published', true)
        ->with(['modules.lessons' => fn($q) => $q->where('is_published', true)])
        ->get();

    $quizzes = Quiz::whereHas('course', fn($q) => $q->where('skill_id', $skill->id))
        ->where('is_published', true)
        ->withCount('questions')
        ->get();

    $allLessons = $courses->flatMap->modules->flatMap->lessons;

    $enrolledCourseIds = \App\Models\AccessGrant::where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('course_id')
        ->toArray();

    $libraryFiles = $allLessons->filter(fn($l) => in_array($l->content_type, ['pdf', 'file', 'document', 'image']) && $l->content_url);
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto" x-data="{ tab: 'lessons' }">
    
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1"><?php echo e($skill->section?->subject?->name_ar ?? ''); ?> / <?php echo e($skill->section?->name_ar ?? ''); ?></p>
                <h1 class="text-2xl font-black text-gray-900"><?php echo e($skill->name_ar ?? $skill->name); ?></h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skill->description_ar): ?>
                    <p class="text-gray-600 mt-2"><?php echo e($skill->description_ar); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progress): ?>
                <?php
                    $badgeColor = match($progress->status) {
                        'mastered' => 'bg-emerald-100 text-emerald-700',
                        'good' => 'bg-blue-100 text-blue-700',
                        'average' => 'bg-amber-100 text-amber-700',
                        default => 'bg-red-100 text-red-700',
                    };
                    $barColor = match($progress->status) {
                        'mastered' => 'bg-emerald-500',
                        'good' => 'bg-blue-500',
                        'average' => 'bg-amber-500',
                        default => 'bg-red-500',
                    };
                ?>
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-2 border-4 <?php echo e($progress->mastery >= 80 ? 'border-emerald-200' : ($progress->mastery >= 60 ? 'border-blue-200' : ($progress->mastery >= 40 ? 'border-amber-200' : 'border-red-200'))); ?>">
                        <span class="text-2xl font-black <?php echo e($progress->mastery >= 80 ? 'text-emerald-600' : ($progress->mastery >= 60 ? 'text-blue-600' : ($progress->mastery >= 40 ? 'text-amber-600' : 'text-red-600'))); ?>"><?php echo e(number_format($progress->mastery, 0)); ?>%</span>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold <?php echo e($badgeColor); ?>"><?php echo e($progress->status === 'mastered' ? 'متقن' : ($progress->status === 'good' ? 'جيد' : ($progress->status === 'average' ? 'متوسط' : 'ضعيف'))); ?></span>
                    <p class="text-xs text-gray-400 mt-1"><?php echo e($progress->total_attempts); ?> محاولة</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="flex gap-2 mb-6">
        <button @click="tab = 'lessons'" :class="tab === 'lessons' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">الشروحات (<?php echo e($allLessons->count()); ?>)</button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">التدريبات (<?php echo e($quizzes->count()); ?>)</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($libraryFiles->isNotEmpty()): ?>
            <button @click="tab = 'library'" :class="tab === 'library' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">المكتبة (<?php echo e($libraryFiles->count()); ?>)</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'lessons'" x-transition class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allLessons->isNotEmpty()): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $hasAccess = in_array($course->id, $enrolledCourseIds) || $course->is_free;
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($module->lessons->isNotEmpty()): ?>
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                            <h3 class="font-bold text-gray-900 mb-3"><?php echo e($module->title_ar ?? $module->title); ?></h3>
                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($hasAccess ? route('student.lesson.show', ['course' => $course->id, 'lesson' => $lesson->id]) : '#'); ?>"
                                       class="flex items-center gap-3 p-3 rounded-xl <?php echo e($hasAccess ? 'hover:bg-gray-50' : 'opacity-60'); ?> transition-colors <?php echo e(!$hasAccess ? 'cursor-not-allowed' : ''); ?>">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->content_type === 'youtube' || $lesson->content_type === 'video'): ?>
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <?php elseif($lesson->content_type === 'pdf'): ?>
                                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <?php else: ?>
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-sm text-gray-900"><?php echo e($lesson->title_ar ?? $lesson->title); ?></p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->duration_minutes): ?>
                                                <p class="text-xs text-gray-500"><?php echo e($lesson->duration_minutes); ?> دقيقة</p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasAccess): ?>
                                            <span class="text-xs px-2 py-1 bg-gray-100 rounded-full text-gray-500">مقفول</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد دروس لهذه المهارة بعد.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'quizzes'" x-transition class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quizzes->isNotEmpty()): ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $best = \App\Models\QuizResult::where('user_id', $user->id)
                            ->where('quiz_id', $quiz->id)
                            ->max('score_percentage');
                        $attemptsCount = \App\Models\QuizAttempt::where('user_id', $user->id)
                            ->where('quiz_id', $quiz->id)
                            ->count();
                        $canTake = is_null($quiz->max_attempts) || $attemptsCount < $quiz->max_attempts;
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold
                                <?php echo e($quiz->difficulty === 'hard' ? 'bg-red-100 text-red-700' : ($quiz->difficulty === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')); ?>">
                                <?php echo e($quiz->difficulty === 'hard' ? 'صعب' : ($quiz->difficulty === 'medium' ? 'متوسط' : 'سهل')); ?>

                            </span>
                            <span class="text-xs text-gray-400"><?php echo e($quiz->questions_count ?? 0); ?> سؤال</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm mb-3"><?php echo e($quiz->title_ar ?? $quiz->title); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($best !== null): ?>
                            <div class="mb-3">
                                <p class="text-xs text-gray-500 mb-1">أفضل نتيجة: <span class="font-bold <?php echo e($best >= 70 ? 'text-emerald-600' : 'text-amber-600'); ?>"><?php echo e(number_format($best, 0)); ?>%</span></p>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?php echo e($best >= 70 ? 'bg-emerald-500' : 'bg-amber-500'); ?>" style="width: <?php echo e($best); ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canTake): ?>
                            <a href="<?php echo e(route('student.quiz.show', $quiz->id)); ?>" class="block w-full text-center py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors">ابدأ الاختبار</a>
                        <?php else: ?>
                            <a href="<?php echo e(route('student.quiz.result', $quiz->id)); ?>" class="block w-full text-center py-2 bg-gray-100 text-gray-500 rounded-xl text-sm font-bold cursor-not-allowed">انتهت المحاولات</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد تدريبات لهذه المهارة بعد.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($libraryFiles->isNotEmpty()): ?>
    <div x-show="tab === 'library'" x-transition class="space-y-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $libraryFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isPdf = $file->content_type === 'pdf';
                    $ext = $isPdf ? 'pdf' : pathinfo($file->content_url, PATHINFO_EXTENSION);
                ?>
                <a href="<?php echo e(asset('storage/' . $file->content_url)); ?>" target="_blank"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl <?php echo e($isPdf ? 'bg-red-50' : 'bg-blue-50'); ?> flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPdf): ?>
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="font-bold text-sm text-gray-900 group-hover:text-blue-600 transition-colors"><?php echo e($file->title_ar ?? $file->title); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file->duration_minutes): ?>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($file->duration_minutes); ?> دقيقة</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full <?php echo e($isPdf ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?> font-bold uppercase"><?php echo e($ext); ?></span>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'skills'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\skill-detail.blade.php ENDPATH**/ ?>