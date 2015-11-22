<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function postSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_string' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }
       else{
           $user = App\User::findOrFail($search_string);            //might be a buggy line
           $profilePreview = array();
           $profilePreview['user_id'] = $user['id']; 
           $profilePreview['username'] = $user['name'];
           $profilePreview['profile_picture'] = $user['picture'];
            return $profilePreview;
        }
    }

    
    
    
}
