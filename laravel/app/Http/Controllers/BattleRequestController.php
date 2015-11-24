<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\open_battles;
use App\battles;
use App\battles_requests;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BattleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function getRequests()
    {
        $main_user = Auth::user()->user_id;
        $challengers = App::table('battles_requests')->where('challenged_id', $main_user)->get();
        
        $Requests = array();
        $i = 0;
        while(array_pop($challengers)){
        $profile = App\User::findOrFail($challengers->challenger_id);
        $ProfilePreview = array();
        $ProfilePreview->user_id = $profile->id;
        $ProfilePreview->user_name = $profile->name;
        $ProfilePreview->profile_picture = $profile->picture;
        $Request[$i]->id = $challengers->id;
        $Request[$i]->opponent = $ProfilePreview;
        $Request[$i]->date = $profile->created_at;
        $i++;  
        }
        $requests = $Requests;
        $opponent_requests = $Requests

        return $requests;

        
    }
    public function postRequests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer'
        ]);

        
           $battles_requests = new battles_requests;  

           $new_request->challenger_id = Auth::user()->user_id;
           $new_request->challenged_id = $request->user_id;         
           
           $battles_requests->save();
        
    }
    public function postAnswer(Request $request , $id)
    {
       /* $validator = Validator::make($request->all(), [
            'accepted' => 'required|boolean'
        ]);

        
           $battle = App\battles_requests::findOrFail($id);           
           $battle->accepted = $request->accepted;
           $battle->save();
        */
    }
    public function getRandomOpponent()
    {
           $user = Auth::user()->user_id;       
           $rand_coll = App::table('User')->whereBetween('ratings', array(($user->ratings) - 1 , ($user->ratings) + 1 ))->get();    //assumed the scale of 0-10 and selected the oppenents from +- 1 range

           $profile = $rand_coll->random()->first();      //selected one from the random collection
           
           $profilePreview = array();
           $profilePreview->user_id = $profile->id; 
           $profilePreview->username = $profile->name;
           $profilePreview->profile_picture = $profile->picture;    //needs the file system insertion methods 
          return $profilePreview;
        
    }

    
    
    
}
