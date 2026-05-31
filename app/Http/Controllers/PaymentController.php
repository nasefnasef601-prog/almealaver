<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function requestPurchase(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($course->is_free) {
            return back()->with('error', 'هذا الكورس مجاني ولا يحتاج إلى دفع.');
        }

        $user = auth()->user();

        $existing = PaymentRequest::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['pending_manual_review', 'approved'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'لديك طلب سابق لهذا الكورس قيد المراجعة.');
        }

        $existingGrant = $user->accessGrants()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($existingGrant) {
            return back()->with('error', 'لديك وصول بالفعل لهذا الكورس.');
        }

        $servedAmount = $course->price;

        PaymentRequest::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => 'bank_transfer',
            'amount' => $servedAmount,
            'currency' => 'SAR',
            'status' => 'pending_manual_review',
        ]);

        return back()->with('success', 'تم إرسال طلب الدفع. سيتم مراجعته من قبل الإدارة.');
    }
}
