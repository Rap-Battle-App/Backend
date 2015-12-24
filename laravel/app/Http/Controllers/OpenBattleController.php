<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Storage;
use App\Models\OpenBattle;
use App\Models\Battle;
use App\Models\User;
use App\Events\VideoWasUploaded;
use App\Events\OpenBattleCompleted;

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
            //'video' => 'required' // add mime validation rule?
            'video' => 'required|mimes:mp4,mpg,3gp,h264,ogv,mov,webm,flv,wmv,mkv' // add mime validation rule?
        ]);

        // get battle and user instances
        $battle = OpenBattle::findOrFail($id);
        $user = $request->user();

        // get rapper number
        $rapperNumber = 0;
        if ($user == $battle->rapper1) {
            $rapperNumber = 1;
        } elseif ($user == $battle->rapper2) {
            $rapperNumber = 2;
        } else {
            return response('Unauthorized.', 401);
        }

        // TODO: check video file

        // Name of the column the video needs to be saved in
        $videoColumn = 'rapper'.$rapperNumber.'_round'.$battle->phase;
        // Name of the video
        $videoName = $battle->id.'_'.$videoColumn;
        $video = $request->file('video');
        // Name of video on the disk
        $videoFilename = $videoName.'.mp4'; // target file format: mp4
        $videoFilenameTmp = $videoName;
        Storage::disk('videos')->put($videoFilenameTmp, file_get_contents($video->getRealPath()));

        // convert video/fire events and delete temporary file
        $inFilePath = Storage::disk('videos')->getAdapter()->applyPathPrefix($videoFilenameTmp);
        $outFilePath = Storage::disk('videos')->getAdapter()->applyPathPrefix($videoFilename);
        \Event::fire(new VideoWasUploaded($outFilePath, $inFilePath, true));

        $battle[$videoColumn] = $videoFilename;
        // Set beat id
        if ($battle->phase == 1) {
            $battle['beat'.$rapperNumber.'_id'] = $request->input('beat_id');
            // Go to phase 2 if both 1st rounds are uploaded
            if ($battle->hasFirstRounds()) {
                $battle->phase++;
            }
        } else if ($battle->phase == 2 && $battle->hasAllRounds()) {
            // note: battle done, concat video, create battle, delete open battle - maybe need to do this when conversion is done
            // concatenates the videos, converts the OpenBattle to a Battle and deletes the old video files
            \Event::fire(new OpenBattleCompleted($battle));
        }

        $battle->save();
    }

}
