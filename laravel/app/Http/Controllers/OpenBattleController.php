<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\open_battles;
use App\battles;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class OpenBattleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function getBattle($id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|max:10'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
           $user_id = Auth::user()->user_id;
           $battle = App\open_battles::findOrFail($id); 
           $Profile = App\User::findOrFail($battle['rapper2_id']);

           $ProfileView = array();
           $ProfileView['user_id'] = $Profile['id'];
           $ProfileView['username'] = $Profile['name'];
           $ProfileView['profile_picture'] = $Profile['picture'];

            $Phase1Info = array();
            $Phase1Info['round1_url'] = $battle['rapper1_round1'];

            $Phase2Info = array();
            $Phase2Info['round1_url'] = $battle['rapper1_round1'];
            $Phase2Info['beat_id'] = $battle['beat1_id'];
            $Phase2Info['opponent_round1_url'] = $battle['rapper2_round1'];
            $Phase2Info['round2_url'] = $battle['rapper1_round2'];


           $PhaseInfo = array();
           $ProfileView['time_left'] = $battle['phase_start'];
           $ProfileView['Phase1Info'] = $Phase1Info;
           $ProfileView['Phase2Info'] = $Phase2Info;

           $OpenBattle = array();
           $OpenBattle['id'] = $battle['id']; 
           $OpenBattle['opponent'] = $ProfileView;
           $OpenBattle['phase'] = $battle['phase'];
           $OpenBattle['info'] = $PhaseInfo;

            return $OpenBattle;
        }
    }
    public function postRound(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'beat_id' => 'required|integer' , 
            'video' => 'required|byte'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
          $id = Auth::user()->id;
           $op_battle = App\open_battles::findOrFail($id);
           $battle = App\battles::findOrFail($id);            
           $battleRound = array();
           $op_battle['beat1_id'] = $request['beat_id'];

           $battle['video'] = $request['video'];
           $battle['rapper1_id'] = $op_battle['rapper1_id'];
           $battle['rapper2_id'] = $op_battle['rapper2_id'];
           $battle['votes_rapper1'] = 0;
           $battle['votes_rapper2'] = 0;

           
           $op_battle->save();
           $battle->save();
        }
    }

    
    
    
}
