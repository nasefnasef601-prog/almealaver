<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'لوحة التحكم'); ?> — منصة المئة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">

    
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm" style="height: auto;">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <div class="flex items-center gap-3">
                    <button x-data @click="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-white font-black text-sm shadow-sm border-2 border-white">م</div>
                        <span class="text-lg font-black text-blue-900">منصة <span class="text-amber-500">المئة</span></span>
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    
                    <div class="relative" x-data="notifBell()" x-init="init()">
                        <button @click="toggle()" class="relative p-2.5 hover:bg-gray-100 rounded-xl transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <template x-if="unread > 0">
                                <span class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm" x-text="unread > 99 ? '99+' : unread"></span>
                            </template>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute top-full left-0 mt-2 w-80 bg-white shadow-xl rounded-xl border border-gray-100 z-50 max-h-96 overflow-y-auto"
                             @dropdown-update.window="unread = $event.detail.count">
                            <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between z-10">
                                <span class="font-bold text-gray-900 text-sm">الإشعارات</span>
                                <template x-if="unread > 0">
                                    <button @click="markAllRead" class="text-xs text-blue-600 hover:text-blue-800 font-medium">تحديد الكل مقروء</button>
                                </template>
                            </div>
                            <div id="notif-dropdown" x-html="html">
                                <div class="text-center py-8 text-gray-400 text-sm">جاري التحميل...</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-50 p-1 pr-3 rounded-full border border-transparent hover:border-gray-100 transition-all">
                            <div class="hidden sm:block text-left">
                                <span class="block text-xs text-gray-500">حسابي</span>
                                <span class="block text-sm font-bold text-gray-800 leading-none"><?php echo e(Auth::user()->name); ?></span>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm border-2 border-white">
                                <?php echo e(mb_substr(Auth::user()->name, 0, 1)); ?>

                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute top-full left-0 mt-2 w-56 bg-white shadow-xl rounded-xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="font-bold text-gray-800"><?php echo e(Auth::user()->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></p>
                            </div>
                            <?php
                                $roleHome = match(true) {
                                    Auth::user()->hasRole('admin') => '/admin',
                                    Auth::user()->hasRole('teacher') => '/teacher/dashboard',
                                    Auth::user()->hasRole('supervisor') => '/supervisor/dashboard',
                                    Auth::user()->hasRole('parent') => '/parent/dashboard',
                                    default => '/student/dashboard',
                                };
                            ?>
                            <a href="<?php echo e($roleHome); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-700 font-medium">لوحة التحكم</a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasRole('student')): ?>
                                <a href="<?php echo e(route('student.profile')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-700 font-medium">الملف الشخصي</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasRole('admin')): ?>
                                <a href="/admin" class="flex items-center gap-3 px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 font-medium">لوحة الإدارة</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">تسجيل الخروج</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1">
        <?php
            $role = Auth::user()->roles->first()?->name ?? 'student';
            $sidebarItems = match($role) {
                'teacher' => [
                    'overview' => ['label' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    'courses' => ['label' => 'كورساتي', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'students' => ['label' => 'الطلاب', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 13.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                    'quizzes' => ['label' => 'الاختبارات', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'reports' => ['label' => 'التقارير', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ],
                'supervisor' => [
                    'overview' => ['label' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    'schools' => ['label' => 'المدارس', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    'teachers' => ['label' => 'المعلمون', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    'students' => ['label' => 'الطلاب', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 13.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                    'reports' => ['label' => 'التقارير', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ],
                'parent' => [
                    'overview' => ['label' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    'children' => ['label' => 'أبنائي', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    'results' => ['label' => 'النتائج', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                    'reports' => ['label' => 'التقارير', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'payments' => ['label' => 'المدفوعات', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ],
                default => [
                    'overview' => ['label' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    'my-courses' => ['label' => 'دوراتي', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'quizzes' => ['label' => 'الاختبارات', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'mock-exams' => ['label' => 'المحاكيات', 'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z', 'route' => 'student.mock-exams'],
                    'reports' => ['label' => 'التقارير', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'skills' => ['label' => 'المهارات', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'route' => 'student.skills'],
                    'results' => ['label' => 'النتائج', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'route' => 'student.results'],
                    'payments' => ['label' => 'المدفوعات', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    'plan' => ['label' => 'خطة الدراسة', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    'favorites' => ['label' => 'المفضلة', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    'leaderboard' => ['label' => 'المتصدرين', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'route' => 'student.leaderboard'],
                ],
            };
            $sidebarRoute = match($role) {
                'teacher' => 'teacher.dashboard',
                'supervisor' => 'supervisor.dashboard',
                'parent' => 'parent.dashboard',
                default => 'student.dashboard',
            };
        ?>
        <aside id="sidebar" class="fixed lg:static inset-y-0 right-0 z-30 w-64 bg-white border-l border-gray-100 shadow-sm pt-16 sm:pt-20 lg:pt-0 transform transition-transform duration-300 -translate-x-full lg:translate-x-0 overflow-y-auto">
            <nav class="p-4 space-y-1 mt-4">
                <?php
                    $currentRoute = request()->route()?->getName();
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sidebarItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $itemRoute = $item['route'] ?? null;
                        $isActive = $itemRoute
                            ? $currentRoute === $itemRoute
                            : ($activeTab ?? 'overview') === $key;
                    ?>
                    <a href="<?php echo e($itemRoute ?? (route($sidebarRoute) . '?tab=' . $key)); ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors
                       <?php if($isActive): ?>
                            bg-amber-50 text-amber-600 shadow-sm
                        <?php else: ?>
                            text-gray-600 hover:bg-amber-50 hover:text-amber-600
                        <?php endif; ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($item['icon']); ?>"/>
                        </svg>
                        <?php echo e($item['label']); ?>

                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </nav>
        </aside>

        
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-20 hidden lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

        
        <main class="flex-1 min-h-screen p-4 sm:p-6 lg:p-8">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script>
        function notifBell() {
            return {
                open: false,
                unread: 0,
                html: '',

                init() {
                    this.fetchUnread();
                    if (this.unread === 0) this.fetchDropdown();
                    setInterval(() => this.fetchUnread(), 30000);
                },

                toggle() {
                    this.open = !this.open;
                    if (this.open) this.fetchDropdown();
                },

                fetchUnread() {
                    fetch('<?php echo e(route('student.notifications.unread')); ?>', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json()).then(d => {
                        if (d.count !== undefined) this.unread = d.count;
                    }).catch(() => {});
                },

                fetchDropdown() {
                    fetch('<?php echo e(route('student.notifications.dropdown')); ?>', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json()).then(d => {
                        if (d.html) this.html = d.html;
                        if (d.unread_count !== undefined) this.unread = d.unread_count;
                    }).catch(() => {});
                },

                markAllRead() {
                    fetch('<?php echo e(route('student.notifications.read-all')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        }
                    }).then(r => r.json()).then(() => {
                        this.unread = 0;
                        this.fetchDropdown();
                        window.dispatchEvent(new CustomEvent('dropdown-update', { detail: { count: 0 } }));
                    }).catch(() => {});
                }
            };
        }
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views/layouts/student.blade.php ENDPATH**/ ?>