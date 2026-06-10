<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addStudent')
                ->label('إضافة طالب')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم الطالب')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('phone')
                        ->label('الجوال'),
                    Forms\Components\TextInput::make('password')
                        ->label('كلمة المرور')
                        ->helperText('اتركها فارغة لاستخدام Demo123456!')
                        ->password(),
                ])
                ->action(function (array $data): void {
                    $user = User::firstOrCreate(
                        ['email' => strtolower(trim($data['email']))],
                        [
                            'name' => $data['name'],
                            'phone' => $data['phone'] ?? null,
                            'password' => Hash::make($data['password'] ?: 'Demo123456!'),
                            'role' => 'student',
                            'school_id' => $this->record->school_id,
                            'is_active' => true,
                        ]
                    );

                    if (method_exists($user, 'assignRole') && !$user->hasRole('student')) {
                        $user->assignRole('student');
                    }

                    $user->update([
                        'name' => $data['name'],
                        'phone' => $data['phone'] ?? $user->phone,
                        'school_id' => $this->record->school_id ?: $user->school_id,
                    ]);

                    $this->attachStudent($user->id);

                    Notification::make()
                        ->success()
                        ->title('تم إضافة الطالب للفصل')
                        ->send();
                }),
            Action::make('importStudentsCsv')
                ->label('استيراد طلاب CSV')
                ->icon('heroicon-o-document-arrow-up')
                ->form([
                    Forms\Components\Textarea::make('csv')
                        ->label('بيانات CSV')
                        ->helperText('انسخ من Excel بصيغة CSV. الأعمدة: name,email,password,phone')
                        ->rows(10)
                        ->placeholder("name,email,password,phone\nStudent One,student1@example.com,Demo123456!,0500000000")
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $result = $this->importStudentsFromCsv((string) $data['csv']);

                    Notification::make()
                        ->success()
                        ->title("تم الاستيراد: {$result['created']} جديد، {$result['attached']} مضاف للفصل، {$result['skipped']} متجاهل")
                        ->send();
                }),
            Action::make('downloadCsvTemplate')
                ->label('قالب CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => response()->streamDownload(function (): void {
                    echo "name,email,password,phone\n";
                    echo "Student One,student1@example.com,Demo123456!,0500000000\n";
                }, 'students-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8'])),
        ];
    }

    private function importStudentsFromCsv(string $csv): array
    {
        $lines = preg_split('/\R/u', trim($csv)) ?: [];
        $header = null;
        $created = 0;
        $attached = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);
            if ($header === null) {
                $header = array_map(fn ($value) => Str::of((string) $value)->trim()->lower()->toString(), $row);
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), ''));
            $name = trim((string) ($data['name'] ?? ''));
            $email = strtolower(trim((string) ($data['email'] ?? '')));

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
                    'password' => Hash::make(trim((string) ($data['password'] ?? '')) ?: 'Demo123456!'),
                    'role' => 'student',
                    'school_id' => $this->record->school_id,
                    'is_active' => true,
                ]);
                $created++;
            } else {
                $user->update([
                    'name' => $name,
                    'phone' => trim((string) ($data['phone'] ?? '')) ?: $user->phone,
                    'school_id' => $this->record->school_id ?: $user->school_id,
                ]);
            }

            if (method_exists($user, 'assignRole') && !$user->hasRole('student')) {
                $user->assignRole('student');
            }

            $this->attachStudent($user->id);
            $attached++;
        }

        return compact('created', 'attached', 'skipped');
    }

    private function attachStudent(int $userId): void
    {
        DB::table('group_user')->updateOrInsert(
            ['group_id' => $this->record->id, 'user_id' => $userId],
            ['role' => 'student', 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
