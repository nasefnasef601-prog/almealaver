<?php $__env->startSection('title', ($subject->name_ar ?? $subject->name) . ' — ' . ($path->name_ar ?? $path->name) . ' — منصة المئة'); ?>

<?php
    use App\Models\Quiz;
    use App\Models\Lesson;
    $user = Auth::user();

    // Courses
    $courses = $subject->courses()->where('is_published', true)->with(['modules.lessons', 'lessonCompletions'])->latest()->get();

    // Skills (Foundation topics)
    $skills = $subject->skills()->where('is_active', true)->orderBy('sort_order')->get();

    // Training quizzes (quiz_type = 'training' or 'quiz')
    $trainingQuizzes = Quiz::where('subject_id', $subject->id)->where('is_published', true)
        ->whereIn('quiz_type', ['quiz', 'training'])->get();

    // Mock exams
    $mockExams = Quiz::where('subject_id', $subject->id)->where('is_published', true)
        ->where('quiz_type', 'mock_exam')->get();

    // Library (published lessons with content)
    $libraryLessons = Lesson::whereHas('course', fn($q) => $q->where('subject_id', $subject->id))
        ->where('is_published', true)
        ->whereNotNull('content_url')
        ->latest()
        ->get();
?>

<?php $__env->startSection('content'); ?>
<div x-data="{ tab: 'courses' }" class="bg-gray-50 min-h-screen">
    
    <section class="bg-gradient-to-b from-blue-900 to-indigo-900 text-white py-10">
        <div class="max-w-7xl mx-auto px-4">
            <a href="<?php echo e(route('category', $path->id)); ?>" class="inline-flex items-center gap-1 text-sm text-blue-200 hover:text-white mb-3 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                العودة للمسار
            </a>
            <h1 class="text-3xl font-black"><?php echo e($subject->name_ar ?? $subject->name); ?></h1>
            <p class="text-blue-200 text-sm mt-1"><?php echo e($path->name_ar ?? $path->name); ?></p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <div class="flex gap-2 overflow-x-auto pb-2 mb-8">
            <button @click="tab = 'courses'" :class="tab === 'courses' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الدورات</button>
            <button @click="tab = 'skills'" :class="tab === 'skills' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التأسيس</button>
            <button @click="tab = 'training'" :class="tab === 'training' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التدريب</button>
            <button @click="tab = 'tests'" :class="tab === 'tests' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الاختبارات</button>
            <button @click="tab = 'library'" :class="tab === 'library' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المكتبة</button>
        </div>

        
        <div x-show="tab === 'courses'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($courses->isEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد دورات بعد</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e(route('course-detail', $course->id)); ?>" class="group bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="h-36 bg-gradient-to-br <?php echo e($loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600'); ?> flex items-center justify-center text-white text-xl font-black relative">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->thumbnail): ?>
                                    <img src="<?php echo e($course->thumbnail); ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->price > 0): ?>
                                    <span class="absolute top-3 left-3 bg-white/90 text-amber-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo e(number_format($course->price, 0)); ?> ريال</span>
                                <?php else: ?>
                                    <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">مجاني</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition-colors"><?php echo e($course->title_ar ?? $course->title); ?></h3>
                                <p class="text-xs text-gray-400 mt-2"><?php echo e($course->modules->sum(fn($m) => $m->lessons->count())); ?> درس</p>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'skills'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skills->isEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد مواضيع تأسيس بعد</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $lessonCount = $skill->courses->sum(fn($c) => $c->lessons->count());
                            $questionCount = $skill->questions->count();
                            $skillUrl = auth()->check()
                                ? route('student.skill.detail', $skill->id)
                                : route('login');
                        ?>
                        <a href="<?php echo e($skillUrl); ?>" class="block bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg hover:border-blue-200 transition-all group">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mb-3 group-hover:from-blue-200 group-hover:to-indigo-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 12h10m-6 0v4"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors"><?php echo e($skill->name_ar ?? $skill->name); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($skill->description_ar ?? $skill->description): ?>
                                <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?php echo e($skill->description_ar ?? $skill->description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="flex gap-2 text-xs text-gray-500">
                                <span class="bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e($lessonCount); ?> شرح</span>
                                <span class="bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e($questionCount); ?> سؤال</span>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'training'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trainingQuizzes->isEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد تدريبات بعد</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trainingQuizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm"><?php echo e($quiz->title_ar ?? $quiz->title); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($quiz->questions()->count()); ?> سؤال</p>
                                </div>
                            </div>
                            <a href="<?php echo e(route('student.quiz.show', $quiz->id)); ?>" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-xl font-bold text-sm transition-colors">ابدأ التدريب</a>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'tests'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mockExams->isEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد اختبارات محاكية بعد</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $mockExams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm"><?php echo e($exam->title_ar ?? $exam->title); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($exam->questions()->count()); ?> سؤال · <?php echo e($exam->time_limit); ?> دقيقة</p>
                                </div>
                            </div>
                            <a href="<?php echo e(route('student.quiz.show', $exam->id)); ?>" class="block text-center bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-xl font-bold text-sm transition-colors">ابدأ الاختبار</a>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'library'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($libraryLessons->isEmpty()): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-500">لا توجد ملفات في المكتبة بعد</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $libraryLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm"><?php echo e($lesson->title_ar ?? $lesson->title); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($lesson->duration_minutes ? $lesson->duration_minutes . ' دقيقة' : 'ملف'); ?></p>
                                </div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->content_url): ?>
                                <a href="<?php echo e($lesson->content_url); ?>" target="_blank" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-xl font-bold text-sm transition-colors">فتح الملف</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\public\subject-learning.blade.php ENDPATH**/ ?>