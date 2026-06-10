<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Path;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLearningCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_category_subject_query_redirects_to_subject_learning_page_with_tab(): void
    {
        [$path, $subject] = $this->makePathAndSubject();

        $response = $this->get("/category/{$path->id}?subject={$subject->id}&tab=skills");

        $response->assertRedirect(route('category.subject', ['path' => $path, 'subject' => $subject]) . '?tab=skills');
    }

    public function test_subject_learning_page_uses_requested_tab_and_next_action(): void
    {
        $view = file_get_contents(resource_path('views/public/subject-learning.blade.php'));

        $this->assertStringContainsString("request('tab', 'skills')", $view);
        $this->assertStringContainsString("'foundation' => 'skills'", $view);
        $this->assertStringContainsString('setTab(value)', $view);
        $this->assertStringContainsString('الخطوة التالية', $view);
    }

    /**
     * @return array{0: Path, 1: Subject, 2: Section}
     */
    private function makePathAndSubject(): array
    {
        $path = Path::create([
            'name' => 'Qudrat',
            'name_ar' => 'القدرات',
            'slug' => 'qudrat',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'path_id' => $path->id,
            'name' => 'Quantitative',
            'name_ar' => 'الكمي',
            'slug' => 'quantitative',
            'is_active' => true,
        ]);

        $section = Section::create([
            'subject_id' => $subject->id,
            'name' => 'Basics',
            'name_ar' => 'الأساسيات',
            'slug' => 'basics',
            'is_active' => true,
        ]);

        return [$path, $subject, $section];
    }
}
