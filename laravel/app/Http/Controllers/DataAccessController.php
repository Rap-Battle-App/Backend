<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use Illuminate\Http\Request;

class DataAccessController extends Controller
{
    // returns the profile picture of a user identified by id
    public function getPicture($id)
    {
        $pathToFile = Auth::getUser($id)->picture;
        return response()->download($pathToFile);
    }
	
    // returns the video of a battle identified by id
    public function getVideo($id)
    {
        $pathToFile = Battle::getBattle($id)->video;
        return response()->download($pathToFile);
    }	
}
