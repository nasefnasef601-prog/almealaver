# تشغيل MySQL محلي لمنصة المئة

هذا الإعداد يجعل التطوير المحلي قريب من Hostinger، بدون استخدام قاعدة الإنتاج.

## التشغيل

1. شغل قاعدة البيانات:

```powershell
docker compose up -d mysql
```

2. انسخ ملف البيئة المحلي مرة واحدة:

```powershell
Copy-Item .env.mysql.example .env.mysql
```

3. أنشئ مفتاح التطبيق المحلي:

```powershell
php artisan --env=mysql key:generate
```

4. جهز الجداول والبيانات التجريبية:

```powershell
php artisan --env=mysql migrate:fresh --seed
```

5. شغل السيرفر المحلي:

```powershell
php -d max_execution_time=0 artisan --env=mysql serve --host=127.0.0.1 --port=8016
```

## حسابات الدخول التجريبية

- admin@demo.local / Demo123456!
- student@demo.local / Demo123456!
- teacher@demo.local / Demo123456!
- supervisor@demo.local / Demo123456!
- parent@demo.local / Demo123456!

## ملاحظات

- هذه القاعدة محلية فقط: `almeaa_local`.
- المنفذ المحلي هو `3307` حتى لا يتعارض مع أي MySQL آخر.
- قبل أي رفع إلى GitHub أو Hostinger: اختبر محليا، ثم ارفع دفعة واحدة بعد النجاح.
