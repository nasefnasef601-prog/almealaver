<?php $__env->startSection('title', 'لوحة الطالب'); ?>

<?php
    use App\Models\Course;
    use App\Models\QuizAttempt;
    use App\Models\QuizResult;
    use App\Models\PaymentRequest;
    use App\Models\LessonCompletion;
    use App\Models\AccessGrant;
    use App\Models\Favorite;
    use Carbon\Carbon;

    $user = Auth::user();
    $enrolledCourseIds = AccessGrant::where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('course_id')
        ->toArray();
    $courses = Course::whereIn('id', $enrolledCourseIds)
        ->orWhere('price', 0)
        ->withCount(['lessons' => fn($q) => $q->where('is_published', true)])
        ->with(['lessonCompletions' => fn($q) => $q->where('user_id', $user->id)])
        ->take(6)
        ->get();
    $totalCourses = Course::whereIn('id', $enrolledCourseIds)->orWhere('price', 0)->count();
    $attempts = QuizAttempt::where('user_id', $user->id)->count();
    $avgScore = QuizResult::where('user_id', $user->id)->avg('score_percentage');
    $payments = PaymentRequest::where('user_id', $user->id)->count();
    $completedLessons = LessonCompletion::where('user_id', $user->id)->count();
    $accessGrants = AccessGrant::where('user_id', $user->id)->where('status', 'active')->count();

    // Weakest skills for recommendations
    $weakSkills = \App\Models\SkillProgress::where('user_id', $user->id)
        ->where('total_questions', '>=', 3)
        ->orderBy('mastery')
        ->take(3)
        ->get();

    // Past 7 days quiz scores for mini chart
    $weeklyScores = QuizResult::where('user_id', $user->id)
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at')
        ->get()
        ->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
        ->map(fn($day) => round($day->avg('score_percentage'), 1));

    // Streak tracking
    $activityDates = LessonCompletion::where('user_id', $user->id)
        ->selectRaw('DATE(created_at) as d')
        ->groupBy('d')
        ->pluck('d')
        ->merge(
            QuizAttempt::where('user_id', $user->id)
                ->selectRaw('DATE(created_at) as d')
                ->groupBy('d')
                ->pluck('d')
        )->unique()->sort()->values();

    $streak = 0;
    $checkDate = now()->format('Y-m-d');
    foreach ($activityDates->reverse() as $ad) {
        if ($ad === $checkDate) {
            $streak++;
            $checkDate = Carbon::parse($checkDate)->subDay()->format('Y-m-d');
        } elseif ($ad === Carbon::parse($checkDate)->subDay()->format('Y-m-d')) {
            $streak++;
            $checkDate = Carbon::parse($ad)->format('Y-m-d');
        } else {
            break;
        }
    }

    // Last 7 days activity for mini calendar
    $weekDays = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $weekDays[] = [
            'date' => $date,
            'day' => now()->subDays($i)->format('D'),
            'active' => $activityDates->contains($date),
            'isToday' => $i === 0,
        ];
    }

    $todayCompleted = LessonCompletion::where('user_id', $user->id)
        ->whereDate('created_at', now())->count();
    $todayQuizzes = QuizAttempt::where('user_id', $user->id)
        ->whereDate('created_at', now())->count();
?>

<?php $__env->startSection('content'); ?>
<div x-data="{ tab: '<?php echo e(request('tab', 'overview')); ?>' }" class="max-w-6xl">

    
    <div class="flex gap-2 overflow-x-auto pb-2 mb-6 scrollbar-thin">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الرئيسية</button>
        <button @click="tab = 'my-courses'" :class="tab === 'my-courses' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">دوراتي</button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">الاختبارات</button>
        <button @click="tab = 'reports'" :class="tab === 'reports' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">التقارير</button>
        <a href="<?php echo e(route('student.skills')); ?>" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0 bg-white text-gray-600 border border-gray-200 hover:border-amber-300 hover:bg-amber-50">المهارات</a>
        <a href="<?php echo e(route('student.results')); ?>" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0 bg-white text-gray-600 border border-gray-200 hover:border-amber-300 hover:bg-amber-50">النتائج</a>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المدفوعات</button>
        <button @click="tab = 'plan'" :class="tab === 'plan' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">خطة الدراسة</button>
        <button @click="tab = 'favorites'" :class="tab === 'favorites' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm whitespace-nowrap transition-all shrink-0">المفضلة</button>
    </div>

    
    <div x-show="tab === 'overview'" x-transition>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-blue-100 text-sm font-medium">الكورسات المسجلة</p>
                <p class="text-3xl font-black mt-1"><?php echo e($totalCourses); ?></p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-emerald-100 text-sm font-medium">الاختبارات المحاولة</p>
                <p class="text-3xl font-black mt-1"><?php echo e($attempts); ?></p>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-amber-100 text-sm font-medium">متوسط النتيجة</p>
                <p class="text-3xl font-black mt-1"><?php echo e($avgScore ? number_format($avgScore, 1) : '—'); ?>%</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-lg">
                <p class="text-purple-100 text-sm font-medium">الدروس المكتملة</p>
                <p class="text-3xl font-black mt-1"><?php echo e($completedLessons); ?></p>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($weeklyScores->isNotEmpty()): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-900 text-sm">آخر 7 أيام</h3>
                <?php $trend = $weeklyScores->count() >= 2 ? ($weeklyScores->last() - $weeklyScores->first()) : 0; ?>
                <span class="text-xs font-bold <?php echo e($trend >= 0 ? 'text-emerald-600' : 'text-red-500'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trend > 0): ?> +<?php echo e(number_format($trend, 1)); ?>% <?php elseif($trend < 0): ?> <?php echo e(number_format($trend, 1)); ?>% <?php else: ?> — <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
            <div class="flex items-end gap-2 h-16">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 6; $i >= 0; $i--): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $date = now()->subDays($i)->format('Y-m-d');
                        $score = $weeklyScores[$date] ?? null;
                        $height = $score ? max(($score / 100) * 56, 4) : 2;
                        $hasScore = !is_null($score);
                    ?>
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full rounded-md <?php echo e($hasScore ? ($score >= 70 ? 'bg-emerald-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-red-500')) : 'bg-gray-100'); ?>" style="height: <?php echo e($height); ?>px; min-height: 2px;"></div>
                        <span class="text-[10px] text-gray-400"><?php echo e(now()->subDays($i)->format('D')); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">أحدث التقدم في الكورسات</h2>
                    <a href="?tab=my-courses" class="text-sm font-bold text-blue-600 hover:text-blue-800">عرض الكل ←</a>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $courses->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $total = $course->lessons_count;
                        $completed = $course->lessonCompletions->count();
                        $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                    ?>
                    <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br <?php echo e($loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600'); ?> flex items-center justify-center text-white font-bold shrink-0">
                            <?php echo e(mb_substr($course->title, 0, 1)); ?>

                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate"><?php echo e($course->title); ?></p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: <?php echo e($pct); ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo e($pct); ?>%</span>
                            </div>
                        </div>
                        <a href="<?php echo e(route('student.course-detail', $course->id)); ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800 shrink-0">استمر ←</a>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="text-center py-8 text-gray-400">
                        <p class="mb-2">لم تسجل في أي كورس بعد</p>
                        <a href="<?php echo e(route('courses')); ?>" class="text-blue-600 font-bold hover:underline">تصفح الكورسات</a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm text-center">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">استمرارية التعلم</h2>
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <span class="text-6xl">🔥</span>
                        <span class="text-5xl font-black <?php echo e($streak > 0 ? 'text-orange-500' : 'text-gray-300'); ?>"><?php echo e($streak); ?></span>
                    </div>
                    <p class="text-gray-500 text-sm mb-4"><?php echo e($streak > 0 ? "أيام متتالية من التعلم!" : "ابدأ رحلة التعلم اليوم"); ?></p>
                    
                    <div class="flex justify-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-gray-400"><?php echo e($wd['day']); ?></span>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all
                                    <?php echo e($wd['isToday'] ? 'ring-2 ring-blue-400 ring-offset-1' : ''); ?>

                                    <?php echo e($wd['active'] ? 'bg-gradient-to-br from-orange-400 to-orange-500 text-white shadow-sm' : 'bg-gray-100 text-gray-300'); ?>">
                                    <?php echo e(now()->parse($wd['date'])->format('d')); ?>

                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayCompleted > 0 || $todayQuizzes > 0): ?>
                        <div class="mt-4 text-xs text-gray-500">
                            اليوم: <?php echo e($todayCompleted); ?> درس<?php echo e($todayCompleted != 1 ? 'وس' : ''); ?> • <?php echo e($todayQuizzes); ?> اختبار<?php echo e($todayQuizzes != 1 ? 'ات' : ''); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($weakSkills->isNotEmpty()): ?>
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">مهارات تحتاج تحسين</h2>
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $weakSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $pct = $sp->mastery ?? 0;
                                $barColor = $pct < 40 ? 'bg-red-500' : ($pct < 60 ? 'bg-amber-500' : 'bg-emerald-500');
                            ?>
                            <a href="<?php echo e(route('student.skill.detail', $sp->skill_id)); ?>" class="block p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900"><?php echo e($sp->skill?->name_ar ?? $sp->skill?->name ?? 'مهارة'); ?></span>
                                    <span class="text-xs font-bold <?php echo e($pct < 40 ? 'text-red-600' : ($pct < 60 ? 'text-amber-600' : 'text-emerald-600')); ?>"><?php echo e(number_format($pct, 0)); ?>%</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?php echo e($barColor); ?>" style="width: <?php echo e($pct); ?>%"></div>
                                </div>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <a href="<?php echo e(route('student.skills')); ?>" class="block text-center text-sm font-bold text-blue-600 mt-3 hover:text-blue-800">عرض كل المهارات ←</a>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">مسار التعلم</h2>
                    <div class="space-y-3">
                        <a href="<?php echo e(route('student.quiz.list')); ?>" class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-900">تجربة اختبار جديد</p>
                                <p class="text-xs text-gray-500">اختبر مستواك الآن</p>
                            </div>
                        </a>
                        <a href="<?php echo e(route('student.courses')); ?>" class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-900">متابعة التعلم</p>
                                <p class="text-xs text-gray-500">ارجع إلى كورساتك</p>
                            </div>
                        </a>
                        <a href="<?php echo e(route('student.reports')); ?>" class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl hover:bg-amber-100 transition-colors">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-900">حلل أدائك</p>
                                <p class="text-xs text-gray-500">شاهد تقاريرك التفصيلة</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mt-6 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4">آخر النشاطات</h2>
            <div class="space-y-3">
                <?php
                    $recentResults = QuizResult::where('user_id', $user->id)->latest()->take(3)->get();
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full <?php echo e($result->passed ? 'bg-emerald-500' : 'bg-red-500'); ?>"></div>
                        <span class="text-gray-600">اختبار "<?php echo e($result->quiz_title); ?>"</span>
                        <span class="font-bold <?php echo e($result->passed ? 'text-emerald-600' : 'text-red-600'); ?>"><?php echo e(number_format($result->score, 1)); ?>%</span>
                        <span class="text-gray-400 text-xs"><?php echo e($result->created_at->diffForHumans()); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-gray-400 text-sm text-center py-4">لا توجد نشاطات بعد. ابدأ بتصفح الكورسات!</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div x-show="tab === 'my-courses'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">دوراتي</h1>
            <a href="<?php echo e(route('courses')); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800">تصفح المزيد ←</a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $total = $course->lessons_count;
                    $completed = $course->lessonCompletions->count();
                    $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                ?>
                <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 group">
                    <div class="h-40 bg-gradient-to-br <?php echo e($loop->even ? 'from-amber-400 to-orange-500' : 'from-blue-500 to-indigo-600'); ?> flex items-center justify-center text-white text-2xl font-black relative">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->price == 0): ?>
                            <span class="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">مجاني</span>
                        <?php else: ?>
                            <span class="absolute top-3 left-3 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full">مدفوع</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e(mb_substr($course->title, 0, 2)); ?>

                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 mb-1"><?php echo e($course->title); ?></h3>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-400"><?php echo e($pct); ?>%</span>
                        </div>
                        <a href="<?php echo e(route('student.course-detail', $course->id)); ?>" class="block text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl font-bold text-sm transition-colors">متابعة التعلم</a>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-span-full text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <p class="mb-2">لم تسجل في أي كورس بعد</p>
                    <a href="<?php echo e(route('courses')); ?>" class="text-blue-600 font-bold hover:underline">تصفح الكورسات المتاحة</a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div x-show="tab === 'quizzes'" x-transition>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">الاختبارات</h1>
            <a href="<?php echo e(route('student.quiz.list')); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800">عرض الكل ←</a>
        </div>
        <?php
            $quizzes = \App\Models\Quiz::with('course', 'questions')
                ->where('is_published', true)
                ->where(function ($q) use ($enrolledCourseIds) {
                    $q->whereIn('course_id', $enrolledCourseIds)->orWhereNull('course_id');
                })
                ->latest()
                ->take(6)
                ->get();
        ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $qCount = $quiz->questions->count(); ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-sm"><?php echo e($quiz->title_ar ?? $quiz->title); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($qCount); ?> سؤال</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400"><?php echo e($quiz->max_attempts ? $quiz->max_attempts . ' محاولات' : 'غير محدود'); ?></span>
                        <a href="<?php echo e(route('student.quiz.show', $quiz->id)); ?>" class="text-sm font-bold text-emerald-600 hover:text-emerald-800">ابدأ ←</a>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-span-full text-center py-16 text-gray-400">
                    <p>لا توجد اختبارات متاحة حالياً.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div x-show="tab === 'reports'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">تقارير الأداء</h1>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-4">آخر 5 اختبارات</h2>
                <?php
                    $lastResults = QuizResult::where('user_id', $user->id)->latest()->take(5)->get();
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lastResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <span class="text-sm text-gray-600 truncate ml-2"><?php echo e($r->quiz_title); ?></span>
                        <span class="text-sm font-bold <?php echo e($r->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($r->score, 0)); ?>%</span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-gray-400 text-sm text-center py-8">لا توجد نتائج بعد.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-4">ملخص الأداء</h2>
                <div class="text-center py-6">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-black text-white"><?php echo e($avgScore ? number_format($avgScore, 0) : '—'); ?>%</span>
                    </div>
                    <p class="text-gray-500">متوسط النتيجة الإجمالي</p>
                </div>
            </div>
        </div>
    </div>

    
    <div x-show="tab === 'results'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-6">نتائج الاختبارات</h1>
        <?php
            $allResults = QuizResult::where('user_id', $user->id)->latest()->paginate(10);
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-right p-4 font-bold text-gray-600">الاختبار</th>
                            <th class="text-center p-4 font-bold text-gray-600">الدرجة</th>
                            <th class="text-center p-4 font-bold text-gray-600">الصحيح</th>
                            <th class="text-center p-4 font-bold text-gray-600">الوقت</th>
                            <th class="text-center p-4 font-bold text-gray-600">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-4 font-medium text-gray-900"><?php echo e($result->quiz_title); ?></td>
                                <td class="p-4 text-center font-bold <?php echo e($result->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($result->score, 1)); ?>%</td>
                                <td class="p-4 text-center text-gray-600"><?php echo e($result->correct_answers); ?>/<?php echo e($result->total_questions); ?></td>
                                <td class="p-4 text-center text-gray-500"><?php echo e($result->time_spent ? gmdate('i:s', $result->time_spent) : '—'); ?></td>
                                <td class="p-4 text-center text-gray-500 text-xs"><?php echo e($result->created_at->format('Y-m-d')); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="p-8 text-center text-gray-400">لا توجد نتائج بعد.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div x-show="tab === 'payments'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">المدفوعات</h1>
        <p class="text-gray-500 text-sm mb-6">سجل طلبات الدفع والاشتراكات</p>
        <?php
            $myPayments = PaymentRequest::where('user_id', $user->id)->latest()->take(10)->get();
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-right p-4 font-bold text-gray-600">الكورس</th>
                            <th class="text-center p-4 font-bold text-gray-600">المبلغ</th>
                            <th class="text-center p-4 font-bold text-gray-600">الحالة</th>
                            <th class="text-center p-4 font-bold text-gray-600">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-4 font-medium text-gray-900"><?php echo e(Str::limit($p->item_name ?? 'كورس', 30)); ?></td>
                                <td class="p-4 text-center font-bold text-gray-900"><?php echo e(number_format($p->amount, 0)); ?> ريال</td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        <?php if($p->status === 'approved'): ?> bg-emerald-100 text-emerald-700
                                        <?php elseif($p->status === 'rejected'): ?> bg-red-100 text-red-700
                                        <?php elseif($p->status === 'pending_manual_review'): ?> bg-amber-100 text-amber-700
                                        <?php else: ?> bg-gray-100 text-gray-600 <?php endif; ?>">
                                        <?php echo e($p->status === 'approved' ? 'مقبول' : ($p->status === 'rejected' ? 'مرفوض' : ($p->status === 'pending_manual_review' ? 'قيد المراجعة' : $p->status))); ?>

                                    </span>
                                </td>
                                <td class="p-4 text-center text-gray-500 text-xs"><?php echo e($p->created_at->format('Y-m-d')); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="p-8 text-center text-gray-400">لا توجد مدفوعات بعد.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?php echo e(route('pricing')); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors">
                اشتراك في باقة
                <span>←</span>
            </a>
        </div>
    </div>

    
    <div x-show="tab === 'plan'" x-transition x-data="studyPlan()" x-init="init()">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900">خطة الدراسة</h1>
        </div>

        
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">التقدم الأسبوعي</h2>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-3xl font-black text-blue-600" x-text="weeklyDone"></div>
                    <p class="text-xs text-gray-500">تم</p>
                </div>
                <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all" :style="`width: ${weeklyPct}%`"></div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-gray-900" x-text="tasks.length"></div>
                    <p class="text-xs text-gray-500">مجموع</p>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
            <form @submit.prevent="addTask">
                <div class="flex gap-3">
                    <input type="text" x-model="newTaskText" placeholder="أضف مهمة دراسية..." required
                           class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select x-model="newTaskDay" class="px-3 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <template x-for="(day, i) in days" :key="i">
                            <option :value="i" x-text="day"></option>
                        </template>
                    </select>
                    <button type="submit" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-colors">إضافة</button>
                </div>
            </form>
        </div>

        
        <div class="grid md:grid-cols-2 gap-4">
            <template x-for="(day, dayIdx) in days" :key="dayIdx">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span x-text="day"></span>
                        <span class="text-xs text-gray-400" x-text="`(${tasksByDay(dayIdx).length})`"></span>
                    </h3>
                    <div class="space-y-2">
                        <template x-for="(task, tIdx) in tasksByDay(dayIdx)" :key="tIdx">
                            <div class="flex items-center gap-3 p-2.5 rounded-xl transition-colors"
                                 :class="task.done ? 'bg-emerald-50' : 'hover:bg-gray-50'">
                                <button @click="toggleTask(dayIdx, tIdx)"
                                        class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                        :class="task.done ? 'bg-emerald-500 text-white' : 'border-2 border-gray-300'">
                                    <svg x-show="task.done" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <span class="flex-1 text-sm" :class="task.done ? 'text-gray-400 line-through' : 'text-gray-700'" x-text="task.text"></span>
                                <button @click="removeTask(dayIdx, tIdx)" class="text-gray-300 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <p x-show="tasksByDay(dayIdx).length === 0" class="text-gray-300 text-sm text-center py-4">لا توجد مهام</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function studyPlan() {
            return {
                days: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
                tasks: [],
                newTaskText: '',
                newTaskDay: 0,
                storageKey: 'study_plan_<?php echo e(auth()->id()); ?>',

                init() {
                    const saved = localStorage.getItem(this.storageKey);
                    if (saved) this.tasks = JSON.parse(saved);
                },
                save() {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.tasks));
                },
                tasksByDay(dayIdx) {
                    return this.tasks.filter(t => t.day === dayIdx);
                },
                get weeklyDone() {
                    return this.tasks.filter(t => t.done).length;
                },
                get weeklyPct() {
                    return this.tasks.length ? Math.round((this.weeklyDone / this.tasks.length) * 100) : 0;
                },
                addTask() {
                    if (!this.newTaskText.trim()) return;
                    this.tasks.push({ day: this.newTaskDay, text: this.newTaskText.trim(), done: false });
                    this.newTaskText = '';
                    this.save();
                },
                toggleTask(dayIdx, tIdx) {
                    const task = this.tasksByDay(dayIdx)[tIdx];
                    if (task) { task.done = !task.done; this.save(); }
                },
                removeTask(dayIdx, tIdx) {
                    const globalIdx = this.tasks.findIndex(t => t === this.tasksByDay(dayIdx)[tIdx]);
                    if (globalIdx > -1) { this.tasks.splice(globalIdx, 1); this.save(); }
                }
            };
        }
    </script>

    
    <div x-show="tab === 'favorites'" x-transition>
        <h1 class="text-2xl font-black text-gray-900 mb-2">المفضلة</h1>
        <p class="text-gray-500 text-sm mb-6">الدروس والكورسات التي حفظتها</p>
        <?php
            $favorites = \App\Models\Favorite::where('user_id', $user->id)
                ->latest()
                ->get()
                ->groupBy('favoriteable_type');
            $favCourses = isset($favorites[\App\Models\Course::class])
                ? \App\Models\Course::whereIn('id', $favorites[\App\Models\Course::class]->pluck('favoriteable_id'))->get()
                : collect();
            $favLessons = isset($favorites[\App\Models\Lesson::class])
                ? \App\Models\Lesson::whereIn('id', $favorites[\App\Models\Lesson::class]->pluck('favoriteable_id'))->get()
                : collect();
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favCourses->isEmpty() && $favLessons->isEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <p class="text-gray-500">لا توجد عناصر في المفضلة بعد.<br>اضغط على قلب ♡ في الدروس والكورسات لإضافتها.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favCourses->isNotEmpty()): ?>
                    <div>
                        <h2 class="font-bold text-gray-900 mb-3">الكورسات <?php echo e($favCourses->count()); ?></h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $favCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(route('student.course-detail', $course->id)); ?>" class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <p class="font-bold text-sm text-gray-900"><?php echo e($course->title_ar ?? $course->title); ?></p>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favLessons->isNotEmpty()): ?>
                    <div>
                        <h2 class="font-bold text-gray-900 mb-3">الدروس <?php echo e($favLessons->count()); ?></h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $favLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(route('student.lesson.show', ['course' => $lesson->course_id, 'lesson' => $lesson->id])); ?>" class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-900"><?php echo e($lesson->title_ar ?? $lesson->title); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->duration_minutes): ?>
                                            <p class="text-xs text-gray-500"><?php echo e($lesson->duration_minutes); ?> دقيقة</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>

<script>
    // Toggle mobile sidebar
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            if (e.target.closest('[onclick]')) return;
            if (sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.add('hidden');
            } else {
                overlay.classList.remove('hidden');
            }
        }
    });
    // Close sidebar on link click (mobile)
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                document.getElementById('sidebar')?.classList.add('-translate-x-full');
                document.getElementById('sidebar-overlay')?.classList.add('hidden');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => request('tab', 'overview')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\dashboard.blade.php ENDPATH**/ ?>