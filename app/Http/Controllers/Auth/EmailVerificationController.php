<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('signed')->only(['verifyEmail']);
    }

    /**
     * Send verification email.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'message' => 'Your email address has been verified!',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => 'The verification email was sent!',
        ]);
    }

    /**
     * Verify email address.
     *
     * @param \Illuminate\Foundation\Auth\EmailVerificationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        if (!$request->user()->hasVerifiedEmail()) {
            $request->fulfill();
        }

        return response()->json([
            'status' => true,
            'message' => 'Your email address has been verified!',
        ]);
    }
}
