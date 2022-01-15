<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PasswordResettingController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send password reset email.
     *
     * @param \App\Http\Requests\Auth\ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->safe()->only('email'),
        );

        if ($status === Password::INVALID_USER) {
            throw new NotFoundHttpException('Email does not exist!');
        }
        elseif ($status === Password::RESET_THROTTLED) {
            throw new UnprocessableEntityHttpException('Please wait a moment!');
        }
        else if ($status !== Password::RESET_LINK_SENT) {
            throw new HttpException(500, 'Unable to send password reset email!');
        }

        return response()->json([
            'status' => true,
            'message' => 'Password reset email was sent!',
        ]);
    }

    /**
     * Reset password.
     *
     * @param \App\Http\Requests\Auth\ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->safe()->only('email', 'token', 'password'),
            function ($player, $password) {
                $player->forceFill([
                    'password' => Hash::make($password),
                ]);

                $player->save();

                // Delete old PATs
                $this->authService->revokeAllPATs($player);
            },
        );

        if ($status === Password::INVALID_TOKEN) {
            throw new AccessDeniedHttpException('Invalid token!');
        }
        if ($status !== Password::PASSWORD_RESET) {
            throw new HttpException(500, 'Unable to reset password!');
        }

        return response()->json([
            'status' => true,
            'status' => 'The password has been reset!',
        ]);
    }
}
