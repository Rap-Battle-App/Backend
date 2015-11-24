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

        
           $user_id = Auth::user()->user_id;
           $battle = App\open_battles::findOrFail($id);
           
           if($user_id == $battle->rapper2_id) 
           $Profile = App\User::findOrFail($battle->rapper2_id);
           else
           $Profile = App\User::findOrFail($battle->rapper1_id);

           $ProfileView = array();
           $ProfileView->user_id = $Profile->id;
           $ProfileView->username= $Profile->name;
           $ProfileView->profile_picture = $Profile->picture;

            $Phase1Info = array();
            $Phase1Info->round1_url = $battle->rapper1_round1;

            $Phase2Info = array();
            $Phase2Info->round1_url = $battle->rapper1_round1;
            $Phase2Info->beat_id = $battle->beat1_id;
            $Phase2Info->opponent_round1_url = $battle->rapper2_round1;
            $Phase2Info->round2_url= $battle->rapper1_round2;


           $PhaseInfo = array();
           $ProfileView->time_left = $battle->phase_start;      //need some work here
           $PhaseInfo->Phase1Info = $Phase1Info;
           $PhaseInfo->Phase2Info = $Phase2Info;

           $OpenBattle = array();
           $OpenBattle->id = $battle->id; 
           $OpenBattle->opponent = $ProfileView;
           $OpenBattle->phase = $battle->phase;
           $OpenBattle->info= $PhaseInfo;

            return $OpenBattle;
        
    }
    public function postRound(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'beat_id' => 'required|integer' , 
            'video' => 'required|byte'
        ]);

        
           $id = Auth::user()->id;
           $out_link = '/path/to/outputvideo.mp4';
           $in_link = '/path/to/inputvideo1';
           //$out_link = upload; sample
           $event = new App\Events\VideoWasUploaded($out_link, [$in_link]);
           Event::fire($event);

           $link//get link

           $op_battle = App\open_battles::findOrFail($id);            
           $battleRound = array();
           if($op_battle->phase == 1)
           {
              $op_battle->beat1_id = $request->beat_id;
              if($op_battle->rapper1_round1 == NULL)
              $op_battle->rapper1_round1 = $out_link;
              else
              $op_battle->rapper2_round1 = $out_link; 
           }
           else
           {
              $op_battle->beat2_id = $request->beat_id;

              if($op_battle->rapper1_round2 == NULL)
              $op_battle->rapper1_round2 = $out_link;
              else
              $op_battle->rapper2_round2 = $out_link;
           }
           $op_battle->phase++;
           if($op_battle->phase == 3)   //both rounds are done and needs to close this open_battle entry and concatenate the video and add the final result to the battle table
           {
            $battle = new battles;
            $battle->rapper1_id = $op_battle->rapper1_id;
            $battle->rapper2_id = $op_battle->rapper2_id;
            //concatenating video

            //uploading video
            $link = upload;
            //final link
            $battle->video = $link;
            $battle->votes_rapper1 = 0;
            $battle->votes_rapper2 = 0;
           }
           
           $op_battle->save();
        
    }

    
    
    
}
