<?php $__env->startSection('title', 'منصة المئة | قدرات وتحصيلي'); ?>

<?php
$s = $settings ?? [];
$hero = $s['hero'] ?? [];
$stats = $s['stats'] ?? [
    ['id' => 'students', 'label' => 'طالب وطالبة', 'displayValue' => '150,000+'],
    ['id' => 'courses', 'label' => 'دورة تدريبية', 'displayValue' => '500+'],
    ['id' => 'assets', 'label' => 'مادة تعليمية', 'displayValue' => '50,000+'],
    ['id' => 'rating', 'label' => 'تقييم عام', 'displayValue' => '4.8'],
];
$testimonials = $s['testimonials'] ?? [
    ['name' => 'سارة العتيبي', 'degree' => '98% قدرات', 'text' => 'المنصة غيرت طريقة مذاكرتي تمامًا. تحليل نقاط الضعف ساعدني أركز جهدي في المكان الصح.', 'image' => 'https://i.pravatar.cc/100?img=5'],
    ['name' => 'فهد الشمري', 'degree' => '96% تحصيلي', 'text' => 'الشروحات والتدريبات كانت مرتبة جدًا وواضحة، وحسيت فعلًا أن عندي خطة كاملة وليست مجرد دروس.', 'image' => 'https://i.pravatar.cc/100?img=11'],
    ['name' => 'نورة السالم', 'degree' => '99% قدرات', 'text' => 'الاختبارات المحاكية كانت قريبة جدًا من الاختبار الحقيقي، وهذا رفع ثقتي قبل يوم الاختبار.', 'image' => 'https://i.pravatar.cc/100?img=9'],
];
$whyChoose = $s['whyChoose'] ?? [];
$paths = \App\Models\Path::where('is_active', true)->orderBy('sort_order')->get();
$featuredCourses = \App\Models\Course::where('is_published', true)->latest()->take(3)->get();
$colors = ['indigo', 'amber', 'emerald', 'purple', 'rose', 'blue'];
$whyDefaultFeatures = [
    ['icon' => 'video', 'title' => 'شرح مباشر وتفاعلي', 'description' => 'احضر الحصص وتابع الشرح بخطوات منظمة تناسب مستواك.'],
    ['icon' => 'users', 'title' => 'مدربون معتمدون', 'description' => 'كادر تعليمي مؤهل وذو خبرة طويلة في القدرات والتحصيلي.'],
    ['icon' => 'chart', 'title' => 'تحليل أداء دقيق', 'description' => 'نظام ذكي يتعرف على نقاط الضعف ويقترح تمارين مخصصة.'],
    ['icon' => 'book', 'title' => 'آلاف الأسئلة', 'description' => 'بنك أسئلة ضخم يغطي جميع أقسام الاختبار مع حلول مفصلة.'],
];
$whyBullets = $whyChoose['bullets'] ?? ['تحديثات مستمرة للأسئلة والتدريب', 'مسارات تأسيس وتدريب ومراجعة في مكان واحد', 'دعم فني وأكاديمي متواصل'];
?>

<?php $__env->startSection('content'); ?>

<section class="relative bg-gradient-to-b from-indigo-50 via-white to-white pt-16 pb-24 overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-amber-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute top-[20%] left-[-10%] w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] right-[20%] w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-12">
            <div class="lg:w-1/2 text-center lg:text-right">
                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 px-4 py-2 rounded-full text-sm font-bold mb-6 border border-blue-100 shadow-sm">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                    </span>
                    <?php echo e($hero['badgeText'] ?? 'المنصة الأولى للقدرات والتحصيلي'); ?>

                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black text-gray-900 leading-tight mb-6">
                    <span><?php echo e($hero['titlePrefix'] ?? 'حقق'); ?></span>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600"><?php echo e($hero['titleHighlight'] ?? 'المئة'); ?></span>
                    <br>
                    <span><?php echo e($hero['titleSuffix'] ?? 'في اختباراتك'); ?></span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    <?php echo e($hero['description'] ?? 'رحلة تعليمية ذكية تجمع بين التدريب المكثف، الشروحات التفاعلية، والتحليل الدقيق لنقاط ضعفك لضمان أعلى الدرجات.'); ?>

                </p>
                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                    <a href="<?php echo e($hero['primaryCtaLink'] ?? route('register')); ?>" class="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 text-white text-lg font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <?php echo e($hero['primaryCtaLabel'] ?? 'ابدأ التدريب مجانًا'); ?>

                    </a>
                    <a href="<?php echo e($hero['secondaryCtaLink'] ?? route('courses')); ?>" class="w-full sm:w-auto bg-white text-gray-700 border border-gray-200 text-lg font-bold px-8 py-4 rounded-xl hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <?php echo e($hero['secondaryCtaLabel'] ?? 'تصفح الدورات'); ?>

                    </a>
                </div>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 sm:gap-6 text-sm text-gray-500 font-medium">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>ضمان تحسن المستوى</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>مدربون معتمدون</span>
                    </div>
                </div>
            </div>
            <div class="lg:w-1/2 relative w-full">
                <div class="relative w-full max-w-lg mx-auto">
                    <img src="<?php echo e($hero['imageUrl'] ?? 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&h=600&fit=crop'); ?>" alt="<?php echo e($hero['imageAlt'] ?? 'طالب يستخدم منصة المئة'); ?>" class="w-full h-auto rounded-3xl shadow-2xl border-4 border-white relative z-10 transform transition-transform hover:scale-[1.02]">
                    <div class="absolute -bottom-4 right-2 sm:-bottom-6 sm:-right-6 z-20 bg-white/90 backdrop-blur-md p-3 sm:p-4 rounded-2xl shadow-xl border border-white/50 max-w-[180px] sm:max-w-[200px] animate-bounce-slow">
                        <div class="flex items-center gap-2 mb-2 border-b border-gray-100 pb-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 12h-4V8"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-800"><?php echo e($hero['floatingCardTitle'] ?? 'منصة المئة'); ?></div>
                                <div class="text-[10px] text-emerald-500 font-bold"><?php echo e($hero['floatingCardSubtitle'] ?? 'مستواك: متقدم'); ?></div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="h-1.5 bg-gray-100 rounded-full w-full overflow-hidden">
                                <div class="h-full bg-blue-500 w-3/4"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-gray-500">
                                <span><?php echo e($hero['floatingCardProgressLabel'] ?? 'التقدم'); ?></span>
                                <span><?php echo e($hero['floatingCardProgressValue'] ?? '75%'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-6 left-2 sm:top-10 sm:-left-10 z-20 bg-white p-3 rounded-2xl shadow-lg animate-float">
                        <div class="text-amber-500 font-black text-xl">A+</div>
                    </div>
                    <div class="absolute bottom-16 left-2 sm:bottom-20 sm:-left-4 z-0 bg-indigo-600 text-white p-3 rounded-2xl shadow-lg animate-float animation-delay-2000">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="bg-blue-900 text-white py-10 relative overflow-hidden"
         x-data="counterApp()"
         x-init="init()"
         x-intersect.enter.threshold.50="startCounters()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center divide-x divide-blue-800 divide-x-reverse">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div>
                <div class="text-3xl md:text-4xl font-black text-amber-400 mb-1">
                    <span x-ref="counter<?php echo e($loop->index); ?>" x-init="$el.textContent = '0'"><?php echo e($stat['displayValue'] ?? '0'); ?></span>
                </div>
                <div class="text-blue-200 text-sm font-bold"><?php echo e($stat['label'] ?? ''); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fbbf24 1px, transparent 1px); background-size: 20px 20px;"></div>
</section>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paths->count() > 0): ?>
<section class="py-20 bg-gray-50" x-intersect.threshold.20="$el.classList.add('animate-fade-in-up')" style="opacity: 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">كل ما تحتاجه للتفوق</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">نقدم لك أدوات تعليمية متكاملة تغطي كافة جوانب التدريب والتقييم.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $paths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php $c = $colors[$loop->index % count($colors)]; ?>
            <a href="/courses?path=<?php echo e($path->slug); ?>" class="group block h-full w-full">
                <div class="w-full min-h-44 sm:h-48 bg-white border-2 border-gray-100 flex flex-col items-center justify-center shadow-sm hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-2 rounded-3xl relative overflow-hidden"
                    style="border-color: <?php echo e(match($c) { 'indigo' => '#c7d2fe', 'amber' => '#fde68a', 'emerald' => '#a7f3d0', 'purple' => '#ddd6fe', 'rose' => '#fecdd3', 'blue' => '#bfdbfe', default => '#e5e7eb' }); ?>;">
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="mb-4 p-4 rounded-2xl group-hover:scale-110 transition-transform shadow-sm" style="background-color: <?php echo e(match($c) { 'indigo' => '#e0e7ff', 'amber' => '#fef3c7', 'emerald' => '#d1fae5', 'purple' => '#ede9fe', 'rose' => '#ffe4e6', 'blue' => '#dbeafe', default => '#f3f4f6' }); ?>; color: <?php echo e(match($c) { 'indigo' => '#4338ca', 'amber' => '#b45309', 'emerald' => '#047857', 'purple' => '#6d28d9', 'rose' => '#be123c', 'blue' => '#1d4ed8', default => '#4b5563' }); ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold tracking-wide text-gray-900 mb-2 text-center px-3 break-words"><?php echo e($path->name_ar ?: $path->name); ?></h3>
                        <p class="text-gray-500 text-xs font-medium px-6 text-center leading-relaxed"><?php echo e($path->description_ar ?: ($path->description ?? 'تأسيس وتدريب شامل')); ?></p>
                    </div>
                </div>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredCourses->count() > 0): ?>
<section class="py-20 bg-white" x-intersect.threshold.20="$el.classList.add('animate-fade-in-up')" style="opacity: 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-right">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-2"><?php echo e($s['sections']['featuredCoursesTitle'] ?? 'الدورات الأكثر طلبًا'); ?></h2>
                <p class="text-gray-500"><?php echo e($s['sections']['featuredCoursesSubtitle'] ?? 'اختر دورتك وابدأ رحلة التفوق اليوم'); ?></p>
            </div>
            <a href="<?php echo e(route('courses')); ?>" class="self-start sm:self-auto text-indigo-600 font-bold hover:underline flex items-center gap-2">
                عرض الكل
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transform rotate-90"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $featuredCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <a href="<?php echo e(route('course-detail', $course->id)); ?>" class="group">
                <div class="overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-500 rounded-3xl group-hover:-translate-y-2 bg-white">
                    <div class="relative aspect-video overflow-hidden">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->thumbnail): ?>
                        <img src="<?php echo e($course->thumbnail); ?>" alt="<?php echo e($course->title); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-black"><?php echo e(mb_substr($course->title, 0, 2)); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-indigo-600 font-black text-sm shadow-sm">
                            <?php echo e($course->price > 0 ? number_format($course->price, 0) . ' ر.س' : 'مجاني'); ?>

                        </div>
                    </div>
                    <div class="p-6 text-right">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-1 text-amber-400">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($s = 0; $s < 5; $s++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <span class="text-xs font-bold text-gray-600 mr-1"><?php echo e($course->rating ?? '4.8'); ?></span>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <?php echo e(number_format($course->student_count ?? 0)); ?> طالب
                            </span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-1"><?php echo e($course->title); ?></h3>
                        <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <?php echo e($course->createdBy?->name ?? 'مدرب معتمد'); ?>

                        </p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                            <span class="text-indigo-600 font-bold text-sm">عرض التفاصيل</span>
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transform rotate-90"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<section class="py-20 bg-gray-50" x-intersect.threshold.20="$el.classList.add('animate-fade-in-up')" style="opacity: 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
            <div class="lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php $features = $whyChoose['features'] ?? []; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="bg-white p-5 sm:p-6 border border-gray-100 hover:shadow-lg transition-shadow flex flex-col gap-3 h-full rounded-2xl">
                    <div class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center mb-2">
                        <?php $iconMap = ['video' => 'M22 12h-4l-3 9L9 3l-3 9H2', 'users' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'chart' => 'M18 20V10M12 20V4M6 20v-6', 'book' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z', 'default' => 'M13 2L3 14h9l-1 8 10-12h-9l1-8z'] ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500"><path d="<?php echo e($iconMap[$f['icon'] ?? ''] ?? $iconMap['default']); ?>"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base sm:text-lg"><?php echo e($f['title'] ?? ''); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed"><?php echo e($f['description'] ?? ''); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $whyDefaultFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="bg-white p-5 sm:p-6 border border-gray-100 hover:shadow-lg transition-shadow flex flex-col gap-3 h-full rounded-2xl">
                    <div class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center mb-2">
                        <?php $iconMap = ['video' => 'M22 12h-4l-3 9L9 3l-3 9H2', 'users' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'chart' => 'M18 20V10M12 20V4M6 20v-6', 'book' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z', 'default' => 'M13 2L3 14h9l-1 8 10-12h-9l1-8z'] ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500"><path d="<?php echo e($iconMap[$wf['icon']]); ?>"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base sm:text-lg"><?php echo e($wf['title']); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed"><?php echo e($wf['description']); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="lg:w-1/2 text-right">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 leading-tight"><?php echo e($whyChoose['title'] ?? 'لماذا يختار الطلاب منصة المئة؟'); ?></h2>
                <p class="text-base sm:text-lg text-gray-600 mb-8 leading-relaxed"><?php echo e($whyChoose['description'] ?? 'نحن لا نقدم مجرد دورات، بل نقدم نظامًا تعليميًا متكاملًا يساعدك على الفهم العميق، التدريب المستمر، وتحليل الأداء بطريقة بسيطة وفعالة.'); ?></p>
                <ul class="space-y-4 mb-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $whyBullets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bullet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <span class="text-gray-700 font-medium"><?php echo e($bullet); ?></span>
                    </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-800 group">
                    اكتشف المزيد
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transform rotate-90 group-hover:-translate-x-1 transition-transform"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>


<section class="py-20 bg-indigo-900 text-white relative overflow-hidden" x-intersect.threshold.20="$el.classList.add('animate-fade-in-up')" style="opacity: 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4"><?php echo e($s['sections']['testimonialsTitle'] ?? 'قصص نجاح نعتز بها'); ?></h2>
            <p class="text-indigo-200"><?php echo e($s['sections']['testimonialsSubtitle'] ?? 'انضم لآلاف الطلاب الذين حققوا أحلامهم معنا'); ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-white/10 backdrop-blur-md border border-white/10 p-5 sm:p-6 rounded-2xl">
                <div class="mb-4 flex items-center gap-3 sm:gap-4">
                    <img src="<?php echo e($t['image'] ?? 'https://i.pravatar.cc/100?img=5'); ?>" alt="<?php echo e($t['name']); ?>" class="w-12 h-12 rounded-full border-2 border-amber-400">
                    <div>
                        <h4 class="font-bold text-sm sm:text-base"><?php echo e($t['name']); ?></h4>
                        <span class="text-amber-400 text-xs font-bold"><?php echo e($t['degree'] ?? ''); ?></span>
                    </div>
                </div>
                <p class="text-indigo-100 text-sm leading-relaxed italic">"<?php echo e($t['text']); ?>"</p>
                <div class="flex gap-1 text-amber-400 mt-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($s = 0; $s < 5; $s++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
    <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-amber-500 opacity-10 rounded-full translate-x-1/3 translate-y-1/3 blur-3xl"></div>
</section>


<section class="py-20 bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white" x-intersect.threshold.20="$el.classList.add('animate-fade-in-up')" style="opacity: 0;">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-5xl font-black mb-6">ابدأ رحلة <span class="text-amber-400">التميز</span> اليوم</h2>
        <p class="text-xl text-blue-200 mb-10 max-w-2xl mx-auto">انضم إلى أكثر من 150,000 طالب وطالبة يحققون نتائجهم المميزة</p>
        <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-10 py-4 rounded-2xl font-bold text-xl transition-all hover:shadow-xl hover:shadow-amber-500/25 hover:-translate-y-0.5">
            إنشاء حساب مجاني
            <span>←</span>
        </a>
    </div>
</section>
<?php $__env->startPush('scripts'); ?>
<script>
    function counterApp() {
        return {
            started: false,
            init() {
            },
            startCounters() {
                if (this.started) return;
                this.started = true;
                <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                this.animateCounter(this.$refs.counter<?php echo e($loop->index); ?>, '<?php echo e($stat['displayValue'] ?? '0'); ?>');
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            },
            animateCounter(el, targetStr) {
                const target = parseFloat(targetStr.replace(/[^0-9.]/g, ''));
                if (isNaN(target) || target <= 0) { el.textContent = targetStr; return; }
                const hasK = targetStr.includes('K') || targetStr.endsWith('+');
                const duration = 1500;
                const start = performance.now();
                const step = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.round(eased * target);
                    if (hasK) {
                        el.textContent = targetStr;
                    } else {
                        el.textContent = current.toLocaleString('ar-SA') + (targetStr.endsWith('+') ? '+' : '');
                    }
                    if (progress < 1) requestAnimationFrame(step);
                    else el.textContent = targetStr;
                };
                requestAnimationFrame(step);
            }
        };
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views/public/landing.blade.php ENDPATH**/ ?>