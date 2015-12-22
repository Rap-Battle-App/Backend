<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Log;
use App\Models\User;
use App\Models\BattleRequest;
use App\Models\OpenBattle;

class BattleRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Battle Request Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling search requests and returns
    | a list of all matching users.
    |
    */

    /**
     * Get all requests by and to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequests()
    {
        $user = Auth::user();

        $requests = $user->battleRequestsChallenger()->get();
        $requestsOpponent = $user->battleRequestsChallenged()->get();

        $requests = $requests->map(function($request, $key) {
            return $request->toJSON_Challenger();
        });
        $requestsOpponent = $requestsOpponent->map(function($request, $key) {
            return $request->toJSON_Challenged();
        });

        return response()->json(['requests' => $requests, 'requests_opponent' => $requestsOpponent]);
    }

    /**
     * Send a battle request to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void|\Illuminate\Http\JsonResponse
     */
    public function postRequest(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer'
        ]);

        $opponent = User::findOrFail($request->input('user_id'));

        $user = $request->user();

        if (!$opponent->rapper) {
            return response()->json(['user_id' => [trans('rap-battle.no-rapper')]], 422);
        }
        if ($opponent->hasBattleRequestAgainst($user)) {
            return response()->json(['user_id' => [trans('rap-battle.request-already-exists')]], 422);
        }
        if ($opponent->hasOpenBattleAgainst($user)) {
            return response()->json(['user_id' => [trans('rap-battle.battle-already-exists')]], 422);
        }

        $battleRequest = new BattleRequest;

        $battleRequest->challenger()->associate($user);
        $battleRequest->challenged()->associate($opponent);

        $battleRequest->save();
    }

    /**
     * Accept or decline a battle request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $id
     * @return void|\Illuminate\Http\Response
     */
    public function postAnswer(Request $request, $id)
    {
        $this->validate($request, [
            'accepted' => 'required|boolean'
        ]);

        $battleRequest = BattleRequest::findOrFail($id);

        // Check if authenticated user is challenged user
        if ($battleRequest->challenged == $request->user()) {
            if ($request->input('accepted')) {
                $battle = new OpenBattle;
                $battle->start($battleRequest->challenger, $battleRequest->challenged);
                $battle->save();
            }
            $battleRequest->delete();
        } else {
            return response('Unauthorized.', 401);
        }
    }

    /**
     * Find a random opponent for the authenticated user based on rating.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandomOpponent()
    {
        $user = Auth::user();
        // Number of possible opponents = Number of rappers - 1 (Authenticated user)
        //     - Number of open battles of authenticated user
        //     - Number of battle requests of authenticated user
        $possibleOpponentCount = User::rapper()->count() - 1 - $user->openBattles()->count() - $user->battleRequests()->count();

        $opponent = null;

        // This is faster because there are no unecessary iterations.
        if ($possibleOpponentCount == 1) {
            // There is only one possible opponent.
            $opponent = User::validOpponentFor($user)->first();
        } elseif ($possibleOpponentCount > 1) {
            // Increase the search range exponentially to make sure an opponent is found.
            $exponentialBase = 2;
            $rating = $user->rating;
            for ($i = 0; is_null($opponent); $i++) {
                $range = pow($exponentialBase, $i);
                $minRating = $rating - $range;
                $maxRating = $rating + $range;

                $possibleOpponents = User::validOpponentFor($user)->ratedBetween($minRating, $maxRating)->get();
                if (!$possibleOpponents->isEmpty()) {
                    $opponent = $possibleOpponents->random();
                    // Log for later optimizing
                    Log::info('Random opponent found.', ['possible opponents' => $possibleOpponentCount, 'exponential base' => $exponentialBase, 'iterations' => $i]);
                }
            }
        }

        if (!is_null($opponent)) {
            $battleRequest = new BattleRequest;

            $battleRequest->challenger()->associate($user);
            $battleRequest->challenged()->associate($opponent);

            $battleRequest->save();

            $opponent = $opponent->profilePreview();
        }

        return response()->json(['opponent' => $opponent]);
    }
}
