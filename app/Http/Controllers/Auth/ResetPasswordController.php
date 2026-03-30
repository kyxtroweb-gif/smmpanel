<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getReset(Request $request, $token = null)
    {
        $email = $request->query('email');

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function postReset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = $request->email;
        $token = $request->token;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'User not found.']);
        }

        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['token' => 'This password reset token is invalid.']);
        }

        $tokenAge = time() - strtotime($resetRecord->created_at);
        if ($tokenAge > 3600) { // 60 minutes expiry
            return back()->withInput($request->only('email'))
                ->withErrors(['token' => 'This password reset token has expired.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_reset',
            'description' => 'User reset their password',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset! Please login with your new password.');
    }
}
