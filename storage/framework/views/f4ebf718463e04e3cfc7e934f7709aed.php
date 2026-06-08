<?php $__env->startSection('title', ($course->title_ar ?? $course->title) . ' — منصة المئة'); ?>

<?php
    $user = Auth::user();
    $totalLessons = $course->modules->sum(fn($m) => $m->lessons->count());
    $instructor = $course->assignedTeacher ?: $course->creator;
    $hasAccess = $user && $user->accessGrants()
        ->where('course_id', $course->id)
        ->where('status', 'active')
        ->exists();
    $hasPendingPayment = $user && \App\Models\PaymentRequest::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->whereIn('status', ['pending_manual_review', 'approved'])
        ->exists();
    $relatedCourses = \App\Models\Course::with('subject', 'modules.lessons')
        ->where('is_published', true)
        ->where('id', '!=', $course->id)
        ->when($course->subject_id, fn($q) => $q->where('subject_id', $course->subject_id))
        ->latest()
        ->take(3)
        ->get();
?>

<?php $__env->startSection('content'); ?>
<div class="bg-white" x-data="{ tab: 'overview', showPaymentModal: false, processing: false, isLoggedIn: <?php echo e($user ? 'true' : 'false'); ?> }">

    
    <section class="bg-gradient-to-b from-blue-900 to-indigo-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 text-sm text-blue-200 mb-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->subject): ?>
                            <span class="px-3 py-1 rounded-full bg-white/10 text-blue-100 font-bold"><?php echo e($course->subject->name_ar ?: $course->subject->name); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->difficulty_level): ?>
                            <span class="px-3 py-1 rounded-full bg-white/10"><?php echo e(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'][$course->difficulty_level] ?? $course->difficulty_level); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-4 leading-tight"><?php echo e($course->title_ar ?: $course->title); ?></h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->short_description): ?>
                        <p class="text-blue-200 text-lg leading-relaxed mb-6"><?php echo e($course->short_description); ?></p>
                    <?php elseif($course->description_ar ?? $course->description): ?>
                        <p class="text-blue-200 text-lg leading-relaxed mb-6 line-clamp-3"><?php echo e($course->description_ar ?: $course->description); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-blue-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($instructor): ?>
                            <span class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center text-blue-900 font-bold text-xs"><?php echo e(mb_substr($instructor->name, 0, 1)); ?></span>
                                <?php echo e($instructor->name); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span><span class="font-bold text-white"><?php echo e($totalLessons); ?></span> دروس</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->duration_minutes): ?>
                            <span><span class="font-bold text-white"><?php echo e(ceil($course->duration_minutes / 60)); ?></span> ساعة</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->thumbnail): ?>
                        <img src="<?php echo e($course->thumbnail); ?>" alt="<?php echo e($course->title); ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-5xl font-black"><?php echo e(mb_substr($course->title, 0, 2)); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="p-6 text-center">
                        <div class="text-3xl font-black text-gray-900 mb-2">
                            <?php echo e($course->price > 0 ? number_format($course->price, 0) . ' ريال' : 'مجاني'); ?>

                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAccess): ?>
                            <a href="<?php echo e(route('student.course-detail', $course->id)); ?>"
                               class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-bold text-lg transition-colors mb-3">
                                متابعة التعلم ←
                            </a>
                        <?php elseif($hasPendingPayment): ?>
                            <button disabled
                               class="block w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-bold text-lg mb-3 cursor-not-allowed">
                                الطلب قيد المراجعة
                            </button>
                        <?php elseif($course->price > 0): ?>
                            <button @click="isLoggedIn ? showPaymentModal = true : document.getElementById('login-trigger')?.click()"
                               class="block w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-bold text-lg transition-colors mb-3">
                                اشتراك الآن
                            </button>
                        <?php else: ?>
                            <button @click="isLoggedIn ? (window.location = '<?php echo e(route('student.course-detail', $course->id)); ?>') : document.getElementById('login-trigger')?.click()"
                               class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-bold text-lg transition-colors mb-3">
                                بدء التعلم مجاناً
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <ul class="space-y-2 text-right text-sm text-gray-500">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e($totalLessons); ?> درس تفاعلي
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                وصول كامل مدى الحياة
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->has_certificate): ?> شهادة إتمام <?php else: ?> تدريب شامل <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex gap-2 overflow-x-auto pb-2 mb-8">
            <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">عن الكورس</button>
            <button @click="tab = 'syllabus'" :class="tab === 'syllabus' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المنهج</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($instructor): ?>
                <button @click="tab = 'instructor'" :class="tab === 'instructor' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدرب</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'overview'" x-transition>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->description_ar ?? $course->description): ?>
                <div class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm mb-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">وصف الكورس</h2>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-line"><?php echo e($course->description_ar ?: $course->description); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->short_description): ?>
                <div class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <h2 class="text-xl font-black text-gray-900 mb-4">ماذا ستتعلم</h2>
                    <p class="text-gray-600"><?php echo e($course->short_description); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'syllabus'" x-transition>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-black text-gray-900 mb-2">المنهج الدراسي</h2>
                <p class="text-gray-500 text-sm mb-6"><?php echo e($totalLessons); ?> دروس موزعة على <?php echo e($course->modules->count()); ?> أقسام</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="mb-3 last:mb-0" x-data="{ open: true }">
                        <button @click="open = !open" class="w-full flex items-center justify-between p-4 bg-gray-50 rounded-2xl font-bold text-gray-900 text-sm">
                            <span><?php echo e($module->title_ar ?: $module->title); ?></span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="mt-2 space-y-1 pr-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($lesson->title_ar ?: $lesson->title); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->duration_minutes): ?>
                                            <p class="text-xs text-gray-400"><?php echo e($lesson->duration_minutes); ?> دقيقة</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->is_free): ?>
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">مجاني</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <p class="text-gray-400 text-sm py-2">لا توجد دروس في هذا القسم.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="text-center py-12 text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <p>لم يتم إضافة محتوى للكورس بعد.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($instructor): ?>
            <div x-show="tab === 'instructor'" x-transition>
                <div class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-white font-black text-xl shadow-sm"><?php echo e(mb_substr($instructor->name, 0, 1)); ?></div>
                        <div>
                            <h2 class="text-xl font-black text-gray-900"><?php echo e($instructor->name); ?></h2>
                            <p class="text-gray-500"><?php echo e($instructor->email); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($relatedCourses->isNotEmpty()): ?>
        <section class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-black text-gray-900 mb-8">كورسات مشابهة</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $relatedCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $rcLessons = $rc->modules->sum(fn($m) => $m->lessons->count()); ?>
                        <a href="<?php echo e(route('course-detail', $rc->id)); ?>" class="group bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="h-36 bg-gradient-to-br <?php echo e($loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600'); ?> flex items-center justify-center text-white text-2xl font-black relative">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rc->thumbnail): ?>
                                    <img src="<?php echo e($rc->thumbnail); ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php echo e(mb_substr($rc->title_ar ?? $rc->title, 0, 2)); ?>

                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition-colors text-sm"><?php echo e($rc->title_ar ?? $rc->title); ?></h3>
                                <p class="text-xs text-gray-400 mt-2"><?php echo e($rcLessons); ?> درس</p>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showPaymentModal = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full p-8" @click.outside="showPaymentModal = false">
            <button @click="showPaymentModal = false" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h2 class="text-xl font-black text-gray-900 mb-2">تأكيد طلب الاشتراك</h2>
                <p class="text-gray-500 text-sm mb-6">سيتم إرسال طلب اشتراك في الكورس للمراجعة من قبل الإدارة</p>
                <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-right">
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600"><?php echo e($course->title_ar ?? $course->title); ?></span>
                        <span class="font-bold text-gray-900"><?php echo e(number_format($course->price, 0)); ?> ريال</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 flex justify-between items-center">
                        <span class="font-bold text-gray-900">الإجمالي</span>
                        <span class="font-bold text-amber-600 text-lg"><?php echo e(number_format($course->price, 0)); ?> ريال</span>
                    </div>
                </div>
                <form action="<?php echo e(route('student.payment-request')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="course_id" value="<?php echo e($course->id); ?>">
                    <input type="hidden" name="amount" value="<?php echo e($course->price); ?>">
                    <button type="submit"
                            x-ref="submitBtn"
                            @click="processing = true; $refs.submitBtn.disabled = true"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-bold text-lg transition-colors">
                        <span x-show="!processing">تأكيد الطلب</span>
                        <span x-show="processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            جاري الإرسال...
                        </span>
                    </button>
                </form>
                <button @click="showPaymentModal = false" class="mt-3 text-sm text-gray-500 hover:text-gray-700 font-medium">إلغاء</button>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
        <button id="login-trigger" @click="loginModal = true" class="hidden" x-data></button>
        <script>
            document.getElementById('login-trigger')?.addEventListener('click', function() {
                let el = document.querySelector('[x-data*="headerApp"]');
                if (el) el.__x?.$data?.loginModal = true;
            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\public\course-detail.blade.php ENDPATH**/ ?>