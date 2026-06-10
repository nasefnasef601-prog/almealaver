<?php

namespace App\Http\Controllers;

use App\Services\AccessCodeRedemptionService;
use Illuminate\Http\Request;
use RuntimeException;

class AccessCodeController extends Controller
{
    public function show()
    {
        return view('student.access-code');
    }

    public function redeem(Request $request, AccessCodeRedemptionService $service)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:80'],
        ]);

        try {
            $service->redeem($data['code'], $request->user());
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('student.dashboard', ['tab' => 'my-courses'])
            ->with('success', 'تم تفعيل كود الدخول وإضافة الصلاحيات إلى حسابك.');
    }
}
