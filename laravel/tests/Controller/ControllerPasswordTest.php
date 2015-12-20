<?php

use App\Http\Controllers\PasswordController;
use App\Models\Battle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerBattleTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the PasswordController
     * 
     */



    public function testPostEmail()
    {
        //put in your email address here to check whether it's sending the reset link properly or not
        $user = factory(App\Models\User::class)->create(['email' => 'testuser123@gmail.com']);

        // here also your email address
        $this->post('/password-recovery/email', ['email' => 'testuser123@gmail.com');
             
    }
	
    
    /**
     * Test for postReset
     */
    public function testPostReset()
    {
        //$this->withoutMiddleware();

        // create users
        $user = factory(App\Models\User::class)->create('email' => 'testuser123@gmail.com','password' => 'password','device_token' => '1q2w3e4r5t6y7u8i');
        
        $this->post('/password-recovery/reset' ,['email' => 'testuser123@gmail.com','token' => '1q2w3e4r5t6y7u8i','password' => 'password123']);
        $this->assertEquals('password123', $user->password); 

    }

   