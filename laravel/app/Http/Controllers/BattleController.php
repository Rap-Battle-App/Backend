<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Battle;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\OpenBattle;


//todo: correct api response format 

//use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{

    //return a single Battle Object identified by id
    public function getBattle($id) 
    {	
        $battleInfo = Battle::findOrFail($id);

        $profilePreview1 = array(
            'user_id' => $battleInfo->rapper1_id,
            'username' => User::findOrFail($battleInfo->rapper1_id)->username,
            'profile_picture' => User::findOrFail($battleInfo->rapper1_id)->picture,
	);

        $profilePreview2 = array(
            'user_id' => $battleInfo->rapper2_id,
            'username' => User::findOrFail($battleInfo->rapper2_id)->username,
            'profile_picture' => User::findOrFail($battleInfo->rapper2_id)->picture,
	);

        $voting = array(
            'votes_rapper1' => $battleInfo->votes_rapper1,
            'votes_rapper2' => $battleInfo->votes_rapper2,
            'voted_for' => '', // TODO: who did the user vote for?
            'open' => $battleInfo->isOpenVoting(),
	);

        $battle = array(
            'id' => $battleInfo->id,
            'video_url' => $battleInfo->video,
            'rapper1' => $profilePreview1,
            'rapper2' => $profilePreview2,
            'voting' => $voting,
        );
        
        return $battle;
    }
	
    //return an Array of Battles with the most votes
    public function getTrending(Request $request)
    {
        return Battle::trending()->paginate($request->input('amount'));
    }
	
    //return an Array of Battles that are still open for voting
    public function getOpenVoting(Request $request)
    {
        //check if request contains user id (if yes return only battles by that user)
        if($request->input('user_id'))
        {
            return Battle::openVoting()->where(function($query){
                $query->where('rapper1_id', '==', $request->input('user_id'))
                      ->orWhere('rapper2_id', '==', $request->input('user_id'));
        });

        }
        else
        {
            return Battle::openVoting()->paginate($request->input('amount'));
        }
    }
	
    //return all Battles that are no longer open for voting for the current user
    public function getCompleted()
    {
        //check if request contains user id (if yes return only battles by that user)
        //return response()->json(Auth::user()->battles()->completed()->paginate($request->input('amount')));
        /*
        if($request->input('user_id'))
        {
            return response()->json(Battle::completed()->where(function($query){
                $query->where('rapper1_id', '==', $request->input('user_id'))
                      ->orWhere('rapper2_id', '==', $request->input('user_id'));
        }));

        }
        else
        {
            //return Battle::completed()->paginate($request->input('amount'));
            //return response()->json(Battle::completed()->paginate($request->input('amount')));
            return response()->json(Battle::completed());
        }
*/
        return response()->json(Battle::completed());
        //return response()->json(Battle::completed()->paginate($request->input('amount')));

        //return response()->json(Battle::completed());
        //return Battle::completed();   //routing error for unknown reasons
        
    }
	
    //return an Array of all openBattles for the current user
    public function getOpen()
    {
        //todo: check if request contains user id (if yes return only battles by that user)
        //return response()->json(Auth::user()->battles()->open());
        //return response()->json(Battle::open());
        //return response()->json(OpenBattle::all());

        /*return OpenBattle::all()->where(function($query){
                $query->where('rapper1_id', '==', $request->input('user_id'))
                      ->orWhere('rapper2_id', '==', $request->input('user_id'));
        });*/
        return OpenBattle::all();
    }
	
    //increase the votes of a single rapper in one battle identified by id 
    public function postVote(Request $request, $battle_id)
    {	
        $validator = $this->validate($request->all(), [
            'rapper_number' => 'required|integer'   
        ]);
		
        $user_id = Auth::user()->id;
        $battle=Battle::find($battle_id);
				
        //build new vote
        $vote = new Vote;
        $vote->user_id = $user_id;
        $vote->battle_id = $battle->id;
        $vote->rapper_number = $request->input('rapper_number');
				
        $vote->save();
        // update vote counter
        if($vote->rapper_number == 1){
            $battle->votes_rapper1++;
        } else {
            $battle->votes_rapper2++;
        }
		
        // save vote count in battle
        $battle->save();
			
    }

}
