@extends('layouts.student', ['activeTab' => 'notifications'])

@section('title', 'الإشعارات')

@section('content')
<div x-data="notifManager()" x-init="init()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">الإشعارات</h1>
            <p class="text-gray-500 text-sm mt-1">آخر التحديثات والإشعارات</p>
        </div>
        @if($unreadCount > 0)
            <button @click="markAllRead" class="text-sm font-bold text-blue-600 hover:text-blue-800 px-4 py-2 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                تحديد الكل كمقروء ({{ $unreadCount }})
            </button>
        @endif
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div id="notif-list" class="divide-y divide-gray-50">
            @include('partials.notification-list', ['notifications' => $notifications])
        </div>
    </div>

    <script>
        function notifManager() {
            return {
                init() {},
                markAllRead() {
                    fetch('{{ route('student.notifications.read-all') }}', {
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
@endsection
