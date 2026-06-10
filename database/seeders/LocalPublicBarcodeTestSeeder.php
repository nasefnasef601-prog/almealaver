<?php

namespace Database\Seeders;

use App\Models\Path;
use App\Models\PublicBarcodeTest;
use App\Models\Question;
use App\Models\Section;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalPublicBarcodeTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@demo.local'],
            ['name' => 'Demo Admin', 'password' => Hash::make('Demo123456!'), 'role' => 'admin']
        );

        $path = Path::firstOrCreate(
            ['slug' => 'barcode-local'],
            ['name' => 'Barcode Local', 'name_ar' => 'باركود محلي', 'is_active' => true]
        );

        $subject = Subject::firstOrCreate(
            ['slug' => 'barcode-math', 'path_id' => $path->id],
            ['name' => 'Math', 'name_ar' => 'رياضيات', 'is_active' => true]
        );

        $section = Section::firstOrCreate(
            ['slug' => 'barcode-basics', 'subject_id' => $subject->id],
            ['name' => 'Basics', 'name_ar' => 'أساسيات', 'is_active' => true]
        );

        $skill = Skill::firstOrCreate(
            ['slug' => 'barcode-addition', 'section_id' => $section->id],
            ['name' => 'Addition', 'name_ar' => 'الجمع', 'is_active' => true]
        );

        $question = Question::firstOrCreate(
            ['question_text' => '1 + 1 = ?', 'created_by' => $user->id],
            [
                'question_type' => 'mcq',
                'question_text_ar' => '١ + ١ = ؟',
                'options' => [
                    ['text_ar' => '١', 'is_correct' => false],
                    ['text_ar' => '٢', 'is_correct' => true],
                ],
                'correct_answer' => '٢',
                'points' => 1,
                'difficulty' => 'easy',
                'skill_id' => $skill->id,
                'subject_id' => $subject->id,
                'section_id' => $section->id,
                'status' => 'active',
            ]
        );

        PublicBarcodeTest::updateOrCreate(
            ['slug' => 'local-demo'],
            [
                'title' => 'اختبار باركود محلي',
                'description' => 'اختبار سريع للتأكد من صفحة الباركود العامة.',
                'path_id' => $path->id,
                'subject_id' => $subject->id,
                'section_id' => $section->id,
                'question_ids' => [$question->id],
                'status' => 'active',
                'settings' => [
                    'passingScore' => 60,
                    'randomizeQuestions' => false,
                    'showAnswers' => true,
                    'showExplanations' => true,
                ],
            ]
        );
    }
}
