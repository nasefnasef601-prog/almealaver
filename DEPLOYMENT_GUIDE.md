# دليل النشر — منصة المئة التعليمية

## المتطلبات
- PHP 8.3+
- MySQL 8.0+ (أو MariaDB 10.6+)
- Composer
- Node.js 20+ (لبناء assets)
- Nginx أو Apache
- Redis (اختياري للـ cache)

## خطوات النشر

### 1. تهيئة البيئة
```bash
cd /var/www/almeaa

cp .env.example .env
# تحرير .env — ضبط بيانات MySQL

APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=almeaa
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. تثبيت الحزم
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 3. تشغيل الهجرات
```bash
php artisan migrate --seed --force
```

### 4. تحسين الأداء
```bash
php artisan optimize
php artisan storage:link
```

### 5. إعداد Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/almeaa/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6. إعداد cron للمهام المجدولة
```bash
* * * * * cd /var/www/almeaa && php artisan schedule:run >> /dev/null 2>&1
```

## النقل من SQLite للتطوير إلى MySQL للإنتاج
1. تصدير SQLite: `sqlite3 database/database.sqlite .dump > dump.sql`
2. تعديل dump.sql ليتوافق مع MySQL
3. استيراد إلى MySQL
4. تحديث `.env` مع بيانات MySQL

## Common Issues

### صفحة بيضاء (500)
- تأكد من `APP_DEBUG=true` في `.env` مؤقتاً لرؤية الخطأ
- تحقق من أذونات `storage/` و `bootstrap/cache/`
- أعد تشغيل PHP-FPM

### الخطوط RTL لا تظهر
```bash
php artisan filament:optimize
```

### Class "App\Providers\Path" not found
- هذا الخطأ ظهر سابقاً في التطوير وتم إصلاحه
- تأكد من `use App\Models\Path;` في `app/Providers/AppServiceProvider.php`

### Filament v5 Namespace Issues
- جميع الموارد تستخدم `Filament\Actions\*` (ليس `Filament\Tables\Actions\*`)
- إذا ظهر خطأ "Class not found" في Filament، تأكد من تحديث الـ imports
