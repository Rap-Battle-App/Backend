<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Battle;
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
        //can't just return battle as json - need to construct ProfilePreviews for rappers and Voting (see API Diagramm)
        // use map
        //return response()->json(Battle::findOrFail($id));

	/*
		$collection = collect([1, 2, 3, 4, 5]);

		$multiplied = $collection->map(function ($item, $key) {
			return $item * 2;
		});

		$multiplied->all();

	// [2, 4, 6, 8, 10]
	*/

    /*
        //json request returrns array
        $collection = collect(response()->json(Battle::findOrFail($id)));

        $format = $collection->map(function ($item, $key) {
            if($item=='rapper1_id')
            {
                return 'rapper1'=> getProfile($item);
            }
            if($item=='rapper2_id')
            {
                return 'rapper2'=> getProfile($item);
            }
            if($item=='votes_rapper1')
            {
                return Voting; //?
            }
            if($item=='votes_rapper2')
            {
                return;
            }
            return $item * 2;
        });

		$format->all();

	// [2, 4, 6, 8, 10]
	*/



        return Battle::findOrFail($id);
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
