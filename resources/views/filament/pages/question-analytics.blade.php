<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Quiz Selector --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <div class="max-w-md">
                {{ $this->form }}
            </div>
        </div>

        @if($selectedQuizId && !empty($questionStats))
            {{-- Summary --}}
            @php
                $avgAccuracy = collect($this->questionStats)->avg('accuracy');
                $easyCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] >= 80));
                $mediumCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] >= 50 && $q['accuracy'] < 80));
                $hardCount = count(array_filter($this->questionStats, fn($q) => $q['accuracy'] < 50));
            @endphp
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">متوسط الدقة</p>
                    <p class="text-2xl font-black {{ $avgAccuracy >= 70 ? 'text-emerald-600' : ($avgAccuracy >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($avgAccuracy, 1) }}%</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة سهلة (80%+)</p>
                    <p class="text-2xl font-black text-emerald-600">{{ $easyCount }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة متوسطة</p>
                    <p class="text-2xl font-black text-amber-600">{{ $mediumCount }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-500">أسئلة صعبة (&lt;50%)</p>
                    <p class="text-2xl font-black text-red-600">{{ $hardCount }}</p>
                </div>
            </div>

            {{-- Questions Table --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">{{ $this->quizTitle }} — {{ count($this->questionStats) }} سؤال، {{ $this->questionStats[0]['total'] ?? 0 }} محاولة</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                <th class="text-right px-5 py-3 w-12">#</th>
                                <th class="text-right px-5 py-3">السؤال</th>
                                <th class="text-center px-5 py-3">صحيح ✓</th>
                                <th class="text-center px-5 py-3">خطأ ✗</th>
                                <th class="text-center px-5 py-3">لم يجب —</th>
                                <th class="text-center px-5 py-3">الدقة</th>
                                <th class="text-center px-5 py-3">مستوى الصعوبة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->questionStats as $i => $qs)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-900 max-w-xs truncate">{{ $qs['text'] }}</td>
                                    <td class="px-5 py-4 text-center font-bold text-emerald-600">{{ $qs['correct'] }}</td>
                                    <td class="px-5 py-4 text-center font-bold text-red-500">{{ $qs['incorrect'] }}</td>
                                    <td class="px-5 py-4 text-center text-gray-400">{{ $qs['unanswered'] }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                                            {{ $qs['accuracy'] >= 80 ? 'bg-emerald-100 text-emerald-700' : ($qs['accuracy'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $qs['accuracy'] }}%
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $qs['accuracy'] >= 80 ? 'bg-emerald-500' : ($qs['accuracy'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 style="width: {{ $qs['accuracy'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($selectedQuizId)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد بيانات كافية</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
