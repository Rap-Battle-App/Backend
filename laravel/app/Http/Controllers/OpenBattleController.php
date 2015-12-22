<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Storage;
use App\Models\OpenBattle;
use App\Models\Battle;
use App\Models\User;

class OpenBattleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Open Battle Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling the data of the open battles
    | and closing the entry, processing video and adding the final result of
    | battle to the battle table
    |
    */

    /**
     * Handle a search request and return the open battle which is required to be
     * updated.
     *
     * @param  integer  id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBattle($id)
    {
        $battle = OpenBattle::findOrFail($id);
        $user = Auth::user();

        if ($user == $battle->rapper1) {
            $battle = $battle->toJSON_Rapper1();
        } elseif ($user == $battle->rapper2) {
            $battle = $battle->toJSON_Rapper2();
        } else {
            return response('Unauthorized.', 401);
        }

        return response()->json($battle);
    }

    public function postRound(Request $request, $id)
    {
        $this->validate($request, [
            'beat_id' => 'required|integer',
            'video' => 'required' // add mime validation rule?
        ]);

        $battle = OpenBattle::findOrFail($id);
        $user = $request->user();

        $rapperNumber = 0;
        if ($user == $battle->rapper1) {
            $rapperNumber = 1;
        } elseif ($user == $battle->rapper2) {
            $rapperNumber = 2;
        } else {
            return response('Unauthorized.', 401);
        }

        //TODO convert video/fire events

        // Name of the column the video needs to be saved in
        $videoColumn = 'rapper'.$rapperNumber.'_round'.$this->phase;
        // Name of the video
        $videoName = $battle->id.'_'.$videoColumn;
        $video = $request->file('video');
        // Name of video on the disk
        $videoFilename = $videoName.'.'.$video->guessExtension();
        Storage::disk('videos')->put($videoFilename, file_get_contents($video->getRealPath()));

        $battle[$videoColumn] = $videoFilename;
        // Set beat id
        if ($battle->phase == 1) {
            $battle['beat'.$rapperNumber.'_id'] = $request->input('beat_id');
            // Go to phase 2 if both 1st rounds are uploaded
            if ($battle->hasFirstRounds()) {
                $battle->phase++;
            }
        } elseif ($battle->phase == 2 && $battle->hasAllRounds()) {
            //TODO battle done, concat video, create battle, delete open battle - maybe need to do this when conversion is done
        }

        $battle->save();
    }

    /**
     * Handle a posted round functionalilites.
     * Checks the phase of round upload the beat according to the phase
     * Checks the user, and according uploads the video in the filesystem
     * If all the videos are there, calls the video concatenation function and uploads the
     * openBattle entry with the final video to the battle table and closes the entry here
     *
     * @param  \Illumnate\Http\Request  Beat_id
     * @param  \Illumnate\Http\Request  Video
     */
    /*public function postRound(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'beat_id' => 'required|integer' ,
            'video' => 'required|byte'
        ]);


           $id = Auth::user()->id;
           $out_link =  OpenBattleController::moveVideoFile($request->video);
           //$in_link = '/path/to/inputvideo1';

           $event = new App\Events\VideoWasUploaded($out_link, [$out_link]);      //because video will be converted in the filesystem
                                                                                  // and replaced there only after conversion

           Event::fire($event);


          // Phase system
          // 1 - 4
          // 1 means 1 video in file system
          // 4 means 4 videos in filesystem, time to concatenate and move

           $op_battle = OpenBattle::findOrFail($id);
           $battleRound = array();
           if($op_battle->phase < 2 && $op_battle->phase > 0)
           {
              $op_battle->beat1_id = $request->beat_id;
              if($op_battle->rapper1_round1 == NULL)
                  $op_battle->rapper1_round1 = $out_link;
              else
                  $op_battle->rapper2_round1 = $out_link;

              $op_battle->phase++;      //phase changed
           }
           else if($op_battle->phase < 4)
           {
              $op_battle->beat2_id = $request->beat_id;

              if($op_battle->rapper1_round2 == NULL)
                  $op_battle->rapper1_round2 = $out_link;
              else
                  $op_battle->rapper2_round2 = $out_link;

              $op_battle->phase++;      //phase changed
           }
           $op_battle->save();
           if($op_battle->phase == 4)   //both rounds are done and needs to close this open_battle entry and
                                        //concatenate the video and add the final result to the battle table
           {
              $battle = new Battle;
              $battle->rapper1_id = $op_battle->rapper1_id;
              $battle->rapper2_id = $op_battle->rapper2_id;

              //converted and concatenated all the videos in the place of first video
              $event = new App\Events\VideoWasUploaded($rapper1_round1, [$rapper1_round1 , $rapper2_round1 , $rapper1_round2 , $rapper2_round2]);
              Event::fire($event);

              //link to the concatenated file given to video in the battle table

              $battle->video = $rapper1_round1;
              $battle->votes_rapper1 = 0;
              $battle->votes_rapper2 = 0;

              //new entry in the battle created

              $battle->save();

              //entry from the open battle removed

              $op_battle->delete();
           }



    }*/

    // ============================================
    // Logic for video file naming and moving
    // (only needed by this class)

    /**
     * Adds a path to a filename depending on the first two letters of the filename
     *
     * @return suggested (not checked) path and filename relative to the filesystem
     */
    /*private static function filePath($filename)
    {
        // create path for sub-directory to decrease file count per directory
        $path = substr($filename, 0, 2);
        return $path . '/' . $filename;
    }*/

    /**
     * Zeropadding for numbers
     */
    /*private static function zeropad($num, $lim)
    {
        return (strlen($num) >= $lim) ? $num : OpenBattleController::zeropad("0" . $num, $lim);
    }*/

    /**
     * PHP seems not to reliably support 64bit integers on all systems
     * This method takes a 64bit hex-coded uint from a string and increments it
     *
     * @return num + 1
     */
    /*private static function int64Increment($num)
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
    }*/

    /**
     * Renames and moves a video file to it's right position in the filesystem
     *
     * @return new path and filename relative to the filesystem
     */
    /*private static function moveVideoFile($infile)
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
    }*/
}
