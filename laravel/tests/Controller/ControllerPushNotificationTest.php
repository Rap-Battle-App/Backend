<?php

use App\Http\Controllers\BattleController;
use App\Models\Battle;
use App\Models\OpenBattle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PushNotificationControllerTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the PushNotificationController
     * 
     */
    public function testPostNotifications()
    {
        $user = factory(App\Models\User::class)->create();

        $this->post('/device-token', ['token' => "hello"]);

        //checking the updation
        $this->assertEquals("hello", $user->device_token);
    }
}	
