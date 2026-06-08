<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>شهادة إتمام - {{ $course->title_ar ?? $course->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;900&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .certificate {
            width: 800px;
            max-width: 100%;
            background: #fff;
            border: 12px solid #1e3a5f;
            border-image: repeating-linear-gradient(45deg, #1e3a5f, #1e3a5f 20px, #f59e0b 20px, #f59e0b 40px) 12;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            position: relative;
        }
        .certificate::before {
            content: '';
            position: absolute;
            top: 12px; left: 12px; right: 12px; bottom: 12px;
            border: 2px solid #d1d5db;
            pointer-events: none;
        }
        h1 { font-size: 42px; font-weight: 900; color: #1e3a5f; margin-bottom: 8px; letter-spacing: 2px; }
        .subtitle { font-size: 18px; color: #6b7280; margin-bottom: 30px; }
        .seal {
            width: 100px; height: 100px;
            border: 4px solid #f59e0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 36px;
            color: #f59e0b;
            background: #fffbeb;
        }
        .statement { font-size: 20px; color: #374151; line-height: 2; margin-bottom: 25px; }
        .course-name { font-size: 32px; font-weight: 900; color: #1e3a5f; margin: 10px 0; }
        .student-name { font-size: 28px; font-weight: 700; color: #f59e0b; }
        .details { display: flex; justify-content: center; gap: 40px; margin: 30px 0; flex-wrap: wrap; }
        .detail-item { text-align: center; }
        .detail-label { font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; }
        .detail-value { font-size: 16px; font-weight: 700; color: #374151; }
        .code { font-size: 14px; color: #9ca3af; direction: ltr; margin-top: 20px; }
        .print-btn {
            display: inline-block; margin-top: 30px; padding: 12px 32px;
            background: #1e3a5f; color: #fff; border: none; border-radius: 8px;
            font-size: 16px; font-family: 'Tajawal', sans-serif; cursor: pointer;
        }
        .print-btn:hover { background: #2d4a7a; }
        @media print {
            body { background: #fff; padding: 0; }
            .certificate { box-shadow: none; border-width: 8px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="seal">&#x1F3C6;</div>
        <h1>شهادة إتمام</h1>
        <p class="subtitle">منصة المئة للتعليم</p>

        <p class="statement">تشهد منصة المئة للتعليم بأن</p>
        <p class="student-name">{{ auth()->user()->name }}</p>
        <p class="statement">قد أتم بنجاح دورة</p>
        <p class="course-name">{{ $course->title_ar ?? $course->title }}</p>

        <div class="details">
            <div class="detail-item">
                <div class="detail-label">تاريخ الإتمام</div>
                <div class="detail-value">{{ $completion->completed_at->locale('ar')->translatedFormat('j F Y') }}</div>
            </div>
            @if($course->duration_minutes)
            <div class="detail-item">
                <div class="detail-label">مدة الدورة</div>
                <div class="detail-value">{{ $course->duration_minutes }} دقيقة</div>
            </div>
            @endif
            <div class="detail-item">
                <div class="detail-label">المستوى</div>
                <div class="detail-value">{{ $course->difficulty_level ?: 'عام' }}</div>
            </div>
        </div>

        <p class="code">رمز التحقق: {{ $completion->certificate_code }}</p>
        <button class="print-btn" onclick="window.print()">طباعة الشهادة</button>
    </div>
</body>
</html>
