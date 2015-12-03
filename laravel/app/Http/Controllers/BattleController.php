<?php

namespace App\Http\Controllers;


use App\Models\Battle;
use Illuminate\Http\Request;
use App\Http\Requests;





class BattleController extends Controller
{

    //return a single Battle Object identified by id
    public function getBattle($id) 
    {	
        //can't just return battle as json - need to construct ProfilePreviews for rappers and Voting (see API Diagramm)
        // use map
        return response()->json(Battle::findOrFail($id));
    }

	
    //return an Array of Battles with the most votes
    public function getTrending(Request $request)
    {
        return response()->json(Battle::trending()->paginate($request->input('amount')));
    }
	
    //return an Array of Battles that are still open for voting
    public function getOpenVoting(Request $request)
    {
        //todo: check if request contains user id (if yes return only battles by that user)
        return response()->json(Battle::openVoting()->paginate($request->input('amount')));
    }
	
    //return all Battles that are no longer open for voting for the current user
    public function getCompleted()
    {
        //todo: check if request contains user id (if yes return only battles by that user)
        return response()->json(Auth::user()->battles()->completed()->paginate($request->input('amount')));
    }
	
    //return an Array of all openBattles for the current user
    public function getOpen()
    {
        
        return response()->json(Auth::user()->battles()->open());
    }
	
    //increase the votes of a single rapper in one battle identified by id 
    public function postVote(Request $request, $battle_id)
    {	
        $validator = $this->validate($request->all(), [
            'rapper_number' => 'required|boolean'   
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
