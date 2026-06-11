<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicBarcodeTestResource\Pages;
use App\Models\PublicBarcodeTest;
use App\Models\Question;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PublicBarcodeTestResource extends Resource
{
    protected static ?string $model = PublicBarcodeTest::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'اختبارات الباركود';

    protected static ?string $pluralLabel = 'اختبارات الباركود';

    protected static ?string $label = 'اختبار باركود';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')
                ->label('العنوان')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state) ?: Str::random(8))),
            Forms\Components\TextInput::make('slug')
                ->label('الرابط المختصر')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->label('الوصف')
                ->rows(3),
            Forms\Components\Select::make('path_id')
                ->label('المسار')
                ->relationship('path', 'name_ar')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('subject_id')
                ->label('المادة')
                ->relationship('subject', 'name_ar')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('section_id')
                ->label('القسم')
                ->relationship('section', 'name_ar')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('question_ids')
                ->label('الأسئلة')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => Question::query()
                    ->where('status', 'active')
                    ->limit(250)
                    ->get()
                    ->mapWithKeys(fn (Question $question) => [
                        $question->id => Str::limit($question->question_text_ar ?: $question->question_text, 90),
                    ])
                    ->all())
                ->required(),
            Forms\Components\Select::make('test_kind')
                ->label('نوع الاختبار')
                ->options([
                    'quick' => 'سريع',
                    'mock' => 'محاكي',
                ])
                ->default('quick')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'draft' => 'مسودة',
                    'active' => 'نشط',
                    'paused' => 'متوقف مؤقتا',
                    'archived' => 'مؤرشف',
                ])
                ->default('draft')
                ->required(),
            Forms\Components\Toggle::make('show_result_to_student')
                ->label('إظهار النتيجة للطالب')
                ->default(true),
            Forms\Components\Toggle::make('collect_school')
                ->label('طلب اسم المدرسة')
                ->default(true),
            Forms\Components\Toggle::make('collect_classroom')
                ->label('طلب الفصل')
                ->default(true),
            Forms\Components\TextInput::make('settings.passingScore')
                ->label('درجة النجاح %')
                ->numeric()
                ->default(60),
            Forms\Components\TextInput::make('settings.timeLimit')
                ->label('الوقت بالدقائق')
                ->numeric()
                ->default(20),
            Forms\Components\Toggle::make('settings.randomizeQuestions')
                ->label('ترتيب الأسئلة عشوائيا')
                ->default(true),
            Forms\Components\Toggle::make('settings.showAnswers')
                ->label('إظهار مراجعة الإجابات')
                ->default(true),
            Forms\Components\Toggle::make('settings.showExplanations')
                ->label('إظهار الشرح')
                ->default(true),
            Forms\Components\DateTimePicker::make('starts_at')
                ->label('يبدأ في'),
            Forms\Components\DateTimePicker::make('ends_at')
                ->label('ينتهي في'),
            Forms\Components\TextInput::make('max_submissions')
                ->label('أقصى عدد محاولات عامة')
                ->numeric(),
            Forms\Components\Select::make('owner_type')
                ->label('المالك')
                ->options([
                    'platform' => 'المنصة',
                    'school' => 'مدرسة',
                    'teacher' => 'معلم',
                ])
                ->default('platform'),
            Forms\Components\TextInput::make('owner_id')
                ->label('معرف المالك'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط')
                    ->formatStateUsing(fn ($state) => url('/barcode-test/'.$state))
                    ->copyable(),
                Tables\Columns\TextColumn::make('test_kind')->label('النوع')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submissions_count')->counts('submissions')->label('المحاولات'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicBarcodeTests::route('/'),
        ];
    }
}
