@forelse($notifications as $n)
    <div class="flex items-start gap-4 p-4 rounded-xl transition-colors {{ is_null($n->read_at) ? 'bg-blue-50/60 border border-blue-100' : 'hover:bg-gray-50' }}" x-data="{ open: false }">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
            @if($n->type === 'payment') bg-emerald-100
            @elseif($n->type === 'quiz') bg-blue-100
            @elseif($n->type === 'course') bg-amber-100
            @else bg-gray-100 @endif">
            <svg class="w-5 h-5
                @if($n->type === 'payment') text-emerald-600
                @elseif($n->type === 'quiz') text-blue-600
                @elseif($n->type === 'course') text-amber-600
                @else text-gray-600 @endif"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($n->type === 'payment')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                @elseif($n->type === 'quiz')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                @endif
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold text-gray-900 text-sm">{{ $n->title_ar ?? $n->title }}</p>
                    <p class="text-gray-600 text-sm mt-1">{{ $n->body_ar ?? $n->body }}</p>
                </div>
                @if(is_null($n->read_at))
                    <button @click="$wire?.markRead?.($n->id) ?? fetch('{{ route('student.notifications.read', $n->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}}).then(r => r.json()).then(() => { open = false; $el.closest('.flex')?.classList.remove('bg-blue-50/60','border-blue-100'); $el.remove() })" class="text-xs text-blue-600 hover:text-blue-800 font-medium shrink-0">مقروء</button>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $n->created_at->format('Y-m-d H:i') }}</p>
            @if($n->data && isset($n->data['amount']))
                <p class="text-xs font-bold text-emerald-600 mt-1">{{ number_format($n->data['amount'], 0) }} ريال</p>
            @endif
        </div>
    </div>
@empty
    <div class="text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p class="text-lg font-bold mb-1">لا توجد إشعارات</p>
        <p class="text-sm">عندما يصلك إشعار، سيظهر هنا</p>
    </div>
@endforelse

@if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
@endif
