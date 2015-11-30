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

    public function getBattle($battle_id)
    {	
        return response()->json(battle['battle' => Battle::findOrFail($battle_id)]);
    }

	
    public function getTrending(Request $request)
    {
        return response()->json(battle['battle' => Battle::scopeTrending($request)]);
    }
	
    public function getOpenVoting(Request $request)
    {
        return response()->json(battle['battle' => Battle::scopeOpenVoting($request)]);
    }
	
    public function getCompleted()
    {
        return response()->json(battle['battle' => Battle::scopeCompleted($request)]);
    }
	
    public function getOpen()
    {
        return response()->json(battle['openBattle' => OpenBattle::findAll()]);
    }
	
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
