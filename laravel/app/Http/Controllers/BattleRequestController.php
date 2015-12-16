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
    /*
    |--------------------------------------------------------------------------
    | Battle Request Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling requests of different rappers
    | and getting challenges and provoke challenges, random opponents.
    | basically regarding provoking battles.
    |
    */

    /**
     * Handle a get Request demand, returns all the challengers to the Rapper
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function getRequests()
    {
        $main_user = Auth::user()->user_id;
        $challengers = App::table('battles_requests')->where('challenged_id', $main_user)->get();
        
        $profiles = $challengers->map(function($challengers, $key) {
            return $challengers->profilePreview();
        });
        //$requests = $Requests;
        //$opponent_requests = $Requests

        return response()->json(['requests' => $profiles , 'opponent_requests' => $profiles]);
        
    }


    /**
     * Handle a challenge opponent request, create a new challenge in the requests table
     * with his id and opponent id 
     *
     * @param  \Illumnate\Http\Request  user_id
     * @return void
     */
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


    /**
     * Handle a battle request responses, if both agree, open a new entry in the OpenBattle table
     * otherwise, simply removes the entry from the battle requests table
     *
     * @param  \Illumnate\Http\id   [battle request table]
     * @param  \Illumnate\Http\Request  accepted   
     * @return void
     */
    public function postAnswer(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'accepted' => 'required|boolean'
        ]);

           $battle = battles_requests::findOrFail($id);
           //accepted response opens a new battle and removes the request from the battle request table
           if($request->accepted == TRUE){
            
            $op_battle = new open_battles;
            $op_battle->rapper1_id = $battle->challenger_id;
            $op_battle->rapper2_id = $battle->challenged_id;
            $op_battle->phase = 0;
            $op_battle->beat1_id = 0;
            $op_battle->rapper1_round1 = NULL;
            $op_battle->rapper2_round1 = NULL;
            $op_battle->beat2_id = 0;
            $op_battle->rapper1_round2 = NULL;
            $op_battle->rapper2_round2 = NULL;
            $op_battle->save();
           }
           
           //deletes the entry from the battel request table, whatever the response is         
           $battle->save();
        
    }


    /**
     * Handles the request for the random opponent demand,
     * returns a random opponent accoring to the ranking of the challenger  
     *
     * @param  void
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandomOpponent()
    {
           $user = Auth::user()->user_id;       
           $rand_coll = App::table('User')->whereBetween('ratings', array(($user->ratings) - 1 , ($user->ratings) + 1 ))->get();    //assumed the scale of 0-10 and selected the oppenents from +- 1 range

           $profile = $rand_coll->random()->first();      //selected one from the random collection
           
           $profiles = $profile->map(function($profile, $key) {
            return $profile->profilePreview();
           });
            
          return response()->json(['opponent' => $profiles]);
        
    }

    
    
    
}
