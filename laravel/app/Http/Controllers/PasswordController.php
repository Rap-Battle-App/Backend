<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Send a reset link to the given user.
     * Overwritten function of ResetsPassword trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject(trans('password-recovery.email-title'));
        });
    }

    /**
     * Reset the given user's password.
     * Overwritten function of ResetsPassword trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only(
            'email', 'password', 'token'
        );
        // confirmation value is used by Password::reset
        $credentials['password_confirmation'] = $credentials['password'];

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::INVALID_USER:
                return response()->json(['email' => [trans($response)]], 422);
            case Password::INVALID_TOKEN:
                return response()->json(['token' => [trans($response)]], 422);
        }
    }
}
