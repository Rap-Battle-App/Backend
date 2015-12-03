<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use Illuminate\Http\Request;

class DataAccessController extends Controller
{
    // returns the picture identified by id
    public function getPicture($id)
    {
        $pathToFile = Storage::get($id);
        return response()->download($pathToFile);
    }
	
    // returns the video of a battle identified by id
    public function getVideo($id)
    {
        $pathToFile = Storage::get($id);
        return response()->download($pathToFile);
    }	
}
