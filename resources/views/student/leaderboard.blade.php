@extends('layouts.student', ['activeTab' => 'overview'])

@section('title', 'لوحة المتصدرين')

@section('content')
@php
    use App\Models\QuizResult;
    use App\Models\User;

    $userId = Auth::id();
    $minResults = 3;

    $leaderboard = QuizResult::selectRaw('user_id, AVG(score_percentage) as avg_score, COUNT(*) as total_tests, SUM(passed) as passed_tests')
        ->groupBy('user_id')
        ->havingRaw('COUNT(*) >= ?', [$minResults])
        ->orderByDesc('avg_score')
        ->limit(50)
        ->get();

    $userIds = $leaderboard->pluck('user_id');
    $users = User::whereIn('id', $userIds)->get()->keyBy('id');

    $userRank = $leaderboard->search(fn($r) => $r->user_id === $userId);
    $userTotal = QuizResult::where('user_id', $userId)->count();
    $userAvg = QuizResult::where('user_id', $userId)->avg('score_percentage');
@endphp

<div class="max-w-5xl">
    <h1 class="text-2xl font-black text-gray-900 mb-2">لوحة المتصدرين</h1>
    <p class="text-gray-500 text-sm mb-8">تصنيف الطلاب حسب متوسط نتائج الاختبارات (بحد أدنى {{ $minResults }} اختبارات)</p>

    @if($userTotal > 0 && $userTotal < $minResults)
        <div class="mb-6 p-4 bg-amber-50 text-amber-700 rounded-2xl border border-amber-200 text-sm font-medium">
            أنت بحاجة إلى {{ $minResults - $userTotal }} اختبارات إضافية للظهور في التصنيف.
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-center p-4 font-bold text-gray-600 w-16">#</th>
                        <th class="text-right p-4 font-bold text-gray-600">الطالب</th>
                        <th class="text-center p-4 font-bold text-gray-600">المتوسط</th>
                        <th class="text-center p-4 font-bold text-gray-600">الاختبارات</th>
                        <th class="text-center p-4 font-bold text-gray-600">الناجح</th>
                    </tr>
                </thead>
                <tbody>
                    @if($leaderboard->isEmpty())
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-gray-500 mb-1">لا يوجد تصنيف بعد</p>
                                <p class="text-gray-400 text-sm">باشر بحل الاختبارات للظهور في التصنيف</p>
                            </td>
                        </tr>
                    @else
                        @foreach($leaderboard as $idx => $entry)
                            @php
                                $user = $users->get($entry->user_id);
                                $isMe = $entry->user_id === $userId;
                                $medal = match($idx) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => null };
                                $avg = round($entry->avg_score, 1);
                            @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $isMe ? 'bg-blue-50/50 font-bold' : '' }}">
                                <td class="p-4 text-center">
                                    @if($medal)
                                        <span class="text-xl">{{ $medal }}</span>
                                    @else
                                        <span class="text-gray-400 {{ $isMe ? 'text-blue-600 font-black' : '' }}">{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full {{ $isMe ? 'bg-blue-600' : 'bg-gray-200' }} flex items-center justify-center text-white font-bold text-sm shrink-0">
                                            {{ $isMe ? mb_substr(Auth::user()->name, 0, 1) : mb_substr($user?->name ?? 'مستخدم', 0, 1) }}
                                        </div>
                                        <span class="{{ $isMe ? 'text-blue-700' : 'text-gray-900' }} truncate">
                                            {{ $isMe ? Auth::user()->name : ($user?->name ?? 'مستخدم') }}
                                            @if($isMe) <span class="text-xs text-blue-500">(أنت)</span> @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-black {{ $avg >= 80 ? 'text-emerald-600' : ($avg >= 60 ? 'text-amber-600' : 'text-red-500') }}">{{ $avg }}%</span>
                                </td>
                                <td class="p-4 text-center text-gray-600">{{ $entry->total_tests }}</td>
                                <td class="p-4 text-center">
                                    <span class="font-bold {{ $entry->passed_tests == $entry->total_tests ? 'text-emerald-600' : 'text-amber-600' }}">{{ $entry->passed_tests }}/{{ $entry->total_tests }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @if($userTotal > 0 && $leaderboard->isNotEmpty() && $userRank === false)
        <div class="mt-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-3">ترتيبي</h2>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900">{{ Auth::user()->name }}</span>
                        <span class="text-gray-400">— من التصنيف</span>
                    </div>
                    <div class="flex gap-4 mt-1 text-sm text-gray-500">
                        <span>المتوسط: <span class="font-bold {{ $userAvg >= 80 ? 'text-emerald-600' : ($userAvg >= 60 ? 'text-amber-600' : 'text-red-500') }}">{{ $userAvg ? number_format($userAvg, 1) : '—' }}%</span></span>
                        <span>الاختبارات: <span class="font-bold text-gray-700">{{ $userTotal }}</span></span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
