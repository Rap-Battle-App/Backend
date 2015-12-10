<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    /**
     * Set the device token for authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
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
