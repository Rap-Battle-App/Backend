<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function postSearch(Request $request)
    {
        $this->$validate = Validator::make($request->all(), [
            'search_string' => 'required|string'
        ]);

            $user = findOrFail($search_string);            //might be a buggy line 
            $profilePreview = array();
            $profilePreview['user_id'] = $user->id; 
            $profilePreview['username'] = $user->name;
            $profilePreview['profile_picture'] = $user->picture;
            return $profilePreview;
        
    }

    
    
    
}
