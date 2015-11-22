<?php
/* REVIEW 22.11.15 by Daniel
    * General:
    * - stick to php coding guidelines
    * - test things by yourself as there are things which obviously dont work (wrong variables, undifined variables, ...)
    * - comment the methods
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// REVIEW: wrong model
use App\User;
// REVIEW: wrong class
use App\Http\Requests;
// REVIEW: dont need this because your in the same namespace
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function getProfile($id)
    {
        // REVIEW: get request - no need to validate
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|unique:posts|max:10'
        ]);
        // REVIEW: dont need to do this - laravel takes care of this
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: no need to namespace because the model is in use delcarations
           $user = App\User::findOrFail($id);
           $profile = array();
           // REVIEW: use $user->id for consistency with other controllers
           $profile['id'] = $user['id'];
            $profile['username'] = $user['name'];
            // REVIEW: case sensitive!
            $profile['Profile_picture'] = $user['picture'];
            $profile['city'] = $user['city'];
            $profile['about_me'] = $user['about_me'];
            $profile['rapper'] = $user['rapper'];
            // REVIEW: statistics object missing - see api diagramm
            $profile['wins'] = $user['wins'];
            $profile['defeats'] = $user['defeats'];

            return $profile;
        }
    }

    public function postProfileInformation(Request $request)
    {
        // REVIEW: use $this->validate for consitency
        // REVIEW: city and about_me are not required
        $validator = Validator::make($request->all(), [
            'city' => 'required|varchar|max:255',
            'about_me' => 'required|varchar|max:512'
        ]);
        // REVIEW: same as in previous method
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: Auth::user() already gives the user no need to search it again
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);

            $user['city'] = $request['city'];
            $user['about_me'] = $request['about_me'];

            $user->save();
        }
    }

    public function postProfilePicture(Request $request)
    {
        // REVIEW: see previous methods
        $validator = Validator::make($request->all(), [
            'picture' => 'required|byte'
        ]);
        // REVIEW: see previous methods
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: see previous method
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);

            // REVIEW: save picture in filesystem and set $user->picture to picture id
            $user['picture'] = $request['picture'];

            $user->save();
        }
    }
    public function getSettings()
    {
        // REVIEW: no need to check this - auth middleware makes sure user is logged in
        if(isset(Auth::user()->user_id))
        {
            // REVIEW: see previous methods
            $id = Auth::user()->user_id;

            $user = App\User::findOrFail($id);
            $settings = array();
            $settings['rapper'] = $user['rapper'];
            $settings['notifications'] = $user['notifications'];

            return $settings;
        }
        // REVIEW: no need to do this
        else
            return false;
    }
    public function postSettings(Request $request)
    {
        // REVIEW: use boolean instead of bit - bit is database type
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);
        // REVIEW: see previous methods
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: see previous method
            if(isset(Auth::user()->user_id))
            {
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
            $user['rapper'] = $request['rapper'];
            $user['notifications'] = $request['notifications'];
            // REVIEW: ? no return
            return $user->save();
            }
        }
    }
    public function postUsername(Request $request)
    {
        // REVIEW: wrong rules
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);
        // REVIEW: see previous methods
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: see previous methods
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);

            $user['name'] = $request['username'];

            $user->save();
        }
    }
    public function postPassword(Request $request)
    {
        // REVIEW: typo
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|varchar|max:60',
            'password' => 'required|vatchar|max:60'
        ]);
        // REVIEW: see previous methods
        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            // REVIEW: see previous methods
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
            // REVIEW: cant compare unhashed password to hashed password
            if($request['old_password'] == $user['password']){
                $user['password'] = Hash::make($request['password']);
                $user->save();
            }
        }

    }
}
