<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إيصال دفع - {{ $payment->course?->title_ar ?? $payment->course?->title ?? '' }}</title>
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
        .receipt {
            width: 600px;
            max-width: 100%;
            background: #fff;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 28px; font-weight: 900; color: #1e3a5f; }
        .header p { font-size: 14px; color: #6b7280; }
        .status {
            display: inline-block; padding: 6px 16px; border-radius: 20px;
            font-size: 14px; font-weight: 700;
        }
        .status.approved { background: #d1fae5; color: #065f46; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.rejected { background: #fee2e2; color: #991b1b; }
        .row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: 700; color: #111827; font-size: 14px; direction: ltr; }
        .total-row { border-bottom: none; border-top: 2px solid #1e3a5f; margin-top: 10px; padding-top: 16px; }
        .total-row .label { font-size: 18px; font-weight: 700; color: #1e3a5f; }
        .total-row .value { font-size: 22px; color: #f59e0b; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #9ca3af; }
        .print-btn {
            display: block; width: 100%; margin-top: 20px; padding: 14px;
            background: #1e3a5f; color: #fff; border: none; border-radius: 8px;
            font-size: 16px; font-family: 'Tajawal', sans-serif; cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>إيصال دفع</h1>
            <p>منصة المئة للتعليم</p>
        </div>

        <div class="row">
            <span class="label">رقم الإيصال</span>
            <span class="value">#{{ $payment->id }}</span>
        </div>
        <div class="row">
            <span class="label">الطالب</span>
            <span class="value">{{ $payment->user->name }}</span>
        </div>
        <div class="row">
            <span class="label">الدورة</span>
            <span class="value">{{ $payment->course?->title_ar ?? $payment->course?->title ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">المبلغ</span>
            <span class="value">{{ number_format($payment->amount, 2) }} {{ $payment->currency ?? 'ر.س' }}</span>
        </div>
        <div class="row">
            <span class="label">طريقة الدفع</span>
            <span class="value">{{ $payment->payment_method === 'bank_transfer' ? 'تحويل بنكي' : $payment->payment_method }}</span>
        </div>
        <div class="row">
            <span class="label">الحالة</span>
            <span class="status {{ $payment->status }}">{{ $payment->status === 'approved' ? 'مقبول' : ($payment->status === 'rejected' ? 'مرفوض' : 'قيد المراجعة') }}</span>
        </div>
        <div class="row">
            <span class="label">تاريخ الطلب</span>
            <span class="value">{{ $payment->created_at->locale('ar')->translatedFormat('j F Y, g:i A') }}</span>
        </div>
        @if($payment->approved_at)
        <div class="row">
            <span class="label">تاريخ القبول</span>
            <span class="value">{{ \Carbon\Carbon::parse($payment->approved_at)->locale('ar')->translatedFormat('j F Y, g:i A') }}</span>
        </div>
        @endif
        <div class="row total-row">
            <span class="label">المبلغ المدفوع</span>
            <span class="value">{{ number_format($payment->amount, 2) }} {{ $payment->currency ?? 'ر.س' }}</span>
        </div>

        <div class="footer">
            <p>شكراً لثقتكم بمنصة المئة للتعليم</p>
            <p style="margin-top:5px;font-size:10px;">هذا الإيصال معتمد وقانوني</p>
        </div>

        <button class="print-btn" onclick="window.print()">طباعة الإيصال</button>
    </div>
</body>
</html>
