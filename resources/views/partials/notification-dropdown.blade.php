@php use App\Models\Notification; @endphp
@forelse($notifications as $n)
    <a href="{{ route('student.notifications') }}?read={{ $n->id }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors {{ is_null($n->read_at) ? 'bg-blue-50/50' : '' }}">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
            @if($n->type === 'payment') bg-emerald-100
            @elseif($n->type === 'quiz') bg-blue-100
            @elseif($n->type === 'course') bg-amber-100
            @else bg-gray-100 @endif">
            <svg class="w-4 h-4
                @if($n->type === 'payment') text-emerald-600
                @elseif($n->type === 'quiz') text-blue-600
                @elseif($n->type === 'course') text-amber-600
                @else text-gray-600 @endif"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($n->type === 'payment')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                @elseif($n->type === 'quiz' || $n->type === 'course')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                @endif
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 leading-tight">{{ $n->title_ar ?? $n->title }}</p>
            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $n->body_ar ?? $n->body }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
        </div>
        @if(is_null($n->read_at))
            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0 mt-2"></span>
        @endif
    </a>
@empty
    <div class="text-center py-8 text-gray-400 text-sm">
        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p>لا توجد إشعارات</p>
    </div>
@endforelse
@if($notifications->count() > 0)
    <div class="border-t border-gray-100">
        <a href="{{ route('student.notifications') }}" class="block text-center text-sm font-bold text-blue-600 py-3 hover:bg-blue-50 transition-colors">عرض كل الإشعارات</a>
    </div>
@endif
