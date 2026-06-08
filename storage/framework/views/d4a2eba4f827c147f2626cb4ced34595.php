<?php
    $tab = request('tab', 'overview');
?>


<?php $__env->startSection('title', 'لوحة المعلم'); ?>

<?php
    use App\Models\Course;
    use App\Models\User;
    use App\Models\LessonCompletion;
    $userId = Auth::id();
    $teacherCourseIds = Course::where('assigned_teacher_id', $userId)->pluck('id');
    $teacherCourseCount = $teacherCourseIds->count();

    // Stats for overview
    $totalStudents = User::role('student')->count();
    $enrolledStudents = \DB::table('access_grants')
        ->whereIn('course_id', $teacherCourseIds)
        ->distinct('user_id')
        ->count('user_id');
    $activeQuizzes = \App\Models\Quiz::whereIn('course_id', $teacherCourseIds)->count();

    // Courses list
    $myCourses = Course::with('creator')
        ->where('assigned_teacher_id', $userId)
        ->latest()
        ->get();

    // Students list
    $myStudentIds = \DB::table('access_grants')
        ->whereIn('course_id', $teacherCourseIds)
        ->distinct('user_id')
        ->pluck('user_id');
    $myStudents = User::whereIn('id', $myStudentIds)->get();

    // Recent results for teacher's students
    $recentResults = \App\Models\QuizResult::whereIn('user_id', $myStudentIds)
        ->whereIn('quiz_id', function ($q) use ($userId) {
            $q->select('id')->from('quizzes')->whereIn('course_id', function ($sq) use ($userId) {
                $sq->select('id')->from('courses')->where('assigned_teacher_id', $userId);
            });
        })
        ->with('user', 'quiz')
        ->latest()
        ->take(10)
        ->get();
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl">

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'overview'): ?>
    <h1 class="text-2xl font-black text-gray-900 mb-2">لوحة المعلم</h1>
    <p class="text-gray-500 text-sm mb-6">مرحباً بعودتك!</p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl p-6 shadow-lg">
            <p class="text-blue-100 text-sm font-medium">الكورسات</p>
            <p class="text-3xl font-black mt-1"><?php echo e($teacherCourseCount); ?></p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl p-6 shadow-lg">
            <p class="text-emerald-100 text-sm font-medium">الطلاب المسجلين</p>
            <p class="text-3xl font-black mt-1"><?php echo e($enrolledStudents); ?></p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-6 shadow-lg">
            <p class="text-amber-100 text-sm font-medium">إجمالي الطلاب</p>
            <p class="text-3xl font-black mt-1"><?php echo e($totalStudents); ?></p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-2xl p-6 shadow-lg">
            <p class="text-purple-100 text-sm font-medium">الاختبارات</p>
            <p class="text-3xl font-black mt-1"><?php echo e($activeQuizzes); ?></p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">آخر الكورسات</h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myCourses->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                    <span class="font-medium text-gray-900 text-sm"><?php echo e($course->title); ?></span>
                    <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($course->is_published ? 'منشور' : 'مسودة'); ?></span>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="text-gray-400 text-sm text-center py-6">لا توجد كورسات مخصصة لك بعد.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">إجراءات سريعة</h2>
            <div class="space-y-3">
                <a href="/admin/courses/create" class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">إنشاء كورس جديد</p>
                        <p class="text-xs text-gray-500">أضف محتوى تعليمي جديد</p>
                    </div>
                </a>
                <a href="/admin/quizzes" class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">إدارة الاختبارات</p>
                        <p class="text-xs text-gray-500">أنشئ وأدر الأسئلة</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentResults->isNotEmpty()): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">آخر نتائج الطلاب</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-right p-3 font-bold text-gray-600">الطالب</th>
                        <th class="text-right p-3 font-bold text-gray-600">الاختبار</th>
                        <th class="text-center p-3 font-bold text-gray-600">النتيجة</th>
                        <th class="text-center p-3 font-bold text-gray-600">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-900"><?php echo e($r->user->name); ?></td>
                            <td class="p-3 text-gray-500"><?php echo e($r->quiz->title_ar ?? $r->quiz->title); ?></td>
                            <td class="p-3 text-center">
                                <span class="font-bold <?php echo e($r->passed ? 'text-emerald-600' : 'text-red-500'); ?>"><?php echo e(number_format($r->score_percentage, 0)); ?>%</span>
                            </td>
                            <td class="p-3 text-center text-xs text-gray-500"><?php echo e($r->created_at->format('Y-m-d')); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php elseif($tab === 'courses'): ?>
    <h1 class="text-2xl font-black text-gray-900 mb-6">كورساتي</h1>
    <div class="grid gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($course->thumbnail): ?>
                        <img src="<?php echo e($course->thumbnail); ?>" class="w-16 h-16 rounded-2xl object-cover">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo e($course->title_ar ?? $course->title); ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?php echo e($course->modules_count ?? $course->modules()->count()); ?> وحدة</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($course->is_published ? 'منشور' : 'مسودة'); ?></span>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-gray-500">لا توجد كورسات</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

<?php elseif($tab === 'students'): ?>
    <h1 class="text-2xl font-black text-gray-900 mb-6">الطلاب</h1>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-right p-4 font-bold text-gray-600">الاسم</th>
                    <th class="text-right p-4 font-bold text-gray-600">البريد</th>
                    <th class="text-center p-4 font-bold text-gray-600">آخر اختبار</th>
                    <th class="text-center p-4 font-bold text-gray-600">متوسط النتيجة</th>
                    <th class="text-center p-4 font-bold text-gray-600">التسجيل</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $stdResults = \App\Models\QuizResult::where('user_id', $student->id)
                            ->whereIn('quiz_id', function ($q) use ($teacherCourseIds) {
                                $q->select('id')->from('quizzes')->whereIn('course_id', $teacherCourseIds);
                            });
                        $avgStdScore = (clone $stdResults)->avg('score_percentage');
                        $lastStdResult = (clone $stdResults)->latest()->first();
                    ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="p-4 font-medium text-gray-900"><?php echo e($student->name); ?></td>
                        <td class="p-4 text-gray-500 text-xs"><?php echo e($student->email); ?></td>
                        <td class="p-4 text-center text-xs text-gray-500">
                            <?php echo e($lastStdResult ? number_format($lastStdResult->score_percentage, 0) . '%' : '—'); ?>

                        </td>
                        <td class="p-4 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avgStdScore !== null): ?>
                                <span class="font-bold <?php echo e($avgStdScore >= 70 ? 'text-emerald-600' : ($avgStdScore >= 50 ? 'text-amber-600' : 'text-red-500')); ?>">
                                    <?php echo e(number_format($avgStdScore, 0)); ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="p-4 text-center text-xs text-gray-500"><?php echo e($student->created_at->format('Y-m-d')); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">لا يوجد طلاب مسجلين في كورساتك</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif($tab === 'quizzes'): ?>
    <h1 class="text-2xl font-black text-gray-900 mb-6">الاختبارات</h1>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-gray-500 mb-2">إدارة الاختبارات</p>
        <p class="text-gray-400 text-sm mb-4">قم بإدارة اختبارات كورساتك من لوحة الإدارة</p>
        <a href="/admin/quizzes" class="inline-flex px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700">الذهاب لإدارة الاختبارات</a>
    </div>

<?php elseif($tab === 'reports'): ?>
    <h1 class="text-2xl font-black text-gray-900 mb-6">التقارير</h1>
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-3xl font-black text-blue-600"><?php echo e($teacherCourseCount); ?></p>
            <p class="text-sm text-gray-500 mt-1">الكورسات</p>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-3xl font-black text-emerald-600"><?php echo e($enrolledStudents); ?></p>
            <p class="text-sm text-gray-500 mt-1">الطلاب</p>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-3xl font-black text-amber-600"><?php echo e($activeQuizzes); ?></p>
            <p class="text-sm text-gray-500 mt-1">الاختبارات</p>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => $tab], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\teacher\dashboard.blade.php ENDPATH**/ ?>