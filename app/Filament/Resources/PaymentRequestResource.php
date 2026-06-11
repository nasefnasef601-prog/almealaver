<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentRequestResource\Pages;
use App\Mail\PaymentApproved;
use App\Mail\PaymentRejected;
use App\Models\AccessGrant;
use App\Models\ActivityLog;
use App\Models\Notification as AppNotification;
use App\Models\PaymentRequest;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class PaymentRequestResource extends Resource
{
    protected static ?string $model = PaymentRequest::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'طلبات الدفع';

    protected static ?string $pluralLabel = 'طلبات الدفع';

    protected static ?string $label = 'طلب دفع';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('المستخدم')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->disabled(),
                Forms\Components\TextInput::make('payment_method')
                    ->label('طريقة الدفع')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('الحالة')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->disabled(),
                View::make('filament.forms.components.receipt-image')
                    ->visible(fn ($record) => $record && $record->bank_transfer_receipt),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الكورس')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'bank_transfer' => 'تحويل بنكي',
                        'wallet' => 'محفظة',
                        'tap' => 'بطاقة',
                        default => $state,
                    }),
                Tables\Columns\ImageColumn::make('bank_transfer_receipt')
                    ->label('الإيصال')
                    ->circular()
                    ->visible(fn ($record) => $record && $record->bank_transfer_receipt)
                    ->extraImgAttributes(['class' => 'w-10 h-10 object-cover']),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending_manual_review' => 'قيد المراجعة',
                        'approved' => 'تمت الموافقة',
                        'rejected' => 'مرفوض',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending_manual_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('تاريخ المراجعة')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending_manual_review' => 'قيد المراجعة',
                        'approved' => 'تمت الموافقة',
                        'rejected' => 'مرفوض',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الموافقة')
                    ->modalDescription('هل أنت متأكد من الموافقة على طلب الدفع هذا؟')
                    ->modalSubmitActionLabel('نعم، موافقة')
                    ->action(function (PaymentRequest $record) {
                        $record->loadMissing('course.subject');

                        if ($record->status !== 'pending_manual_review') {
                            Notification::make()->danger()->title('تمت معالجة هذا الطلب مسبقًا.')->send();
                            return;
                        }

                        $record->update([
                            'status' => 'approved',
                            'admin_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        AccessGrant::create([
                            'user_id' => $record->user_id,
                            'course_id' => $record->course_id,
                            'course_ids' => $record->course_id ? [(string) $record->course_id] : [],
                            'content_types' => ['courses'],
                            'path_ids' => $record->course?->subject?->path_id ? [(string) $record->course->subject->path_id] : [],
                            'subject_ids' => $record->course?->subject_id ? [(string) $record->course->subject_id] : [],
                            'source_type' => 'payment_request',
                            'source_id' => 'payment_request:' . $record->id,
                            'idempotency_key' => 'payment_request:' . $record->id,
                            'metadata' => [
                                'source' => 'filament_payment_request',
                                'payment_request_id' => $record->id,
                                'amount' => $record->amount,
                                'payment_method' => $record->payment_method,
                            ],
                            'grant_type' => 'purchase',
                            'status' => 'active',
                            'granted_by' => auth()->id(),
                            'granted_at' => now(),
                            'payment_request_id' => $record->id,
                            'starts_at' => now(),
                            'expires_at' => now()->addYear(),
                        ]);

                        AppNotification::create([
                            'user_id' => $record->user_id,
                            'type' => 'payment',
                            'title' => 'تم قبول طلب الدفع',
                            'title_ar' => 'تم قبول طلب الدفع',
                            'body' => "تمت الموافقة على طلب الدفع بقيمة {$record->amount} ريال وتم منحك حق الوصول إلى الكورس.",
                            'body_ar' => "تمت الموافقة على طلب الدفع بقيمة {$record->amount} ريال وتم منحك حق الوصول إلى الكورس.",
                            'data' => ['amount' => $record->amount, 'payment_id' => $record->id],
                        ]);

                        Mail::to($record->user->email)->send(new PaymentApproved($record));

                        ActivityLog::log(
                            'payment.approved',
                            "تمت الموافقة على طلب دفع #{$record->id} بقيمة {$record->amount} ريال",
                            PaymentRequest::class,
                            $record->id,
                            ['amount' => $record->amount, 'user_id' => $record->user_id]
                        );

                        Notification::make()->success()->title('تمت الموافقة على طلب الدفع ومنح الوصول.')->send();
                    })
                    ->visible(fn (PaymentRequest $record) => $record->status === 'pending_manual_review'),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الرفض')
                    ->modalDescription('هل أنت متأكد من رفض طلب الدفع هذا؟')
                    ->modalSubmitActionLabel('نعم، رفض')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('سبب الرفض')
                            ->required(),
                    ])
                    ->action(function (PaymentRequest $record, array $data) {
                        if ($record->status !== 'pending_manual_review') {
                            Notification::make()->danger()->title('تمت معالجة هذا الطلب مسبقًا.')->send();
                            return;
                        }

                        $record->update([
                            'status' => 'rejected',
                            'admin_id' => auth()->id(),
                            'reviewed_at' => now(),
                            'notes' => $data['notes'],
                        ]);

                        AppNotification::create([
                            'user_id' => $record->user_id,
                            'type' => 'payment',
                            'title' => 'تم رفض طلب الدفع',
                            'title_ar' => 'تم رفض طلب الدفع',
                            'body' => "عذراً، تم رفض طلب الدفع بقيمة {$record->amount} ريال. سبب الرفض: {$data['notes']}",
                            'body_ar' => "عذراً، تم رفض طلب الدفع بقيمة {$record->amount} ريال. سبب الرفض: {$data['notes']}",
                            'data' => ['amount' => $record->amount, 'payment_id' => $record->id],
                        ]);

                        Mail::to($record->user->email)->send(new PaymentRejected($record, $data['notes']));

                        ActivityLog::log(
                            'payment.rejected',
                            "تم رفض طلب دفع #{$record->id}: {$data['notes']}",
                            PaymentRequest::class,
                            $record->id,
                            ['amount' => $record->amount, 'reason' => $data['notes']]
                        );

                        Notification::make()->warning()->title('تم رفض طلب الدفع.')->send();
                    })
                    ->visible(fn (PaymentRequest $record) => $record->status === 'pending_manual_review'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRequests::route('/'),
        ];
    }
}
