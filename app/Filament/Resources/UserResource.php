<?php

namespace App\Filament\Resources;

use App\Filament\Pages\StudentDetail;
use App\Filament\Resources\UserResource\Pages;
use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'المستخدمين';

    protected static ?string $pluralLabel = 'المستخدمين';

    protected static ?string $label = 'مستخدم';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('رقم الجوال')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مدير',
                        'student' => 'طالب',
                        'teacher' => 'مدرس',
                        'supervisor' => 'مشرف',
                        'parent' => 'ولي أمر',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('linked_students', [])),
                Forms\Components\TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8),
                Forms\Components\Select::make('linked_students')
                    ->label('الأبناء (لولي الأمر)')
                    ->multiple()
                    ->relationship('linkedStudents', 'name')
                    ->visible(fn (callable $get) => $get('role') === 'parent')
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin' => 'مدير',
                        'student' => 'طالب',
                        'teacher' => 'مدرس',
                        'supervisor' => 'مشرف',
                        'parent' => 'ولي أمر',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('enrolled_courses_count')
                    ->label('الكورسات')
                    ->counts('accessGrants')
                    ->formatStateUsing(fn ($state) => $state ?: '—')
                    ->color('info'),
                Tables\Columns\TextColumn::make('completed_lessons_count')
                    ->label('الدروس')
                    ->formatStateUsing(function ($record) {
                        return LessonCompletion::where('user_id', $record->id)->count() ?: '—';
                    })
                    ->color('success'),
                Tables\Columns\TextColumn::make('quiz_attempts_count')
                    ->label('الاختبارات')
                    ->formatStateUsing(function ($record) {
                        return QuizAttempt::where('user_id', $record->id)->count() ?: '—';
                    })
                    ->color('warning'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مدير',
                        'student' => 'طالب',
                        'teacher' => 'مدرس',
                        'supervisor' => 'مشرف',
                        'parent' => 'ولي أمر',
                    ]),
            ])
            ->actions([
                Action::make('student_report')
                    ->label('تقرير الطالب')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (User $record) => $record->role === 'student'
                        ? StudentDetail::getUrl(['id' => $record->id])
                        : null)
                    ->visible(fn (User $record) => $record->role === 'student'),
                EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    BulkAction::make('enroll_course')
                        ->label('تسجيل في كورس')
                        ->icon('heroicon-o-academic-cap')
                        ->form([
                            Select::make('course_id')
                                ->label('اختر الكورس')
                                ->options(Course::where('is_published', true)->pluck('title_ar', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, $records) {
                            $course = Course::find($data['course_id']);
                            if (!$course) return;

                            $count = 0;
                            foreach ($records as $user) {
                                $exists = AccessGrant::where('user_id', $user->id)
                                    ->where('course_id', $course->id)
                                    ->exists();

                                if (!$exists) {
                                    AccessGrant::create([
                                        'user_id' => $user->id,
                                        'course_id' => $course->id,
                                        'grant_type' => 'admin',
                                        'status' => 'active',
                                        'granted_by' => auth()->id(),
                                        'starts_at' => now(),
                                        'expires_at' => now()->addYear(),
                                    ]);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title("تم تسجيل {$count} طالب في الكورس بنجاح")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
