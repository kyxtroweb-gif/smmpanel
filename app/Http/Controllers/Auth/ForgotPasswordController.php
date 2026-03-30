<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getEmail()
    {
        return view('auth.forgot-password');
    }

    public function postEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], ['email.exists' => 'We could not find a user with that email address.']);

        $user = User::where('email', $request->email)->first();

        if (!$user->is_active) {
            return back()->withInput()->withErrors(['email' => 'This account has been suspended.']);
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => bcrypt($token), 'created_at' => now()]
        );

        // In a real application you would dispatch a Mailable here
        // Mail::to($request->email)->send(new ResetPasswordMail($token));

        return back()->with('status', 'Password reset link generated! In demo mode, use the provided link to reset.')
            ->with('demo_token', $token)
            ->with('demo_email', $request->email);
    }
}
