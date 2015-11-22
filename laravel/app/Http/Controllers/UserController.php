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

            
            $user = findOrFail($id);

            $BattleStatisctics = array();
            $BattleStatisctics->wins = $user->wins;
            $BattleStatisctics->defeats = $user->defeats;

            $Profile = array();
            $Profile->id = $user->id;
            $Profile->username = $user->name;
            $Profile->profile_picture = $user->picture;
            $Profile->city = $user->city;
            $Profile->about_me = $user->about_me;
            $Profile->statisctics = $BattleStatisctics;
            $Profile->rapper = $user->rapper;
            

            return $profile;
        
    }

    public function postProfileInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|varchar|max:255',
            'about_me' => 'required|varchar|max:512'
        ]);

        
            $id = Auth::user()->user_id;
            $user = findOrFail($id);
              
            $user->city = $request->city;
            $user->about_me = $request->about_me;
           
            $user->save();
        
    }

    public function postProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picture' => 'required|byte'
        ]);

        
            $id = Auth::user()->user_id;
            $user = findOrFail($id);
                   
            $user->picture = $request->picture;        

            $user->save();
        
    }
    public function getSettings()
    {
        
            $id = Auth::user()->user_id;

            $user = findOrFail($id);
            $settings = array();
            $settings->rapper = $user->rapper;
            $settings->notifications= $user->notifications;

            return $settings;
        
    }
    public function postSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|boolean',
            'notifications' => 'required|boolean'
        ]);

        
            
            $id = Auth::user()->user_id;
            $user = findOrFail($id);
            $user->rapper = $request->rapper; 
            $user->notifications = $request->notifications;
            $user->save();
            
        
    }
    public function postUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rapper' => 'required|bit',
            'notifications' => 'required|bit'
        ]);

        
            $id = Auth::user()->user_id;
            $user = findOrFail($id);
                   
            $user->name = $request->username;        

            $user->save();
        
    }
    public function postPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|varchar|max:60',
            'password' => 'required|vatchar|max:60'
        ]);

    
            $id = Auth::user()->user_id;
            $user = findOrFail($id);
            $request->old_password = Hash::make($request->old_password);       
            if($request->old_password == $user->password){
                $user->password = Hash::make($request->password);
                $user->save();        
            }
           
        
    }
}
