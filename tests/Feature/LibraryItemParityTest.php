<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LibraryItem;
use App\Models\Path;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryItemParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_visible_library_items_are_scoped_to_path_and_subject(): void
    {
        [$path, $subject, $section] = $this->makePathSubjectAndSection();

        $visible = LibraryItem::create([
            'title' => 'Fractions Support PDF',
            'type' => 'pdf',
            'path_id' => $path->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'url' => 'https://example.com/fractions.pdf',
            'show_on_platform' => true,
            'approval_status' => 'approved',
            'skill_ids' => [1, 2],
        ]);

        LibraryItem::create([
            'title' => 'Hidden Draft',
            'type' => 'pdf',
            'path_id' => $path->id,
            'subject_id' => $subject->id,
            'url' => 'https://example.com/draft.pdf',
            'show_on_platform' => true,
            'approval_status' => 'draft',
        ]);

        $items = LibraryItem::query()
            ->visibleOnPlatform()
            ->where('path_id', $path->id)
            ->where('subject_id', $subject->id)
            ->get();

        $this->assertTrue($items->pluck('id')->contains($visible->id));
        $this->assertSame([1, 2], $visible->fresh()->skill_ids);
        $this->assertSame(1, $items->count());
    }

    public function test_subject_learning_view_uses_library_items_before_lesson_files(): void
    {
        $view = file_get_contents(resource_path('views/public/subject-learning.blade.php'));

        $this->assertStringContainsString('LibraryItem::query()', $view);
        $this->assertStringContainsString('visibleOnPlatform()', $view);
        $this->assertStringContainsString('$libraryItems->isEmpty() && $libraryLessons->isEmpty()', $view);
        $this->assertStringContainsString('@foreach($libraryItems as $item)', $view);
    }

    /**
     * @return array{0: Path, 1: Subject, 2: Section}
     */
    private function makePathSubjectAndSection(): array
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
