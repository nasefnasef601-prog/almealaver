<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegacyRouteRedirectTest extends TestCase
{
    #[DataProvider('legacyRedirects')]
    public function test_legacy_react_routes_redirect_to_laravel_routes(string $from, string $to): void
    {
        $this->get($from)->assertRedirect($to);
    }

    public static function legacyRedirects(): array
    {
        return [
            ['/dashboard', '/student/dashboard'],
            ['/my-quizzes', '/student/dashboard?tab=quizzes'],
            ['/my-requests', '/student/dashboard?tab=payments'],
            ['/reports', '/student/dashboard?tab=reports'],
            ['/favorites', '/student/dashboard?tab=favorites'],
            ['/plan', '/student/dashboard?tab=plan'],
            ['/qa', '/student/dashboard?tab=favorites'],
            ['/book-session', '/student/dashboard?tab=sessions'],
            ['/live-sessions', '/student/dashboard?tab=sessions'],
            ['/profile', '/student/profile'],
            ['/mock-exams', '/student/mock-exams'],
            ['/quizzes', '/student/quizzes'],
            ['/results', '/student/results'],
            ['/achievements', '/student/dashboard'],
            ['/review', '/student/dashboard?tab=favorites'],
        ];
    }
}
