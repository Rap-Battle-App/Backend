<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpenBattle;
use App\Models\Battle;
use App\Models\User;
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

        
            $user_id = Auth::user()->id;
            $battle = App\open_battles::findOrFail($id);
           
            if($user_id == $battle->rapper2_id) 
                $profile = App\User::findOrFail($battle->rapper2_id);
            else
                $profile = App\User::findOrFail($battle->rapper1_id);

            $profileView = array();
            $profileView->user_id = $profile->id;
            $profileView->username= $profile->name;
            $profileView->profile_picture = $profile->picture;

            $phase1Info = array();
            $phase1Info->round1_url = $battle->rapper1_round1;

            $phase2Info = array();
            $phase2Info->round1_url = $battle->rapper1_round1;
            $phase2Info->beat_id = $battle->beat1_id;
            $phase2Info->opponent_round1_url = $battle->rapper2_round1;
            $phase2Info->round2_url= $battle->rapper1_round2;


            $phaseInfo = array();
            $profileView->time_left = $battle->phase_start;      //need some work here // is it finished ? O.o
            $phaseInfo->phase1Info = $phase1Info;
            $phaseInfo->phase2Info = $phase2Info;

            $openBattle = array();
            $openBattle->id = $battle->id; 
            $openBattle->opponent = $profileView;
            $openBattle->phase = $battle->phase;
            $openBattle->info= $phaseInfo;

            return $openBattle;
        
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
           
           $event = new App\Events\VideoWasUploaded($out_link, [$in_link]);
           Event::fire($event);

          

           $op_battle = OpenBattle::findOrFail($id);            
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
           
           if($op_battle->phase == 3)   //both rounds are done and needs to close this open_battle entry and 
                                        //concatenate the video and add the final result to the battle table
           {
              $battle = new Battle;
              $battle->rapper1_id = $op_battle->rapper1_id;
              $battle->rapper2_id = $op_battle->rapper2_id;
              //concatenating video
              $event = new App\Events\VideoWasUploaded($out_link, [$in_link]);
              Event::fire($event);
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
