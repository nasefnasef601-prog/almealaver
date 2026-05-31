<?php

namespace Database\Seeders;

use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\PaymentRequest;
use App\Models\PaymentSetting;
use App\Models\Path;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\School;
use App\Models\Section;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::create([
            'name' => 'Almeaa International School',
            'name_ar' => 'مدرسة المئة العالمية',
            'code' => 'ALM001',
            'address' => 'Riyadh, Saudi Arabia',
            'phone' => '+966500000000',
            'email' => 'school@almeaa.com',
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@demo.local',
            'password' => bcrypt('Demo123456!'),
            'phone' => '+966500000001',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $student = User::create([
            'name' => 'Ahmed Student',
            'email' => 'student@demo.local',
            'password' => bcrypt('Demo123456!'),
            'phone' => '+966500000002',
            'role' => 'student',
            'is_active' => true,
        ]);
        $student->assignRole('student');

        $teacher = User::create([
            'name' => 'Sara Teacher',
            'email' => 'teacher@demo.local',
            'password' => bcrypt('Demo123456!'),
            'phone' => '+966500000003',
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $teacher->assignRole('teacher');

        $supervisor = User::create([
            'name' => 'Khaled Supervisor',
            'email' => 'supervisor@demo.local',
            'password' => bcrypt('Demo123456!'),
            'phone' => '+966500000004',
            'role' => 'supervisor',
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $supervisor->assignRole('supervisor');

        $parent = User::create([
            'name' => 'Mona Parent',
            'email' => 'parent@demo.local',
            'password' => bcrypt('Demo123456!'),
            'phone' => '+966500000005',
            'role' => 'parent',
            'is_active' => true,
        ]);
        $parent->assignRole('parent');

        $parent->linkedStudents()->attach($student->id);

        $path = Path::create([
            'name' => 'Scholastic Achievement',
            'name_ar' => 'التحصيلي',
            'slug' => 'scholastic-achievement',
            'description' => 'Comprehensive preparation for scholastic achievement tests',
            'description_ar' => 'تحضير شامل لاختبارات التحصيلي',
            'icon' => 'book',
            'color' => '#2563eb',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Mathematics',
            'name_ar' => 'الرياضيات',
            'slug' => 'mathematics',
            'description' => 'Mathematics preparation',
            'description_ar' => 'تحضير مادة الرياضيات',
            'icon' => 'calculator',
            'color' => '#dc2626',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $section = Section::create([
            'subject_id' => $subject->id,
            'name' => 'Algebra',
            'name_ar' => 'الجبر',
            'slug' => 'algebra',
            'description' => 'Algebra fundamentals',
            'description_ar' => 'أساسيات الجبر',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $skill = Skill::create([
            'section_id' => $section->id,
            'name' => 'Linear Equations',
            'name_ar' => 'المعادلات الخطية',
            'slug' => 'linear-equations',
            'description' => 'Solving linear equations',
            'description_ar' => 'حل المعادلات الخطية',
            'skill_category' => 'application',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $freeCourse = Course::create([
            'title' => 'Introduction to Algebra',
            'title_ar' => 'مقدمة في الجبر',
            'slug' => 'introduction-to-algebra',
            'description' => 'Learn the basics of algebra for free',
            'description_ar' => 'تعلم أساسيات الجبر مجانًا',
            'short_description' => 'Free algebra course',
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
            'status' => 'approved',
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'created_by' => $teacher->id,
            'assigned_teacher_id' => $teacher->id,
            'difficulty_level' => 'beginner',
            'duration_minutes' => 120,
            'has_certificate' => false,
            'sort_order' => 1,
        ]);

        $paidCourse = Course::create([
            'title' => 'Advanced Algebra Mastery',
            'title_ar' => 'إتقان الجبر المتقدم',
            'slug' => 'advanced-algebra-mastery',
            'description' => 'Master advanced algebra concepts',
            'description_ar' => 'أتقن مفاهيم الجبر المتقدمة',
            'short_description' => 'Paid advanced course',
            'price' => 199.99,
            'is_free' => false,
            'is_published' => true,
            'status' => 'approved',
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'created_by' => $teacher->id,
            'assigned_teacher_id' => $teacher->id,
            'difficulty_level' => 'advanced',
            'duration_minutes' => 300,
            'has_certificate' => true,
            'sort_order' => 2,
        ]);

        foreach ([$freeCourse, $paidCourse] as $course) {
            $module = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Chapter 1: Basics',
                'title_ar' => 'الفصل الأول: الأساسيات',
                'description' => 'Foundational concepts',
                'description_ar' => 'المفاهيم الأساسية',
                'sort_order' => 1,
                'is_free' => $course->is_free,
            ]);

            Lesson::create([
                'module_id' => $module->id,
                'course_id' => $course->id,
                'title' => 'Introduction',
                'title_ar' => 'مقدمة',
                'description' => 'First lesson',
                'description_ar' => 'الدرس الأول',
                'content_type' => 'video',
                'content_text' => 'Welcome to this course! In this lesson we will cover the fundamental concepts.',
                'content_text_ar' => 'مرحبًا بك في هذه الدورة! في هذا الدرس سنغطي المفاهيم الأساسية.',
                'video_url' => 'https://www.youtube.com/watch?v=example',
                'duration_minutes' => 15,
                'is_free' => $course->is_free,
                'is_published' => true,
                'sort_order' => 1,
            ]);

            Lesson::create([
                'module_id' => $module->id,
                'course_id' => $course->id,
                'title' => 'Practice Exercises',
                'title_ar' => 'تمارين تطبيقية',
                'description' => 'Practice what you learned',
                'description_ar' => 'طبق ما تعلمته',
                'content_type' => 'text',
                'content_text' => 'Try solving these problems on your own before checking the solutions.',
                'content_text_ar' => 'حاول حل هذه المسائل بنفسك قبل مراجعة الحلول.',
                'duration_minutes' => 30,
                'is_free' => $course->is_free,
                'is_published' => true,
                'sort_order' => 2,
            ]);
        }

        $quiz = Quiz::create([
            'title' => 'Algebra Basics Quiz',
            'title_ar' => 'اختبار أساسيات الجبر',
            'description' => 'Test your algebra knowledge',
            'description_ar' => 'اختبر معرفتك بالجبر',
            'course_id' => $freeCourse->id,
            'created_by' => $teacher->id,
            'quiz_type' => 'practice',
            'difficulty' => 'easy',
            'time_limit' => 30,
            'passing_score' => 60,
            'max_attempts' => 3,
            'randomize_questions' => true,
            'show_answers' => true,
            'show_explanations' => true,
            'is_published' => true,
            'status' => 'approved',
        ]);

        $questionsData = [
            ['What is 2 + 2?', 'ما حاصل 2 + 2؟', '4', ['3', '4', '5', '6'], 0],
            ['What is the square root of 16?', 'ما الجذر التربيعي للعدد 16؟', '4', ['2', '4', '8', '16'], 0],
            ['Solve for x: x + 5 = 10', 'حل المعادلة: س + 5 = 10', 'x = 5', ['x = 5', 'x = 10', 'x = 15', 'x = -5'], 0],
        ];

        foreach ($questionsData as $i => $qData) {
            $options = array_map(function ($opt) {
                return ['id' => uniqid(), 'text' => $opt, 'text_ar' => $opt, 'is_correct' => false];
            }, $qData[3]);
            $options[0]['is_correct'] = true;

            Question::create([
                'quiz_id' => $quiz->id,
                'created_by' => $teacher->id,
                'question_type' => 'mcq',
                'question_text' => $qData[0],
                'question_text_ar' => $qData[1],
                'options' => $options,
                'correct_answer' => $qData[2],
                'explanation' => "The correct answer is: {$qData[2]}",
                'explanation_ar' => "الإجابة الصحيحة هي: {$qData[2]}",
                'points' => 1,
                'difficulty' => 'easy',
                'status' => 'approved',
            ]);
        }

        PaymentSetting::create([
            'payment_method' => 'bank_transfer',
            'is_active' => true,
            'config' => [
                'bank_name' => 'البنك الأهلي السعودي',
                'account_name' => 'منصة المئة',
                'account_number' => 'SA1234567890',
                'iban' => 'SA1234567890123456789012',
            ],
            'sort_order' => 1,
        ]);

        $paymentRequest = PaymentRequest::create([
            'user_id' => $student->id,
            'course_id' => $paidCourse->id,
            'payment_method' => 'bank_transfer',
            'amount' => 199.99,
            'currency' => 'SAR',
            'status' => 'approved',
            'admin_id' => $admin->id,
            'notes' => 'Manual approval for demo',
            'reviewed_at' => now(),
        ]);

        AccessGrant::create([
            'user_id' => $student->id,
            'course_id' => $paidCourse->id,
            'grant_type' => 'purchase',
            'status' => 'active',
            'granted_by' => $admin->id,
            'payment_request_id' => $paymentRequest->id,
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $demoGroup = Group::create([
            'school_id' => $school->id,
            'name' => 'الصف الأول',
            'type' => 'class',
            'owner_id' => $teacher->id,
        ]);

        $demoGroup->users()->attach($student->id, ['role' => 'student']);

        LessonCompletion::create([
            'user_id' => $student->id,
            'lesson_id' => $freeCourse->lessons()->first()->id,
            'course_id' => $freeCourse->id,
            'completed_at' => now(),
        ]);
    }
}
