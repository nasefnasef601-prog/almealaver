<?php $__env->startSection('title', ($course->title_ar ?? $course->title) . ' — منصة المئة'); ?>

<?php
    use App\Models\Course;
    use App\Models\CourseModule;
    use App\Models\Lesson;
    use App\Models\AccessGrant;
    use App\Models\LessonCompletion;

    $course = Course::with(['modules.lessons' => fn($q) => $q->where('is_published', true)->orderBy('sort_order')])
        ->with(['lessonCompletions' => fn($q) => $q->where('user_id', Auth::id())])
        ->findOrFail($courseId);
    $user = Auth::user();
    $hasAccess = AccessGrant::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->where('status', 'active')
        ->exists();
    $isFree = $course->price == 0;
    $canAccess = $hasAccess || $isFree;
    $allLessonIds = $course->modules->flatMap(fn($m) => $m->lessons->pluck('id'));
    $totalLessons = $allLessonIds->count();
    $completedLessonIds = $course->lessonCompletions->pluck('lesson_id')->toArray();
    $completedLessons = count($completedLessonIds);
    $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
    $instructor = $course->assignedTeacher ?: $course->creator;
    $nextLessonId = null;
    foreach ($course->modules as $module) {
        foreach ($module->lessons as $lesson) {
            if (!in_array($lesson->id, $completedLessonIds)) {
                $nextLessonId = $lesson->id;
                break 2;
            }
        }
    }
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl">
    <a href="<?php echo e(route('student.courses')); ?>" class="inline-flex items-center gap-1 text-sm font-bold text-gray-500 hover:text-blue-600 mb-4 lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
        العودة للدورات
    </a>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="grid lg:grid-cols-5 gap-0">
            <div class="lg:col-span-3 p-6 sm:p-8">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold"><?php echo e($course->subject?->name_ar ?? $course->subject?->name ?? 'عام'); ?></span>
                    <span><?php echo e($totalLessons); ?> دروس</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900"><?php echo e($course->title_ar ?? $course->title); ?></h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php $isFav = \App\Models\Favorite::isFavorited(auth()->id(), \App\Models\Course::class, $course->id); ?>
                        <button x-data="{ fav: <?php echo e($isFav ? 'true' : 'false'); ?> }"
                                @click="
                                    fetch('<?php echo e(route('student.favorite.toggle')); ?>', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                                        body: JSON.stringify({ type: 'course', id: <?php echo e($course->id); ?> })
                                    }).then(r => r.json()).then(d => { fav = d.favorited; });
                                "
                                :class="fav ? 'text-red-500' : 'text-gray-300 hover:text-red-400'"
                                class="p-2 rounded-xl hover:bg-red-50 transition-all shrink-0">
                            <svg class="w-7 h-7" :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->description_ar ?? $course->description): ?>
                    <p class="text-gray-500 leading-relaxed mb-4"><?php echo e($course->description_ar ?? $course->description); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->duration_minutes): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php echo e(ceil($course->duration_minutes / 60)); ?> ساعة
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->difficulty_level): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <?php echo e(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'][$course->difficulty_level] ?? $course->difficulty_level); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-2 bg-gradient-to-br <?php echo e($isFree ? 'from-emerald-500 to-emerald-700' : 'from-blue-500 to-indigo-700'); ?> p-6 sm:p-8 flex flex-col justify-center items-center text-white">
                <div class="text-5xl mb-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canAccess): ?> 🎉 <?php else: ?> 🔒 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canAccess): ?>
                    <p class="text-lg font-bold mb-1">مسجل ✓</p>
                    <p class="text-sm text-blue-200 mb-4">لديك وصول كامل لهذا الكورس</p>
                    <div class="w-full bg-white/20 rounded-full h-2.5 mb-4">
                        <div class="bg-white rounded-full h-2.5" style="width: <?php echo e($progress); ?>%"></div>
                    </div>
                    <p class="text-sm text-blue-200 mb-4"><?php echo e($progress); ?>% مكتمل</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextLessonId): ?>
                        <a href="<?php echo e(route('student.lesson.show', [$course->id, $nextLessonId])); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-lg">
                            <?php echo e($progress > 0 ? 'متابعة التعلم' : 'بدء التعلم'); ?>

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    <?php else: ?>
                        <div class="text-emerald-200 font-bold text-sm">🎉 أكملت جميع الدروس!</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <p class="text-lg font-bold mb-1"><?php echo e(number_format($course->price, 0)); ?> ريال</p>
                    <p class="text-sm text-blue-200 mb-4">اشترك الآن واحصل على وصول كامل</p>
                    <button @click="document.getElementById('payment-section').scrollIntoView({behavior: 'smooth'})" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">اشتراك الآن</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-xl font-black text-gray-900 mb-4">محتوى الكورس</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="mb-4 last:mb-0" x-data="{ open: true }">
                        <button @click="open = !open" class="w-full flex items-center justify-between p-4 bg-gray-50 rounded-2xl font-bold text-gray-900 text-sm">
                            <span><?php echo e($module->title_ar ?? $module->title); ?></span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" class="mt-2 space-y-1 pr-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $lCompleted = in_array($lesson->id, $completedLessonIds); ?>
                                <a href="<?php echo e($canAccess ? route('student.lesson.show', [$course->id, $lesson->id]) : '#'); ?>"
                                   class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors <?php echo e(!$canAccess ? 'opacity-60 cursor-not-allowed' : ''); ?>">
                                    <div class="w-8 h-8 rounded-lg <?php echo e($lCompleted ? 'bg-emerald-100 text-emerald-600' : ($canAccess ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400')); ?> flex items-center justify-center shrink-0">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lCompleted): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($lesson->title_ar ?? $lesson->title); ?></p>
                                        <p class="text-xs text-gray-400">
                                            <?php echo e($lesson->duration_minutes ? $lesson->duration_minutes . ' دقيقة' : ''); ?>

                                            <?php echo e($lesson->is_free ? '· مجاني' : ''); ?>

                                        </p>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lCompleted): ?>
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <p class="text-gray-400 text-sm py-2">لا توجد دروس في هذا الجزء.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-gray-400 text-center py-8">لم يتم إضافة محتوى للكورس بعد.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$canAccess): ?>
                <div id="payment-section" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3">اشتراك في الكورس</h3>
                    <p class="text-3xl font-black text-blue-600 mb-4"><?php echo e(number_format($course->price, 0)); ?> <span class="text-base font-normal text-gray-400">ريال</span></p>
                    <form method="POST" action="<?php echo e(route('student.payment-request')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="course_id" value="<?php echo e($course->id); ?>">
                        <input type="hidden" name="amount" value="<?php echo e($course->price); ?>">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">تحويل بنكي</label>
                            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-600 space-y-1 mb-3">
                                <p><strong>البنك:</strong> <?php $paySetting = \App\Models\PaymentSetting::where('payment_method', 'bank_transfer')->first(); ?> <?php echo e($paySetting?->config['bank_name'] ?? 'البنك الأهلي السعودي'); ?></p>
                                <p><strong>الحساب:</strong> <?php echo e($paySetting?->config['account_name'] ?? 'منصة المئة'); ?></p>
                                <p><strong>رقم الحساب:</strong> <?php echo e($paySetting?->config['account_number'] ?? 'SA1234567890'); ?></p>
                            </div>
                            <label class="block text-sm font-medium text-gray-600 mb-1 cursor-pointer">
                                <input type="file" name="bank_transfer_receipt" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" required>
                            </label>
                            <p class="text-xs text-gray-400 mt-1">أرفق صورة الإيصال (jpg, png - حد أقصى 5MB)</p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition-colors">
                            شراء الكورس
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-3 text-center">سيتم مراجعة طلبك من قبل الإدارة</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-3">معلومات الكورس</h3>
                <ul class="space-y-3 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->duration_minutes): ?>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-500">المدة</span>
                            <span class="font-bold text-gray-900"><?php echo e(ceil($course->duration_minutes / 60)); ?> ساعة</span>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">الدروس</span>
                        <span class="font-bold text-gray-900"><?php echo e($totalLessons); ?></span>
                    </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($instructor): ?>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-500">المدرب</span>
                            <span class="font-bold text-gray-900"><?php echo e($instructor->name); ?></span>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">المستوى</span>
                        <span class="font-bold text-gray-900"><?php echo e($course->difficulty_level ? ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'][$course->difficulty_level] : 'جميع المستويات'); ?></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500">السعر</span>
                        <span class="font-bold <?php echo e($isFree ? 'text-emerald-600' : 'text-blue-600'); ?>"><?php echo e($isFree ? 'مجاني' : number_format($course->price, 0) . ' ريال'); ?></span>
                    </li>
                </ul>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canAccess): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3">تقدمك</h3>
                    <div class="text-center">
                        <div class="text-4xl font-black text-blue-600 mb-2"><?php echo e($progress); ?>%</div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
                            <div class="bg-amber-500 rounded-full h-2.5 transition-all" style="width: <?php echo e($progress); ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mb-4"><?php echo e($completedLessons); ?> من <?php echo e($totalLessons); ?> دروس</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextLessonId): ?>
                            <a href="<?php echo e(route('student.lesson.show', [$course->id, $nextLessonId])); ?>" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">
                                <?php echo e($progress > 0 ? 'استمر في التعلم' : 'ابدأ التعلم'); ?>

                            </a>
                        <?php else: ?>
                            <div class="text-emerald-600 font-bold text-sm">🎉 تهانينا! أكملت الكورس</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'my-courses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\course-detail.blade.php ENDPATH**/ ?>