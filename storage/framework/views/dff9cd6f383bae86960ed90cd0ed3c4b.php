<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - غير مصرح</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f9fafb; color: #1f2937; padding: 1rem; }
        .card { text-align: center; background: white; padding: 3rem 4rem; border-radius: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f3f4f6; max-width: 480px; }
        .code { font-size: 7rem; font-weight: 900; background: linear-gradient(135deg, #dc2626, #b91c1c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; }
        .title { font-size: 1.5rem; font-weight: 800; margin: 0.5rem 0 0.75rem; }
        .desc { color: #6b7280; font-size: 1rem; margin-bottom: 2rem; line-height: 1.6; }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; background: #dc2626; color: white; padding: 0.75rem 2rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: all 0.2s; }
        .btn-primary:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
        .btn-ghost { display: inline-flex; align-items: center; gap: 0.5rem; background: #f3f4f6; color: #374151; padding: 0.75rem 1.5rem; border-radius: 0.75rem; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; }
        .btn-ghost:hover { background: #e5e7eb; }
        .icon { width: 4rem; height: 4rem; margin: 0 auto 1.5rem; background: #fef2f2; border-radius: 1rem; display: flex; align-items: center; justify-content: center; }
        .icon svg { width: 2rem; height: 2rem; color: #dc2626; stroke-width: 1.5; }
        @media (max-width: 480px) { .card { padding: 2rem 1.5rem; } .code { font-size: 5rem; } }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/></svg>
        </div>
        <div class="code">403</div>
        <p class="title">غير مصرح بالوصول</p>
        <p class="desc">ليس لديك صلاحية الوصول إلى هذه الصفحة. يرجى تسجيل الدخول أو التواصل مع الدعم.</p>
        <div class="actions">
            <a href="<?php echo e(url('/login')); ?>" class="btn-primary">تسجيل الدخول ←</a>
            <a href="<?php echo e(url('/')); ?>" class="btn-ghost">الرئيسية</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\almeaa lave\almeaa-laravel-new\resources\views/errors/403.blade.php ENDPATH**/ ?>