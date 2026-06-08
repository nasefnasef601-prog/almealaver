@extends('layouts.app')

@section('title', 'الأسئلة الشائعة')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-black text-gray-900 mb-4">الأسئلة الشائعة</h1>
        <p class="text-lg text-gray-500">إجابات لأكثر الأسئلة تكراراً عن منصة المئة</p>
    </div>

    @forelse($faqs as $category => $items)
        @if($category)
            <h2 class="text-xl font-bold text-gray-800 mb-4 mt-8">{{ $category }}</h2>
        @endif
        <div class="space-y-3 mb-8" x-data="{ open: null }">
            @foreach($items as $faq)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all">
                    <button @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}"
                            class="w-full text-right px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                        <span class="font-bold text-gray-900">{{ $faq->question_ar }}</span>
                        <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform" 
                             :class="open === {{ $faq->id }} ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === {{ $faq->id }}" x-collapse class="px-6 pb-4">
                        <div class="text-gray-600 leading-relaxed">{!! $faq->answer_ar !!}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg">لا توجد أسئلة شائعة بعد</p>
        </div>
    @endforelse
</div>
@endsection
