<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'الفصول والمجموعات';

    protected static ?string $pluralLabel = 'الفصول والمجموعات';

    protected static ?string $label = 'فصل / مجموعة';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('الاسم')
                ->required(),
            Forms\Components\Textarea::make('description')
                ->label('الوصف')
                ->rows(2),
            Forms\Components\TextInput::make('location')
                ->label('الموقع / الفصل الدراسي'),
            Forms\Components\Select::make('type')
                ->label('النوع')
                ->options([
                    'school' => 'مدرسة',
                    'class' => 'فصل',
                    'private_group' => 'مجموعة خاصة',
                ])
                ->required()
                ->default('class'),
            Forms\Components\Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
            Forms\Components\Select::make('school_id')
                ->label('المدرسة')
                ->relationship('school', 'name_ar')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('parent_id')
                ->label('المجموعة الأم')
                ->relationship('parent', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('owner_id')
                ->label('مدير/مالك المجموعة')
                ->relationship('owner', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('course_ids')
                ->label('الدورات المرتبطة')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => Course::query()
                    ->where('is_published', true)
                    ->limit(250)
                    ->get()
                    ->mapWithKeys(fn (Course $course) => [
                        $course->id => Str::limit($course->title_ar ?: $course->title, 90),
                    ])
                    ->all()),
            static::roleSelect('student_ids', 'الطلاب', 'student'),
            static::roleSelect('teacher_ids', 'المعلمون', 'teacher'),
            static::roleSelect('class_supervisor_ids', 'مشرفو الفصل', 'class_supervisor'),
            static::roleSelect('school_manager_ids', 'مديرو/مشرفو المدرسة', 'school_manager'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'school' => 'مدرسة',
                        'class' => 'فصل',
                        'private_group' => 'مجموعة خاصة',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('الطلاب')
                    ->counts('students'),
                Tables\Columns\TextColumn::make('teachers_count')
                    ->label('المعلمون')
                    ->counts('teachers'),
                Tables\Columns\TextColumn::make('class_supervisors_count')
                    ->label('مشرفو الفصل')
                    ->counts('classSupervisors'),
                Tables\Columns\TextColumn::make('school_managers_count')
                    ->label('إدارة المدرسة')
                    ->counts('schoolManagers'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'school' => 'مدرسة',
                        'class' => 'فصل',
                        'private_group' => 'مجموعة خاصة',
                    ]),
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    private static function roleSelect(string $name, string $label, string $pivotRole): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->label($label)
            ->multiple()
            ->searchable()
            ->preload()
            ->options(fn () => User::query()
                ->whereIn('role', match ($pivotRole) {
                    'student' => ['student'],
                    'teacher' => ['teacher'],
                    default => ['supervisor', 'admin', 'teacher'],
                })
                ->orderBy('name')
                ->limit(300)
                ->pluck('name', 'id')
                ->all())
            ->dehydrated(false)
            ->afterStateHydrated(function (Forms\Components\Select $component, ?Group $record) use ($pivotRole): void {
                if (!$record) {
                    $component->state([]);
                    return;
                }

                $component->state(
                    DB::table('group_user')
                        ->where('group_id', $record->id)
                        ->where('role', $pivotRole)
                        ->pluck('user_id')
                        ->map(fn ($id) => (string) $id)
                        ->all()
                );
            })
            ->saveRelationshipsUsing(function (Group $record, $state) use ($pivotRole): void {
                DB::table('group_user')
                    ->where('group_id', $record->id)
                    ->where('role', $pivotRole)
                    ->delete();

                $rows = collect($state ?? [])
                    ->filter()
                    ->unique()
                    ->map(fn ($userId) => [
                        'group_id' => $record->id,
                        'user_id' => (int) $userId,
                        'role' => $pivotRole,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->values()
                    ->all();

                if ($rows !== []) {
                    DB::table('group_user')->insert($rows);
                }

                if ($pivotRole === 'student' && $record->school_id) {
                    User::whereIn('id', collect($state ?? [])->filter()->all())
                        ->update(['school_id' => $record->school_id]);
                }
            });
    }
}
