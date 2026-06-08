<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CheckHealth extends Command
{
    protected $signature = 'check:health';
    protected $description = 'تحقق من صحة المشروع';

    public function handle(): int
    {
        $this->info('=== فحص المشروع ===');
        $this->newLine();

        // PHP version
        $this->line("PHP: " . PHP_VERSION);
        $this->line("Laravel: " . app()->version());
        $this->line("DB: " . config('database.default'));
        $this->line("Environment: " . app()->environment());
        $this->line("Routes: " . count(app('router')->getRoutes()));
        $this->newLine();

        // Database connection
        try {
            DB::connection()->getPdo();
            $this->info('✓ DB متصل');
        } catch (\Throwable $e) {
            $this->error('✗ DB: ' . $e->getMessage());
        }

        // Tables exist
        $requiredTables = ['users', 'courses', 'quizzes', 'questions', 'roles', 'permissions'];
        foreach ($requiredTables as $table) {
            try {
                if (DB::schema()->hasTable($table)) {
                    $this->info("  ✓ جدول $table موجود");
                } else {
                    $this->warn("  ✗ جدول $table مفقود");
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ $table: " . $e->getMessage());
            }
        }
        $this->newLine();

        // Storage link
        if (file_exists(public_path('storage'))) {
            $this->info('✓ storage:link موجود');
        } else {
            $this->warn('✗ storage:link غير موجود — شغّل: php artisan storage:link');
        }

        // .env exists
        if (file_exists(base_path('.env'))) {
            $this->info('✓ .env موجود');
        } else {
            $this->warn('✗ .env مفقود');
        }

        // Key generated
        if (config('app.key')) {
            $this->info('✓ APP_KEY موجود');
        } else {
            $this->warn('✗ APP_KEY غير موجود — شغّل: php artisan key:generate');
        }

        $this->newLine();
        $this->info('=== الفحص اكتمل ===');

        return Command::SUCCESS;
    }
}
