<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
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
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|unique:posts|max:10'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
           $user = App\User::findOrFail($id);
           $profile = array();
           $profile['id'] = $user['id'];
            $profile['username'] = $user['name'];
            $profile['Profile_picture'] = $user['picture'];
            $profile['city'] = $user['city'];
            $profile['about_me'] = $user['about_me'];
            $profile['rapper'] = $user['rapper'];
            $profile['wins'] = $user['wins'];
            $profile['defeats'] = $user['defeats'];

            return $profile;
        }
    }

    public function postProfileInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|varchar|max:255',
            'about_me' => 'required|varchar|max:512'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
              
            $user['city'] = $request['city'];
            $user['about_me'] = $request['about_me'];
           
            $user->save();
        }
    }

    public function postProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picture' => 'required|byte'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
                   
            $user['picture'] = $request['picture'];        

            $user->save();
        }
    }
    public function getSettings()
    {
        if(isset(Auth::user()->user_id))
        {
            $id = Auth::user()->user_id;

            $user = App\User::findOrFail($id);
            $settings = array();
            $settings['rapper'] = $user['rapper'];
            $settings['notifications'] = $user['notifications'];

            return $settings;
        }
        else
            return false;
    }
    public function postSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            if(isset(Auth::user()->user_id))
            {
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
            $user['rapper'] = $request['rapper']; 
            $user['notifications'] = $request['notifications'];
            return $user->save();
            }
        }
    }
    public function postUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
                   
            $user['name'] = $request['username'];        

            $user->save();
        }
    }
    public function postPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|varchar|max:60',
            'password' => 'required|vatchar|max:60'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
            $id = Auth::user()->user_id;
            $user = App\User::findOrFail($id);
                   
            if($request['old_password'] == $user['password']){
                $user['password'] = Hash::make($request['password']);
                $user->save();        
            }
        }    
        
    }
}
