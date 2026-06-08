<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><title>إتمام دورة</title></head>
<body style="font-family: 'Tajawal', sans-serif; background: #f3f4f6; padding: 30px;">
    <div style="max-width: 500px; margin: auto; background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 25px;">
            <h1 style="font-size: 24px; color: #065f46; margin: 0;">&#x1F3C6; تهانينا! أكملت الدورة</h1>
        </div>
        <p style="font-size: 16px; color: #374151; line-height: 1.8;">مرحباً {{ $completion->user->name }}،</p>
        <p style="font-size: 14px; color: #6b7280; line-height: 1.8;">تهانينا! لقد أكملت دورة <strong style="color: #111827;">{{ $completion->course->title_ar ?? $completion->course->title }}</strong> بنجاح.</p>
        <p style="font-size: 14px; color: #6b7280; line-height: 1.8;">يمكنك الآن تحميل شهادتك من الرابط أدناه.</p>
        <a href="{{ route('student.certificate', $completion->course_id) }}"
           style="display: block; text-align: center; margin-top: 25px; padding: 14px; background: #059669; color: #fff; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px;">
            عرض الشهادة
        </a>
        <p style="font-size: 12px; color: #9ca3af; text-align: center; margin-top: 25px;">منصة المئة للتعليم</p>
    </div>
</body>
</html>
