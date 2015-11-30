<?php
namespace App\Http\Controllers;



use App\Model\Battle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;




class BattleController extends Controller
{

    public function __construct($AppName, IRequest $request)
    {
        parent::__construct($AppName, $request);
    }

    //return a single Battle Object identified by id
    public function getBattle($battle_id)
    {	
        return response()->json(battle['battle' => Battle::findOrFail($battle_id)]);
    }

	
    //return an Array of Battles with the most votes
    public function getTrending(Request $request)
    {
        return response()->json(battle['battle' => Battle::scopeTrending($request)]);
    }
	
    //return an Array of Battles that are still open for voting
    public function getOpenVoting(Request $request)
    {
        return response()->json(battle['battle' => Battle::scopeOpenVoting($request)]);
    }
	
    //return an Array of Battles that are no longer open for voting
    public function getCompleted()
    {
        return response()->json(battle['battle' => Battle::scopeCompleted($request)]);
    }
	
    //return an Array of all openBattles
    public function getOpen()
    {
        return response()->json(battle['openBattle' => OpenBattle::findAll()]);
    }
	
    //increase the votes of a single rapper in one battle identified by id 
    public function postVote(Request $request, $battle_id)
    {	
        $validator = Validator::make($request->all(), [
            'rapper_number' => 'required|boolean'   
        ]);
		
        $user_id = Auth::user()->id;
        $battle=getBattle($battle_id);
				
        //build new vote
        $vote = new Vote;
        $vote->user_id = $user_id;
        $vote->battle_id = $battle->id;
        $vote->rapper_number = $request->rapper_number;
				
        $vote->save();
        // update vote counter
        if($vote->rapper_number == 0){
            $battle->votes_rapper1++;
        } else {
            $battle->votes_rapper2++;
        }
		
        // save vote count in battle
        $battle->save();
			
    }

}
