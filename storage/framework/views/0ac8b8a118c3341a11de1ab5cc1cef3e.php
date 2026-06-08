<?php $__env->startSection('title', 'الإشعارات'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="notifManager()" x-init="init()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">الإشعارات</h1>
            <p class="text-gray-500 text-sm mt-1">آخر التحديثات والإشعارات</p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
            <button @click="markAllRead" class="text-sm font-bold text-blue-600 hover:text-blue-800 px-4 py-2 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                تحديد الكل كمقروء (<?php echo e($unreadCount); ?>)
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div id="notif-list" class="divide-y divide-gray-50">
            <?php echo $__env->make('partials.notification-list', ['notifications' => $notifications], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <script>
        function notifManager() {
            return {
                init() {},
                markAllRead() {
                    fetch('<?php echo e(route('student.notifications.read-all')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        }
                    }).then(r => r.json()).then(() => {
                        document.querySelectorAll('#notif-list .bg-blue-50\\/60').forEach(el => {
                            el.classList.remove('bg-blue-50/60', 'border-blue-100');
                        });
                        document.querySelectorAll('#notif-list [onclick*="markRead"]').forEach(el => el.remove());
                        document.querySelector('[x-data="notifManager()"] .bg-blue-50.rounded-xl')?.remove();
                    });
                }
            };
        }
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', ['activeTab' => 'notifications'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\student\notifications.blade.php ENDPATH**/ ?>