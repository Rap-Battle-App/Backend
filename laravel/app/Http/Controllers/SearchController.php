<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SearchController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling search requests and returns
    | a list of all matching users.
    |
    */

    /**
     * Handle a search request and return all matching users.
     *
     * @param  \Illumnate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSearch(Request $request)
    {
        $this->validate($request, [
            'search_string' => 'required|string'
        ]);

        $users = User::namedLike($request->input('search_string'))->get();

        $profiles = $users->map(function($user, $key) {
            return $user->profilePreview();
        });

        return response()->json(['profiles' => $profiles]);
    }
}
