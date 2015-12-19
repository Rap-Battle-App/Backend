<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Storage;
use Hash;
use App\Models\User;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for returning information about a single
    | user and handling requests to change that information.
    |
    */

    /**
     * Get a users profile.
     *
     * @param  integer  $id
     * @return array
     */
    public function getProfile($id)
    {
        $user = User::findOrFail($id);
        return $user->profile();
    }

    /**
     * Change the authenticated users profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postProfileInformation(Request $request)
    {
        $this->validate($request, [
            'city' => 'string|max:255',
            'about_me' => 'string'
        ]);

        $user = $request->user();

        $user->city = $request->input('city');
        $user->about_me = $request->input('about_me');

        $user->save();
    }

    /**
     * Change the authenticated users profile picture.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postProfilePicture(Request $request)
    {
        $this->validate($request, [
            'picture' => 'required|image'
        ]);

        $user = $request->user();

        // Save the picture to the backend storage.
        $picture = $request->file('picture');
        $picture_id = $user->id.'.'.$picture->guessExtension();
        Storage::disk('avatars')->put($picture_id, file_get_contents($picture->getRealPath()));

        $user->picture = $picture_id;

        $user->save();
    }

    /**
     * Get the authenticated users settings.
     *
     * @return array
     */
    public function getSettings()
    {
        $user = Auth::user();
        return $user->settings();
    }

    /**
     * Change the authenticated users settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postSettings(Request $request)
    {
        $this->validate($request, [
            'rapper' => 'required|boolean',
            'notifications' => 'required|boolean'
        ]);

        $user = $request->user();

        $user->rapper = $request->input('rapper');
        $user->notifications = $request->input('notifications');

        $user->save();
    }

    /**
     * Change the authenticated users username.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postUsername(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|unique:users'
        ]);

        $user = $request->user();

        $user->username = $request->input('username');

        $user->save();
    }

    /**
     * Change the authenticated users password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function postPassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        $user = $request->user();

        if (Hash::check($request->input('old_password'), $user->password)) {
            $user->password = bcrypt($request->input('password'));

            $user->save();
        } else {
            return response()->json(['old_password' => [trans('passwords.passwords-not-matching')]], 422);
        }
    }
}
