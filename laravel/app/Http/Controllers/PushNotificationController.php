<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    /*
     *  Sets the device token for authenticated user.
     */
    public function postToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'string|required'
        ]);

        $user = $request->user();
        $user->device_token = $request->input('token');
        $user->save();
    }
}