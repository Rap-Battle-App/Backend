<?php

use App\Http\Controllers\PasswordController;
use App\Models\Battle;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerPasswordTest extends TestCase
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
        $this->post('/password-recovery/email', ['email' => 'testuser123@gmail.com']);

        //checking
        //put your email and check whether i you're getting any reset email or not
             
    }
	
    
    /**
     * Test for postReset
     */
    public function testPostReset()
    {
        //$this->withoutMiddleware();

        // create users
        $user = factory(App\Models\User::class)->create(['email' => 'testuser123@gmail.com',
                'password' => 'password',
                'device_token' => '1q2w3e4r5t6y7u8i']);
        
        $this->post('/password-recovery/reset',
                ['email' => 'testuser123@gmail.com',
                'token' => '1q2w3e4r5t6y7u8i',
                'password' => 'password123']);
        //checking 
        //crypting to compare crypted password
        $this->assertEquals(bcrypt('password123'), $user->password); 

    }

} 
