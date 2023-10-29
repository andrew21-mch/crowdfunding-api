<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\CustomResetPasswordEmail;
use App\Models\User;
use DB;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class AuthController extends ApiBaseController
{
    // User Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $user->sendEmailVerificationNotification();

        return $this->successResponse('User registered successfully. Please check your email for verification.', null, Response::HTTP_CREATED);
    }

    // User Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Invalid credentials',null, Response::HTTP_UNAUTHORIZED);
        }

        $user = $request->user();
        $token = $user->createToken('authToken')->plainTextToken;

        return $this->successResponse(['token' => $token], null, Response::HTTP_OK);
    }

    // User Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('User logged out successfully', null, Response::HTTP_OK);
    }

    // Get Authenticated User
    public function getUser(Request $request)
    {
        return $this->successResponse($request->user(), null, Response::HTTP_OK);
    }

    // Update User Profile
    public function updateUser(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return $this->successResponse('User profile updated successfully', null, Response::HTTP_OK);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return $this->successResponse('Password changed successfully', null, Response::HTTP_OK);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = $this->broker()->getUser($request->only('email'));

        if (!$user) {
            return $this->errorResponse('Invalid user email', Response::HTTP_BAD_REQUEST);
        }

        $token = Str::random(8); // Generate a random token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Send the custom email
        $emailData = [
            'token' => $token,
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new CustomResetPasswordEmail($emailData));

        return $this->successResponse(['token' => $token], null, Response::HTTP_OK);
    }

    public function resetPassword(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return $this->errorResponse('Invalid password reset token', Response::HTTP_BAD_REQUEST);
        }

        if (!Hash::check($token, $reset->token)) {
            return $this->errorResponse('Invalid password reset token', Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return $this->successResponse('Password reset successfully', null, Response::HTTP_OK);
    }

    protected function broker()
    {
        return Password::broker();
    }

    // Verify Email
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!$user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return $this->successResponse('Email verified successfully', null, Response::HTTP_OK);
    }

    // Resend Email Verification Link
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse('User already has a verified email', Response::HTTP_BAD_REQUEST);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse('Email verification link resent', null, Response::HTTP_OK);
    }
}