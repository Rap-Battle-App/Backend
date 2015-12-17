<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\User;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    private $username = 'username';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['getLogout', 'getId']]);
        $this->middleware('auth', ['only' => 'getId']);
    }

    /**
     * Redirect the user after determining they are locked out.
     * Overwritten function of ThrottlesLogins trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = app(RateLimiter::class)->availableIn(
            $request->input('username').$request->ip()
        );

        // Send a JsonResponse containing the lockout message.
        return response()->json(['username' => [$this->getLockoutErrorMessage($seconds)]], 422);
    }

    /**
    * Return the users ID after successful authentication.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Models\User  $user
    * @return \Illuminate\Http\JsonResponse
    */
    protected function authenticated(Request $request, $user)
    {
        return response()->json(['user_id' => $user->id]);
    }

    /**
     * Handle a login request to the application.
     * Overwritten funtion of AuthenticatesUsers trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        // Second parameter is set to true because app users should always be remembered.
        if (Auth::attempt($credentials, true)) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login. Of course, when this user surpasses their maximum number of attempts
        // they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        // Send a JsonResponse containing the error message.
        return response()->json(['username' => [$this->getFailedLoginMessage()]], 422);
    }

    /**
     * Handle a registration request for the application.
     * Overwritten function of RegistersUsers trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRegister(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);

        Auth::login($user);

        return response()->json(['user_id' => $user->id]);
    }

    /**
     * Log the user out of the application.
     * Overwritten function of AuthenticatesUsers trait.
     *
     * @return void
     */
    public function getLogout()
    {
        Auth::logout();
    }

    /**
    * Get the authenticated users ID.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getId()
    {
        return response()->json(['user_id' => Auth::user()->id]);
    }
}
