<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Mail\WelcomeMail;
use App\Mail\VerifiyEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{

    /**
     * Register New User
     * @return JSONResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'otp' => Str::random(8),
                'password' => Hash::make($request->password),
            ]);

            if ($user) {
                Mail::to($user->email)->send(new WelcomeMail($user));
                Mail::to($user->email)->send(new VerifiyEmail($user));
                return ResponseHelper::success(message: 'User has been registered successfully, Please check your inbox to verify your email!', data: $user, statusCode: 201);
            }
            return ResponseHelper::error(message: 'Unable to Register user ooh! Please try again.', statusCode: 400);
        }
        catch (Exception $e) {
            Log::error('Unable to Register User : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to Register user! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Function : Login User
     */
    public function login(LoginRequest $request,)
    {
        try {

            // If credentials are incorrect
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return ResponseHelper::error(message: 'Unable to login due to invalid credentials.', statusCode: 400);
            }

            $user = Auth::user();

            // Check if the user's email is verified
            if (!$user->is_verified) {
                // Send verification email
                Mail::to($user->email)->send(new VerifiyEmail($user));

                return ResponseHelper::error(message: 'Your email is not verified. A verification email has been sent to your email address.', statusCode: 403);
            }

            if (!$user->is_active) {
                $user->is_active = true;
                $user->save();
            }

            // Create API Token
            $token = $user->createToken('My API Token')->plainTextToken;

            $authUser = [
                'user' => $user,
                'token' => $token
            ];

            return ResponseHelper::success(message: 'You are logged in successfully!', data: $authUser, statusCode: 200);
        }
        catch (Exception $e) {
            Log::error('Unable to Login User : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to Login! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function profile()
    {
        try {
            $user = Auth::user();

            if ($user) {
                return ResponseHelper::success(message: 'User profile fetched successfully!', data: $user, statusCode: 200);
            }

            return ResponseHelper::error(message: 'Unable to fetch user data due to invalid token.', statusCode: 400);
        }
        catch (Exception $e) {
            Log::error('Unable to Fetch User Profile : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to Fetch User Profile! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

        /**
     * Function : User Logout
     * @param NA
     * @return JSONResponse
     */
    public function logout() {
        try {
            $user = Auth::user();

            if ($user) {
                $user->currentAccessToken()->delete();
                return ResponseHelper::success(message: 'User logged out successfully!', statusCode: 200);
            }

            return ResponseHelper::error(message: 'Unable to logout due to invalid token.', statusCode: 400);
        }
        catch (Exception $e) {
            Log::error('Unable to Logout due to some exception : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to Logout due to some exception! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Function :Verify User Email
     */

    public function verifyEmail(Request $request)
    {
        try {
            $otp = $request->input('otp');

            if (!$otp) {
                return ResponseHelper::error(message: 'OTP is required.', statusCode: 400);
            }

            $user = User::where('otp', $otp)->first();

            if (!$user) {
                return ResponseHelper::error(message: 'Invalid OTP.', statusCode: 400);
            }

            $user->is_verified = true;
            $user->otp = null; // Clear OTP after verification
            $user->save();

            return ResponseHelper::success(message: 'Email verified successfully!', data: $user, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Verify Email: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to verify email! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return ResponseHelper::error(message: 'User not found.', statusCode: 400);
            }

            $token = Str::random(60);
            $newUser = PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                ['token' => $token]
            );

            Mail::to($user->email)->send(new PasswordResetMail($token, $user));

            return ResponseHelper::success(message: 'Password reset link sent', data: $newUser, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Send reset link: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to send reset link! Please try again.' . $e->getMessage(), statusCode: 500);
        }

    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'password' => 'required|min:5|max:25|confirmed',
            ]);

            $passwordReset = PasswordResetToken::where('token', $request->token)->first();

            if (!$passwordReset) {
                return ResponseHelper::error(message: 'Invalid token.', statusCode: 400);
            }

            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return ResponseHelper::error(message: 'User not found.', statusCode: 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            $passwordReset->delete();

            return ResponseHelper::success(message: 'Password has been reset', data: $user, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Reset Password: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to reset password! Please try again.' . $e->getMessage(), statusCode: 500);
        }

    }

}
