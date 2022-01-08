<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:sanctum')->only(['signOut']);
    }

    /**
     * Create an account.
     *
     * @param  \App\Http\Requests\Auth\SignUpRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(SignUpRequest $request)
    {
        $validated = $request->validated();

        $player = $this->authService->createPlayer($validated);
        $token = $this->authService->createPAT($player);

        return response()->json([
            'data' => $player,
            'token' => $token,
        ], 201);
    }

    /**
     * Sign in to system.
     *
     * @param  \App\Http\Requests\Auth\SignInRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(SignInRequest $request)
    {
        $validated = $request->validated();

        $player = $this->authService->authenticate($validated);
        $token = $this->authService->createPAT($player);

        return response()->json([
            'token' => $token,
        ]);
    }

    /**
     * Sign out of system.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {
        $ok = $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => $ok,
        ], 201);
    }
}
