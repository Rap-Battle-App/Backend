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


    public function getProfile(Request $id)
    {
        //for some reason the validator returns strange errors

        //$this->validate = Validator::make($request->all(), [
        //    'id' => 'required|integer|unique:posts|max:10'
        //]);

            
            $user = User::findOrFail($id);

            $battleStatistics = array();
            $battleStatistics->wins = $user->wins;
            $battleStatistics->defeats = $user->defeats;

            $profile = array();
            $profile->id = $user->id;
            $profile->username = $user->name;
            $profile->profile_picture = $user->picture;
            $profile->city = $user->city;
            $profile->about_me = $user->about_me;
            $profile->statisctics = $battleStatistics;
            $profile->rapper = $user->rapper;
            

            return $profile;
        
    }

    public function postProfileInformation(Request $request)
    {
        $this->validate = Validator::make($request->all(), [
            'city' => /*required|*/'string|max:255',       // just made is as a comment now so in case it is needed it can be uncommented fast.
            'about_me' => /*required|*/'string|max:512'
        ]);

        
            /*$id = Auth::user()->id;
            $user = findOrFail($id);*/
            $user = $request->user();

            $user->city = $request->city;
            $user->about_me = $request->about_me;
           
            $user->save();
        
    }

    public function postProfilePicture(Request $request)
    {
        $this->validate = Validator::make($request->all(), [
            'picture' => /*required*/'|byte'
        ]);

        
            /*$id = Auth::user()->id;
            $user = findOrFail($id);*/
            $user = $request->user();

            $user->picture = $request->picture;        

            $user->save();
        
    }
    public function getSettings()
    {
        
            $id = Auth::user()->id;

            $user = findOrFail($id);
            $settings = array();
            $settings->rapper = $user->rapper;
            $settings->notifications= $user->notifications;

            return $settings;
        
    }
    public function postSettings(Request $request)
    {
        $this->validate = Validator::make($request->all(), [
            'rapper' => 'required|boolean',
            'notifications' => 'required|boolean'
        ]);

        
            
            /*$id = Auth::user()->id;
            $user = findOrFail($id);*/
            $user = $request->user();
            $user->rapper = $request->rapper; 
            $user->notifications = $request->notifications;
            $user->save();
            
        
    }
    public function postUsername(Request $request)
    {
        $this->validate = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);

        
            /*$id = Auth::user()->id;
            $user = findOrFail($id);*/
            $user = $request->user();

            $user->name = $request->username;        

            $user->save();
        
    }
    public function postPassword(Request $request)
    {
        $this->validate = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',       //
            'password' => 'required|string|min:6'
        ]);

            $options = array('cost' => 15);
            /*$id = Auth::user()->id;
            $user = findOrFail($id);*/
            $user = $request->user();
            $request->old_password = password_hash($request->old_password,PASSWORD_BCRYPT,$options);//old --> Hash::make($request->old_password);       
            if($request->old_password == $user->password){
                $user->password = password_hash($request->password,PASSWORD_BCRYPT,$options); // old --> Hash::make($request->password);
                $user->save();        
            }
           
        
    }
}
