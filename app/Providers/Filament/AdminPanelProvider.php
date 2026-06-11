<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Pages\ActivityLogs;
use App\Filament\Pages\ManageHomepage;
use App\Filament\Pages\QuestionAnalytics;
use App\Filament\Pages\QuizResults;
use App\Filament\Pages\Reports;
use App\Filament\Pages\SchoolDiagnostics;
use App\Filament\Pages\StudentDetail;
use App\Filament\Resources\AccessCodeResource;
use App\Filament\Resources\AccessGrantResource;
use App\Filament\Resources\B2BPackageResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\CourseModuleResource;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\CourseReviewResource;
use App\Filament\Resources\DiscussionThreadResource;
use App\Filament\Resources\DiscountCodeResource;
use App\Filament\Resources\FaqResource;
use App\Filament\Resources\GroupResource;
use App\Filament\Resources\LessonResource;
use App\Filament\Resources\LibraryItemResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\PathResource;
use App\Filament\Resources\PaymentRequestResource;
use App\Filament\Resources\PaymentSettingResource\PaymentSettingResource;
use App\Filament\Resources\PublicBarcodeTestResource;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuizResource;
use App\Filament\Resources\SchoolResource;
use App\Filament\Resources\SectionResource;
use App\Filament\Resources\SkillResource;
use App\Filament\Resources\SubjectResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\CourseEnrollmentsChart;
use App\Filament\Widgets\LatestActivityWidget;
use App\Filament\Widgets\PendingPaymentsWidget;
use App\Filament\Widgets\QuizCompletionChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TodayActivityWidget;
use App\Filament\Widgets\UserRegistrationsChart;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->resources([
                AccessCodeResource::class,
                AccessGrantResource::class,
                B2BPackageResource::class,
                ContactMessageResource::class,
                CourseModuleResource::class,
                CourseResource::class,
                CourseReviewResource::class,
                DiscussionThreadResource::class,
                DiscountCodeResource::class,
                FaqResource::class,
                GroupResource::class,
                LessonResource::class,
                LibraryItemResource::class,
                NotificationResource::class,
                PathResource::class,
                PaymentRequestResource::class,
                PaymentSettingResource::class,
                PublicBarcodeTestResource::class,
                QuestionResource::class,
                QuizResource::class,
                SchoolResource::class,
                SectionResource::class,
                SkillResource::class,
                SubjectResource::class,
                UserResource::class,
            ])
            ->pages([
                ActivityLogs::class,
                ManageHomepage::class,
                QuestionAnalytics::class,
                QuizResults::class,
                Reports::class,
                SchoolDiagnostics::class,
                StudentDetail::class,
            ])
            ->widgets([
                CourseEnrollmentsChart::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
                StatsOverviewWidget::class,
                TodayActivityWidget::class,
                UserRegistrationsChart::class,
                LatestActivityWidget::class,
                PendingPaymentsWidget::class,
                QuizCompletionChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'role:admin,supervisor',
            ]);
    }
}
