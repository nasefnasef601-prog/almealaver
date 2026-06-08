<?php
    use Illuminate\Support\Facades\Storage;
    $receiptUrl = $getRecord()->bank_transfer_receipt
        ? Storage::disk('public')->url($getRecord()->bank_transfer_receipt)
        : null;
?>
<div class="flex items-center gap-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receiptUrl): ?>
    <a href="<?php echo e($receiptUrl); ?>" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-colors text-sm font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        عرض الإيصال
    </a>
        <img src="<?php echo e($receiptUrl); ?>" alt="Receipt"
             class="max-w-xs rounded-xl border border-gray-200 shadow-sm">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views\filament\forms\components\receipt-image.blade.php ENDPATH**/ ?>