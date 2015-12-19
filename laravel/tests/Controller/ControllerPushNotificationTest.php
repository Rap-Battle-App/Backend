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
        //$this->withoutMiddleware();
        $user = factory(App\Models\User::class)->create();
        $user->save();

        echo $user->device_token;



        $this->post('/device-token', ['token' => '12345']);

        //checking the updation
        $this->assertEquals('12345', $user->device_token);

             
    }
	