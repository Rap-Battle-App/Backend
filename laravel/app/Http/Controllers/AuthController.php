<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Validator;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

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

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $request)
    {
        return Validator::make($request, [
            'name' => 'required|max:255|unique:users',   
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6'   
        ]);
    }


    protected function postLogin(array $request)
    {
        Auth::login($request->user);

        return Auth::user()->id;
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $request
     * @return UserId
    */

    public function postRegister(array $request)
    {
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);
        $user->save();

        return $user->$id;
    }


    public function getLogout()
    {
        Auth::logout(Auth::user()->id);
    }

    public function getId()
    {
        //return Auth::user()->id;
        return $this->middleware('auth', ['except' => ['getId']]);
    }


}
