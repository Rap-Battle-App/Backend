<?php

namespace App\Http\Controllers;

use Storage;

class DataAccessController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Data Access Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for giving users access to pictures and
    | videos stored in the storage.
    |
    */

    /**
     * Get a picture.
     *
     * @param  string  $file
     * @return \Illuminate\Http\Response
     */
    public function getPicture($file)
    {
        $disk = Storage::disk('avatars');
        $picture = $disk->get($file);
        $type = $disk->getMimetype($file);
        return response($picture)->header('Content-Type', $type);
    }

    /**
     * Get a video.
     *
     * @param  integer  $file
     * @return \Illuminate\Http\Response
     */
    public function getVideo($file)
    {
        $disk = Storage::disk('videos');
        $video = $disk->get($file);
        $type = $disk->getMimetype($file);
        return response($video)->header('Content-Type', $type);
    }
}
