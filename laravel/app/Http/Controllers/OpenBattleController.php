<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpenBattle;
use App\Models\Battle;
use App\Models\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Storage;

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
   
    // ============================================
    // Logic for video file naming and moving
    // (only needed by this class)
 
    /**
     * Adds a path to a filename depending on the first two letters of the filename
     *
     * @return suggested (not checked) path and filename relative to the filesystem
     */
    private static function filePath($filename)
    {
        // create path for sub-directory to decrease file count per directory
        $path = substr($filename, 0, 2);
        return $path . '/' . $filename;
    }

    /**
     * Zeropadding for numbers
     */
    private static function zeropad($num, $lim)
    {
        return (strlen($num) >= $lim) ? $num : OpenBattleController::zeropad("0" . $num, $lim);
    }

    /**
     * PHP seems not to reliably support 64bit integers on all systems
     * This method takes a 64bit hex-coded uint from a string and increments it
     *
     * @return num + 1
     */
    private static function int64Increment($num)
    {
        // split string in 3 parts (<32 bits, integers are signed)
        $lowString = substr($num, 20, 12);
        $middleString = substr($num, 10, 10);
        $highString = substr($num, 0, 10);

        // convert strings to integer
        $lowInt = intval($lowString, 16);
        $middleInt = intval($middleString, 16);
        $highInt = intval($highString, 16);

        // increment
        $lowInt++;
        $lowStringInc = dechex($lowInt);
        if(strlen($lowStringInc) > 12) $middleInt++; // handle overflow
        $lowStringInc = OpenBattleController::zeropad($lowStringInc, 12);
        $lowStringInc = substr($lowStringInc, strlen($lowStringInc) - 12, 12); // cut (on overflow)

        $middleStringInc = dechex($middleInt);
        if(strlen($middleStringInc) > 10) $highInt++; // handle overflow
        $middleStringInc = OpenBattleController::zeropad($middleStringInc, 10);
        $middleStringInc = substr($middleStringInc, strlen($middleStringInc) - 10, 10); // cut (on overflow)

        $highStringInc = dechex($highInt);
        $highStringInc = OpenBattleController::zeropad($highStringInc, 10);
        $highStringInc = substr($highStringInc, strlen($highStringInc) - 10, 10); // cut (on overflow)

        // concatenate strings
        return $highStringInc . $middleStringInc . $lowStringInc;
    }

    /**
     * Renames and moves a video file to it's right position in the filesystem
     *
     * @return new path and filename relative to the filesystem
     */
    private static function moveVideoFile($infile)
    {
        // get hash of video file
        $hash = hash_file('md5', $infile);
        // create path from filename
        $outfile = OpenBattleController::filePath($hash);
        // don't move file, if it already has the right path and name
        if(strcmp($infile, $outfile) == 0) return $infile;

        // collision avoidance
        while(Storage::disk('videos')->has($outfile)){
            // increment hash since filename already exists
            $outfile = OpenBattleController::filePath(OpenBattleController::int64Increment(++$hash));
        }

        // move video file
        if(Storage::disk('videos')->has($infile)){ // file is already in disk 'videos' -> simply move it
            Storage::disk('videos')->move($infile, $outfile);
        } else { // file is not in disk 'videos' -> write it there
            $stream = fopen($infile, 'r+');
            Storage::disk('videos')->writeStream($outfile, $stream);
            fclose($stream);
            unlink($infile);
        }

        return $outfile;
    }

}
