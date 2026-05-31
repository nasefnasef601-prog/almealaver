<?php

namespace App\Filament\Pages;

use App\Models\QuizResult;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class QuestionAnalytics extends Page
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'تحليل الأسئلة';

    protected static ?string $title = 'تحليل أداء الأسئلة';

    protected string $view = 'filament.pages.question-analytics';

    public ?int $selectedQuizId = null;
    public array $questionStats = [];
    public ?string $quizTitle = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('quiz_id')
                    ->label('اختر الاختبار')
                    ->options(Quiz::where('is_published', true)->pluck('title_ar', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->loadAnalysis($state)),
            ]);
    }

    public function loadAnalysis(?int $quizId): void
    {
        if (!$quizId) return;

        $this->selectedQuizId = $quizId;
        $quiz = Quiz::with('questions')->find($quizId);
        if (!$quiz) return;

        $this->quizTitle = $quiz->title_ar ?? $quiz->title;

        $attempts = QuizAttempt::where('quiz_id', $quizId)->whereNotNull('answers')->get();
        $totalAttempts = $attempts->count();

        $stats = [];
        foreach ($quiz->questions as $question) {
            $correct = 0;
            $incorrect = 0;
            $unanswered = 0;

            foreach ($attempts as $attempt) {
                $answers = $attempt->answers ?? [];

                $answer = $answers[$question->id] ?? null;

                if ($answer === null) {
                    $unanswered++;
                } elseif ((string) $answer === (string) $question->correct_option) {
                    $correct++;
                } else {
                    $incorrect++;
                }
            }

            $stats[] = [
                'id' => $question->id,
                'text' => $question->question_text,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'total' => $totalAttempts,
                'accuracy' => $totalAttempts > 0 ? round(($correct / $totalAttempts) * 100, 1) : 0,
                'difficulty' => $totalAttempts > 0 ? $correct / $totalAttempts : 0,
            ];
        }

        usort($stats, fn($a, $b) => $a['accuracy'] <=> $b['accuracy']);

        $this->questionStats = $stats;
    }

    protected static bool $shouldRegisterNavigation = true;
}
