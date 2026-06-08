<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'منصة المئة'); ?> — منصة المئة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">

    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm"
            x-data="headerApp()">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20 gap-3">

                
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <button class="md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg" @click="mobileMenu = !mobileMenu">
                        <svg x-show="!mobileMenu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        <svg x-show="mobileMenu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2 min-w-0">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-white font-black text-sm sm:text-lg shadow-sm border-2 border-white">
                            <?php echo e(substr(config('app.name', 'م'), 0, 1)); ?>

                        </div>
                        <div class="text-lg sm:text-2xl font-black whitespace-nowrap">
                            <span class="text-blue-900">منصة</span>
                            <span class="text-amber-500 mx-1">المئة</span>
                            <span class="hidden sm:inline text-xs font-normal text-gray-400 -mt-2 align-top">قدرات & تحصيلي</span>
                        </div>
                    </a>
                </div>

                
                <nav class="hidden md:flex items-center gap-1">
                    <a href="<?php echo e(url('/')); ?>" class="px-3 py-2 text-sm font-bold text-gray-600 hover:text-amber-500 rounded-lg hover:bg-amber-50 transition-colors <?php echo e(request()->is('/') ? 'text-amber-500 bg-amber-50' : ''); ?>">الرئيسية</a>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $navPath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div x-data="{ open: false }"
                             @mouseenter="open = true"
                             @mouseleave="open = false"
                             class="relative">
                            <a href="<?php echo e(route('category', $navPath->id)); ?>"
                               class="flex items-center gap-1 px-3 py-2 text-sm font-bold text-gray-600 hover:text-amber-500 rounded-lg hover:bg-amber-50 transition-colors">
                                <?php echo e($navPath->name_ar); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($navPath->subjects->isNotEmpty()): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($navPath->subjects->isNotEmpty()): ?>
                                <div x-show="open"
                                     x-transition
                                     @click.away="open = false"
                                     class="absolute top-full right-0 w-72 bg-white shadow-xl rounded-b-xl border-t-2 border-amber-500 py-2 z-50">
                                    <div class="px-4 py-2 text-xs font-bold text-gray-400 border-b border-gray-100 mb-1">
                                        <?php echo e($navPath->name_ar); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navPath->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <a href="<?php echo e(route('category.subject', [$navPath->id, $subject->id])); ?>"
                                           class="block px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-700 font-medium transition-colors">
                                            <?php echo e($subject->name_ar); ?>

                                        </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="border-t border-gray-100 mt-1 pt-1">
                                        <a href="<?php echo e(route('category', $navPath->id)); ?>"
                                           class="block px-4 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 transition-colors">
                                            صفحة <?php echo e($navPath->name_ar); ?>

                                        </a>
                                        <a href="<?php echo e(route('category', $navPath->id)); ?>?tab=mock-exams"
                                           class="block px-4 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            اختبارات محاكية <?php echo e($navPath->name_ar); ?>

                                        </a>
                                        <a href="<?php echo e(route('pricing')); ?>"
                                           class="block px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            عروض وباقات <?php echo e($navPath->name_ar); ?>

                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <a href="<?php echo e(route('courses')); ?>"
                       class="px-3 py-2 text-sm font-bold text-gray-600 hover:text-amber-500 rounded-lg hover:bg-amber-50 transition-colors <?php echo e(request()->is('courses') ? 'text-amber-500 bg-amber-50' : ''); ?>">الكورسات</a>
                    <a href="<?php echo e(route('pricing')); ?>"
                       class="px-3 py-2 text-sm font-bold text-gray-600 hover:text-amber-500 rounded-lg hover:bg-amber-50 transition-colors <?php echo e(request()->is('pricing') ? 'text-amber-500 bg-amber-50' : ''); ?>">الباقات</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasRole('admin')): ?>
                            <a href="/admin" class="px-3 py-2 text-sm font-bold text-gray-600 hover:text-amber-500 rounded-lg hover:bg-amber-50 transition-colors">لوحة الإدارة</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </nav>

                
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">

                    
                    <button @click="searchOpen = true; $nextTick(() => { if ($refs.searchInput) $refs.searchInput.focus(); })" class="text-gray-500 hover:text-amber-500 transition-colors p-1" title="بحث (Ctrl+K)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>

                    
                    <a href="#" class="text-gray-500 hover:text-amber-500 transition-colors relative p-1" title="السلة">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span x-show="cartCount > 0"
                              x-text="cartCount"
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center"></span>
                    </a>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 hover:bg-gray-50 p-1 sm:pr-3 rounded-full border border-transparent hover:border-gray-100 transition-all">
                                <div class="hidden lg:block text-left">
                                    <span class="block text-xs text-gray-500 font-normal">حسابي</span>
                                    <span class="block text-sm font-bold text-gray-800 leading-none"><?php echo e(Auth::user()->name); ?></span>
                                </div>
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm border-2 border-white">
                                    <?php echo e(mb_substr(Auth::user()->name, 0, 1)); ?>

                                </div>
                            </button>
                            <div x-show="open" x-transition @click.away="open = false" class="absolute top-full left-0 mt-2 w-64 bg-white shadow-xl rounded-xl border border-gray-100 py-2 z-50">
                                <div class="px-4 py-3 border-b border-gray-100 mb-2">
                                    <p class="font-bold text-gray-800"><?php echo e(Auth::user()->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></p>
                                </div>
                                <?php
                                    $dashboardRoute = match(true) {
                                        Auth::user()->hasRole('admin') => '/admin',
                                        Auth::user()->hasRole('teacher') => '/teacher/dashboard',
                                        Auth::user()->hasRole('supervisor') => '/supervisor/dashboard',
                                        Auth::user()->hasRole('parent') => '/parent/dashboard',
                                        default => '/student/dashboard',
                                    };
                                ?>
                                <a href="<?php echo e($dashboardRoute); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-colors font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                    لوحة التحكم
                                </a>
                                <a href="<?php echo e(route('student.courses')); ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition-colors font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    دوراتي
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasRole('admin')): ?>
                                    <a href="/admin" class="flex items-center gap-3 px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        لوحة الإدارة
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <button @click="loginModal = true" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-3 sm:px-4 py-2 rounded-lg font-bold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            <span class="hidden sm:inline">تسجيل الدخول</span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div x-show="loginModal" x-transition.opacity class="fixed inset-0 z-[100] bg-black/50 flex items-center justify-center p-4"
             @keydown.escape.window="loginModal = false">
            <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-xl" @click.away="loginModal = false">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-black text-gray-800" x-text="isSignUp ? 'إنشاء حساب جديد' : 'تسجيل الدخول'"></h2>
                        <button @click="loginModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div x-show="authError" x-transition x-text="authError" class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200"></div>
                    <div x-show="authSuccess" x-transition x-text="authSuccess" class="mb-4 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl border border-emerald-200"></div>
                    <form @submit.prevent="handleEmailAuth" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">البريد الإلكتروني</label>
                            <input type="email" x-model="email" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-left transition-shadow" dir="ltr" placeholder="user@example.com">
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <label class="block text-sm font-bold text-gray-700">كلمة المرور</label>
                                <template x-if="!isSignUp">
                                    <a href="<?php echo e(route('password.request')); ?>" @click="loginModal = false" class="text-xs font-bold text-emerald-600 hover:underline">نسيت كلمة المرور؟</a>
                                </template>
                            </div>
                            <div class="relative">
                                <input x-bind:type="showPassword ? 'text' : 'password'" x-model="password" required x-bind:minlength="isSignUp ? 8 : undefined" class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-left transition-shadow" dir="ltr" placeholder="********">
                                <button type="button" @click="showPassword = !showPassword" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" :disabled="isLoading" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="isLoading" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg>
                            <span x-text="isLoading ? '...' : (isSignUp ? 'إنشاء حساب' : 'دخول')"></span>
                        </button>
                    </form>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-sm text-gray-400">أو</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>
                    <button type="button" class="mt-4 w-full flex items-center justify-center gap-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 rounded-xl transition-colors">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        تسجيل بجوجل
                    </button>
                    <div class="mt-6 text-center">
                        <button @click="isSignUp = !isSignUp; authError = ''; authSuccess = ''" type="button" class="text-sm text-emerald-600 hover:underline font-bold" x-text="isSignUp ? 'لديك حساب بالفعل؟ تسجيل الدخول' : 'ليس لديك حساب؟ إنشاء حساب جديد'"></button>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="searchOpen" x-transition class="fixed inset-0 z-[120] bg-black/50 p-4 flex items-start justify-center"
             @keydown.escape.window="searchOpen = false">
            <div class="mt-16 w-full max-w-3xl rounded-2xl border border-gray-100 bg-white shadow-2xl" @click.away="searchOpen = false">
                <div class="flex items-center gap-2 border-b border-gray-100 p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" x-model="searchQuery" x-ref="searchInput"
                           placeholder="ابحث عن درس أو سؤال أو دورة..."
                           class="flex-1 bg-transparent text-sm outline-none">
                    <button @click="searchOpen = false" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="max-h-[60vh] overflow-auto p-4">
                    <template x-if="searchQuery.length > 0 && searchQuery.length < 2">
                        <p class="text-sm text-gray-500">اكتب حرفين على الأقل.</p>
                    </template>
                    <template x-if="searchQuery.length >= 2 && !searchLoading">
                        <p class="text-sm text-gray-500">لا توجد نتائج مطابقة الآن.</p>
                    </template>
                    <template x-if="searchLoading">
                        <p class="text-sm text-gray-500">جاري البحث...</p>
                    </template>
                </div>
            </div>
        </div>

        
        <div x-show="mobileMenu" x-transition class="md:hidden fixed inset-0 z-40 bg-white overflow-y-auto pb-20">
            <div class="p-4 pt-20">
                <div class="space-y-1">
                    <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-amber-50 rounded-xl font-bold transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        الرئيسية
                    </a>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $navPath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3 text-gray-700 hover:bg-amber-50 rounded-xl font-bold transition-colors">
                                <span class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    <?php echo e($navPath->name_ar); ?>

                                </span>
                                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="mr-6">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navPath->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="/courses?path=<?php echo e($navPath->slug); ?>&subject=<?php echo e($subject->slug); ?>"
                                       class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-amber-50 rounded-xl font-medium transition-colors">
                                        <?php echo e($subject->name_ar); ?>

                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <a href="<?php echo e(route('courses')); ?>" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-amber-50 rounded-xl font-bold transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        جميع الكورسات
                    </a>
                    <a href="<?php echo e(route('pricing')); ?>" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-amber-50 rounded-xl font-bold transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        الباقات
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('student.dashboard')); ?>" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-amber-50 rounded-xl font-bold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            لوحة التحكم
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="bg-white border-t border-gray-200 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
            &copy; <?php echo e(date('Y')); ?> منصة المئة. جميع الحقوق محفوظة.
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        function headerApp() {
            return {
                loginModal: false,
                isSignUp: false,
                email: '',
                password: '',
                showPassword: false,
                authError: '',
                authSuccess: '',
                mobileMenu: false,
                searchOpen: false,
                searchQuery: '',
                searchLoading: false,
                cartCount: 0,
                isLoading: false,

                init() {
                    this.$watch('searchOpen', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (this.$refs.searchInput) this.$refs.searchInput.focus();
                            });
                        }
                    });
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                            e.preventDefault();
                            this.searchOpen = true;
                        }
                    });
                },

                handleEmailAuth() {
                    this.authError = '';
                    this.authSuccess = '';
                    this.isLoading = true;

                    if (this.isSignUp && this.password.length < 8) {
                        this.authError = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.';
                        this.isLoading = false;
                        return;
                    }

                    const url = this.isSignUp ? '<?php echo e(route("register")); ?>' : '<?php echo e(route("login")); ?>';
                    const data = { email: this.email, password: this.password };

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                    .then(r => r.json())
                    .then(res => {
                        this.isLoading = false;
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else if (res.error) {
                            this.authError = res.error;
                        } else {
                            this.authError = 'حدث خطأ غير متوقع.';
                        }
                    })
                    .catch(err => {
                        this.isLoading = false;
                        this.authError = 'حدث خطأ أثناء تسجيل الدخول.';
                    });
                }
            }
        }
    </script>
</body>
</html>
<?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\layouts\app.blade.php ENDPATH**/ ?>