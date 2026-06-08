<?php $__env->startSection('title', 'تسجيل الدخول'); ?>

<?php $__env->startSection('content'); ?>
<main class="min-h-[calc(100vh-8rem)] bg-gray-50 px-4 py-10" dir="rtl">
    <section class="mx-auto w-full max-w-md">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="mb-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-white font-black text-2xl shadow-sm border-2 border-white">م</div>
                <h1 class="text-2xl font-black text-gray-900">تسجيل الدخول</h1>
                <p class="mt-1 text-sm text-gray-500">مرحباً بعودتك! أدخل بياناتك للمتابعة.</p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <p><?php echo e($error); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div class="mb-4 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl border border-emerald-200">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-left transition-shadow"
                        dir="ltr" placeholder="user@example.com">
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <label class="block text-sm font-bold text-gray-700">كلمة المرور</label>
                        <a href="<?php echo e(route('password.request')); ?>" class="text-xs font-bold text-emerald-600 hover:underline">
                            نسيت كلمة المرور؟
                        </a>
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <input x-bind:type="show ? 'text' : 'password'" name="password" required
                            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-left transition-shadow"
                            dir="ltr" placeholder="********">
                        <button type="button" @click="show = !show" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <label for="remember" class="text-sm text-gray-600">تذكرني</label>
                </div>

                <button type="submit"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-colors">
                    دخول
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
                <a href="<?php echo e(route('register')); ?>" class="text-sm text-emerald-600 hover:underline font-bold">
                    ليس لديك حساب؟ إنشاء حساب جديد
                </a>
            </div>
        </div>
    </section>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\auth\login.blade.php ENDPATH**/ ?>