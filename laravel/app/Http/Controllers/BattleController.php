<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Models\Battle;
use App\Models\OpenBattle;
use App\Models\Vote;

class BattleController extends Controller
{

    //return a single Battle Object identified by id
    public function getBattle($id) 
    {
        $battle = Battle::findOrFail($id);
        $user = Auth::user();
        if(is_null($user)) return response('Unauthorized', 401);

        $profilePreview1 = array(
            'user_id' => $battle->rapper1_id,
            'username' => User::findOrFail($battle->rapper1_id)->username,
            'profile_picture' => User::findOrFail($battle->rapper1_id)->getProfilePicture(),
    	);

        $profilePreview2 = array(
            'user_id' => $battle->rapper2_id,
            'username' => User::findOrFail($battle->rapper2_id)->username,
            'profile_picture' => User::findOrFail($battle->rapper2_id)->getProfilePicture(),
	    );

        $userVote = $user->votes()->where('battle_id', $battle->id)->get()->first();
        $votedFor = ($userVote == null ? null : $userVote->rapper_number);

        $voting = array(
            'votes_rapper1' => $battle->votes_rapper1,
            'votes_rapper2' => $battle->votes_rapper2,
            'voted_for' => $votedFor,
            'open' => $battle->isOpenVoting(),
	    );

        $battleInfo = array(
            'id' => $battle->id,
            'video_url' => $battle->getVideoURL(),
            'rapper1' => $profilePreview1,
            'rapper2' => $profilePreview2,
            'voting' => $voting,
        );
        
        return response()->json($battleInfo);
    }

    // create a BattleOverview array
    private function createBattleOverview($battles){
        $data = array();
        foreach($battles as $battle){
            $data[] = ['battle_id' => $battle->id,
                    'rapper1' => $battle->rapper1()->first()->profilePreview(),
                    'rapper2' => $battle->rapper2()->first()->profilePreview()];
        }
        return $data;
    }
	
    //return an Array of Battles with the most votes
    public function getTrending(Request $request)
    {
        $trending = Battle::completed()->trending()->get();
        $data = $this->createBattleOverview($trending);

        $amount = $request->input('amount', 15);
        $page = $request->input('page', 1);
        // slice requested data set for paginator
        $slicedData = array_slice($data, $amount * ($page - 1), $amount);

        return new LengthAwarePaginator($slicedData, count($data), $amount, $page);
    }
	
    //return an Array of Battles that are still open for voting
    public function getOpenVoting(Request $request)
    {
        if($request->has('user_id')){
            // get battles by user
            $user_id = $request->input('user_id');
            $battles = User::find($user_id)->battles()->openVoting();
        } else {
            // get all battles
            $battles = Battle::openVoting();
        }

        $data = $this->createBattleOverview($battles->get());

        $amount = $request->input('amount', 15);
        $page = $request->input('page', 1);
        // slice requested data set for paginator
        $slicedData = array_slice($data, $amount * ($page - 1), $amount);

        return new LengthAwarePaginator($slicedData, count($data), $amount, $page);
    }
	
    //return all Battles that are no longer open for voting for the current user
    public function getCompleted(Request $request)
    {
        if($request->has('user_id')){
            // get battles by user
            $user_id = $request->input('user_id');
            $battles = User::find($user_id)->battles()->completed();
        } else {
            // get all battles
            $battles = Battle::completed();
        }
 
        $data = $this->createBattleOverview($battles->get());

        $amount = $request->input('amount', 15);
        $page = $request->input('page', 1);
        // slice requested data set for paginator
        $slicedData = array_slice($data, $amount * ($page - 1), $amount);

        return new LengthAwarePaginator($slicedData, count($data), $amount, $page);
    }
	
    //return an Array of all openBattles for the current user
    public function getOpen(Request $request)
    {
        // get open battles by user
        $user = Auth::user();
        if(is_null($user)){
            $battles = collect();
        } else {
            $battles = $user->openBattles()->open();
        }
 
        $data = $this->createBattleOverview($battles->get());

        $amount = $request->input('amount', 15);
        $page = $request->input('page', 1);
        // slice requested data set for paginator
        $slicedData = array_slice($data, $amount * ($page - 1), $amount);

        return new LengthAwarePaginator($slicedData, count($data), $amount, $page);
    }
	
    //increase the votes of a single rapper in one battle identified by id 
    public function postVote(Request $request, $battle_id)
    {	
        $this->validate($request, [
            'rapper_number' => 'required|integer'   
        ]);

        $user = Auth::user();
        $battle = Battle::find($battle_id);

        if(is_null($user)) return response('', 403); // forbidden
        if(is_null($battle)) return response('', 404); // Not found

        // check if battle is votable
        if(!$battle->isOpenVoting()) return response('', 405); // Method not allowed

        // don't let the user change a vote
        if($user->votes()->where('battle_id', $battle->id)->get()->first() == null){
            // build new vote
            $vote = new Vote;
            $vote->user_id = $user->id;
            $vote->battle_id = $battle->id;
            $vote->rapper_number = $request->input('rapper_number');

            // update vote counter
            if($vote->rapper_number == 0){
                $battle->votes_rapper1++;
            } else if($vote->rapper_number == 1){
                $battle->votes_rapper2++;
            } else return response('', 422); // Unprocessable Entity

            // save vote count in battle
            $vote->save();
            $battle->save();
        } else {
            return response('', 405); // Method not allowed
        }
    }

}
