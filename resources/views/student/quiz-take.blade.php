@extends('layouts.student', ['activeTab' => 'quizzes'])

@section('title', $quiz->title)

@php
    $questionsArray = $questions->values();
    $totalQ = $questionsArray->count();
@endphp

@section('content')
<div class="max-w-4xl mx-auto" x-data="quizApp({{ $totalQ }}, {{ $timeLimit ?? 0 }})">

    {{-- Header bar --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-bold text-gray-900 text-sm">{{ $quiz->title_ar ?? $quiz->title }}</p>
                <p class="text-xs text-gray-400">
                    <span x-text="`السؤال ${current + 1} من ${total}`"></span>
                    <span x-show="timeLimit > 0" class="mr-2">— <span x-text="formatTime(timeLeft)" :class="timeLeft <= 30 ? 'text-red-500 font-bold' : ''"></span></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-gray-600" x-text="`${answeredCount}/${total}`"></span>
            <div class="w-32 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" :style="`width: ${(answeredCount / total) * 100}%`"></div>
            </div>
            <form action="{{ route('student.quiz.submit', $attempt->id) }}" method="POST" x-ref="submitForm">
                @csrf
                <input type="hidden" name="answers" x-model="JSON.stringify(submittedAnswers)">
                <input type="hidden" name="time_taken_seconds" x-model="elapsed">
                <button type="button" @click="confirmFinish()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors">إنهاء</button>
            </form>
        </div>
    </div>

    {{-- Low time warning --}}
    <div x-show="timeLimit > 0 && timeLeft <= 30 && timeLeft > 0" x-transition
         class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 mb-6 flex items-center gap-3 text-sm font-bold">
        <svg class="w-5 h-5 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <span>الوقت المتبقي أقل من <span x-text="timeLeft"></span> ثانية! يرجى الإسراع.</span>
    </div>

    {{-- Question card --}}
    <template x-for="(q, idx) in questions" :key="q.id">
        <div x-show="current === idx" x-transition>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
                <div class="flex items-start justify-between gap-3 mb-6">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0" x-text="idx + 1"></span>
                        <p class="text-lg font-bold text-gray-900" x-html="q.text"></p>
                    </div>
                    <button @click="toggleFlag(idx)" type="button"
                            :class="flagged[idx] ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400'"
                            class="p-1.5 rounded-lg hover:bg-amber-50 transition-all shrink-0" title="ضع علامة للمراجعة">
                        <svg class="w-6 h-6" :fill="flagged[idx] ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(opt, oi) in q.options" :key="oi">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="submittedAnswers[q.id] == oi ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-200 bg-white'">
                            <input type="radio" :name="`q_${q.id}`" :value="oi"
                                   @change="selectAnswer(q.id, oi)"
                                   :checked="submittedAnswers[q.id] == oi"
                                   class="w-5 h-5 text-blue-600 accent-blue-600 shrink-0">
                            <span class="text-gray-700 font-medium" x-html="opt.text_ar || opt.text"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Navigation --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-bold text-gray-600">خريطة الأسئلة</p>
            <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span> تمت الإجابة</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200"></span> لم تتم</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> الحالي</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-400"></span> للمراجعة</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <template x-for="(q, idx) in questions" :key="idx">
                <button @click="current = idx" class="relative w-9 h-9 rounded-lg text-xs font-bold transition-colors"
                        :class="current === idx ? 'bg-blue-500 text-white' : (submittedAnswers[q.id] !== undefined ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')"
                        x-text="idx + 1">
                    <span x-show="flagged[idx]" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full border border-white"></span>
                </button>
            </template>
        </div>
        <div class="mt-3 flex gap-3 text-xs">
            <span class="text-gray-500">المؤشر <span class="font-bold text-amber-500" x-text="flaggedCount"></span> للمراجعة</span>
        </div>
    </div>

    {{-- Prev/Next --}}
    <div class="flex items-center justify-between">
        <button @click="prevQ()" x-show="current > 0"
                class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">
            → السابق
        </button>
        <div class="flex-1"></div>
        <button @click="nextQ()" x-show="current < total - 1"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors">
            التالي ←
        </button>
        <button @click="confirmFinish()" x-show="current === total - 1"
                class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition-colors">
            إنهاء الاختبار
        </button>
    </div>

    {{-- Finish confirmation modal --}}
    <div x-show="showFinishModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="showFinishModal = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 mb-2">تأكيد إنهاء الاختبار</h2>
            <p class="text-gray-500 text-sm mb-2">
                لقد أجبت على <span class="font-bold text-gray-900" x-text="answeredCount"></span> من <span class="font-bold text-gray-900" x-text="total"></span> أسئلة.
            </p>
            <p class="text-gray-500 text-sm mb-6">
                <span x-show="answeredCount < total" class="text-amber-600">لديك <span x-text="total - answeredCount"></span> أسئلة لم تجب عليها.</span>
                <span x-show="answeredCount === total">جميع الأسئلة تمت الإجابة عليها.</span>
            </p>
            <div class="flex gap-3">
                <button @click="showFinishModal = false" class="flex-1 px-6 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">العودة</button>
                <button @click="finishQuiz()" class="flex-1 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-colors">تأكيد الإنـهاء</button>
            </div>
        </div>
    </div>
</div>

<script>
    function quizApp(totalQ, timeLimitMinutes) {
        return {
            current: 0,
            total: totalQ,
            questions: @json($questionsArray->map(fn($q) => [
                'id' => $q->id,
                'text' => $q->question_text_ar ?? $q->question_text,
                'options' => $q->options ?? [],
            ])),
            submittedAnswers: {},
            flagged: {},
            timeLimit: timeLimitMinutes || 0,
            timeLeft: (timeLimitMinutes || 0) * 60,
            elapsed: 0,
            timerInterval: null,
            showFinishModal: false,
            storageKey: 'quiz_attempt_{{ $attempt->id }}',

            init() {
                const saved = localStorage.getItem(this.storageKey);
                if (saved) {
                    try {
                        const data = JSON.parse(saved);
                        this.submittedAnswers = data.answers || {};
                        this.flagged = data.flagged || {};
                        this.elapsed = data.elapsed || 0;
                        if (this.timeLimit > 0) {
                            this.timeLeft = Math.max(0, (timeLimitMinutes * 60) - this.elapsed);
                        }
                    } catch(e) {}
                }

                if (this.timeLimit > 0) {
                    this.timerInterval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                            this.elapsed++;
                            this.autoSave();
                        } else {
                            clearInterval(this.timerInterval);
                            this.finishQuiz();
                        }
                    }, 1000);
                } else {
                    this.timerInterval = setInterval(() => { this.elapsed++; this.autoSave(); }, 5000);
                }

                window.addEventListener('beforeunload', (e) => {
                    if (Object.keys(this.submittedAnswers).length > 0) {
                        this.autoSave();
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            },

            autoSave() {
                localStorage.setItem(this.storageKey, JSON.stringify({
                    answers: this.submittedAnswers,
                    flagged: this.flagged,
                    elapsed: this.elapsed,
                }));
            },

            get answeredCount() {
                return Object.keys(this.submittedAnswers).length;
            },

            get flaggedCount() {
                return Object.values(this.flagged).filter(v => v).length;
            },

            toggleFlag(idx) {
                this.flagged[idx] = !this.flagged[idx];
                this.autoSave();
            },

            selectAnswer(qId, optIdx) {
                this.submittedAnswers[qId] = optIdx;
                this.autoSave();
            },

            nextQ() {
                if (this.current < this.total - 1) this.current++;
            },

            prevQ() {
                if (this.current > 0) this.current--;
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return `${m}:${s.toString().padStart(2, '0')}`;
            },

            confirmFinish() {
                this.showFinishModal = true;
            },

            finishQuiz() {
                if (this.timerInterval) clearInterval(this.timerInterval);
                localStorage.removeItem(this.storageKey);
                const input = this.$refs.submitForm.querySelector('input[name="answers"]');
                if (input) input.value = JSON.stringify(this.submittedAnswers);
                this.$refs.submitForm.submit();
            }
        };
    }
</script>
@endsection
