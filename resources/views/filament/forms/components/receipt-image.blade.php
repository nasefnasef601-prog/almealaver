<div class="flex items-center gap-3">
    <a href="{{ $getRecord()->bank_transfer_receipt }}" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-colors text-sm font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        عرض الإيصال
    </a>
    @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $getRecord()->bank_transfer_receipt))
        <img src="{{ $getRecord()->bank_transfer_receipt }}" alt="Receipt"
             class="max-w-xs rounded-xl border border-gray-200 shadow-sm">
    @endif
</div>
