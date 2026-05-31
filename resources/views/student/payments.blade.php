@extends('layouts.student', ['activeTab' => 'payments'])

@section('title', 'المدفوعات')

@php
    use App\Models\PaymentRequest;
    use App\Models\AccessGrant;
    $payments = PaymentRequest::where('user_id', Auth::id())->latest()->paginate(15);
    $activeGrants = AccessGrant::where('user_id', Auth::id())
        ->where('status', 'active')
        ->with('course')
        ->latest('expires_at')
        ->get();
@endphp

@section('content')
<div class="max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">المدفوعات</h1>
            <p class="text-gray-500 text-sm">سجل طلبات الدفع والاشتراكات</p>
        </div>
        <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-colors">
            شراء باقة
            <span>←</span>
        </a>
    </div>

    {{-- Active Subscriptions --}}
    @if($activeGrants->isNotEmpty())
    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">الاشتراكات النشطة</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeGrants as $grant)
                @php
                    $now = now();
                    $expires = $grant->expires_at;
                    $daysLeft = $expires ? $now->diffInDays($expires, false) : null;
                    $isExpiring = $daysLeft !== null && $daysLeft <= 7 && $daysLeft >= 0;
                    $isExpired = $daysLeft !== null && $daysLeft < 0;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 {{ $isExpiring ? 'border-amber-200 bg-amber-50/30' : '' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-bold text-gray-900">{{ $grant->course->title_ar ?? $grant->course->title ?? 'كورس' }}</p>
                            <p class="text-xs text-gray-500">اشتراك {{ $grant->grant_type === 'purchase' ? 'مباشر' : 'باقة' }}</p>
                        </div>
                        @if($isExpired)
                            <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 font-bold">انتهى</span>
                        @elseif($isExpiring)
                            <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700 font-bold">ينتهي قريباً</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold">نشط</span>
                        @endif
                    </div>
                    @if($expires)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">تاريخ الانتهاء:</span>
                            <span class="font-bold {{ $isExpiring ? 'text-amber-700' : ($isExpired ? 'text-red-600' : 'text-gray-900') }}">
                                {{ $expires->format('Y/m/d') }}
                            </span>
                        </div>
                        @if($daysLeft !== null && $daysLeft >= 0)
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>المتبقي {{ $daysLeft }} يوم</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    @php $pct = min(100, ($now->diffInDays($expires) / max(365, $now->diffInDays($expires) + 1)) * 100); @endphp
                                    <div class="h-full rounded-full {{ $isExpiring ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ 100 - $pct }}%"></div>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-emerald-600 font-bold">دائم</p>
                    @endif
                    <a href="{{ route('student.course-detail', $grant->course_id) }}" class="mt-3 inline-block text-xs text-blue-600 hover:text-blue-800 font-bold">عرض الكورس ←</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-right p-4 font-bold text-gray-600">#</th>
                        <th class="text-right p-4 font-bold text-gray-600">العنصر</th>
                        <th class="text-center p-4 font-bold text-gray-600">المبلغ</th>
                        <th class="text-center p-4 font-bold text-gray-600">طريقة الدفع</th>
                        <th class="text-center p-4 font-bold text-gray-600">الحالة</th>
                        <th class="text-center p-4 font-bold text-gray-600">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-gray-400">{{ $p->id }}</td>
                            <td class="p-4 font-medium text-gray-900">{{ Str::limit($p->item_name ?? 'كورس', 35) }}</td>
                            <td class="p-4 text-center font-bold text-gray-900">{{ number_format($p->amount, 0) }} ريال</td>
                            <td class="p-4 text-center text-gray-600">{{ $p->payment_method === 'bank_transfer' ? 'تحويل بنكي' : ($p->payment_method ?? '—') }}</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($p->status === 'approved') bg-emerald-100 text-emerald-700
                                    @elseif($p->status === 'rejected') bg-red-100 text-red-700
                                    @elseif(in_array($p->status, ['pending', 'pending_manual_review'])) bg-amber-100 text-amber-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    @switch($p->status)
                                        @case('approved') مقبول ✓ @break
                                        @case('rejected') مرفوض ✗ @break
                                        @case('pending') قيد المراجعة @break
                                        @case('pending_manual_review') بانتظار المراجعة @break
                                        @default {{ $p->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="p-4 text-center text-gray-500 text-xs">{{ $p->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-gray-500">لا توجد مدفوعات بعد</p>
                                <p class="text-gray-400 text-sm mt-1">اشترك في باقة للوصول لكل المحتوى</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($payments->hasPages())
        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
