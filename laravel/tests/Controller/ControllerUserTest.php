<?php

use App\Http\Controllers\UserController;
use App\Models\Battle;
use App\Models\OpenBattle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerUserTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the UserController
     * 
     */



    public function testGetProfile()
    {
        //$this->withoutMiddleware();
        $user = factory(App\Models\User::class)->create(['rating' => 3]);
        $user->save();


        echo $user->rating;
        echo $user->username;
        echo $user->id;



        $this->get('/user/{id}', ['id' => $user->id]);
            
    }
	
    
    /**
     * Test for postProfileInformation
     */
    public function testPostProfileInformation()
    {
        //$this->withoutMiddleware();

        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user1->save();
        $user2->save();
        echo $user1->city;
        echo $user2->about_me;
        //may need to send the user number.
        $this->post('/profile', ['city' => $user2->city , 'about_me' => $user2->about_me]);

        //checking
        $this->assertEquals($user2->city , $user1->city);

    }

    /**
     * Test for postProfilePicture
     * needs work here
    public function testPostProfilePicture()
    {
        $user1 = factory(App\Models\User::class)->create();

    }
    */
    /**
     * Test for getSettings
     * need some work on getting id 
     */
    public function testGetSettings()
    {
        $user = factory(App\Models\User::class)->create();
        
        $user->save();
        echo $user->rapper;
        echo $user->notifications;

        $this->get('/account/settings');

        

    }	

    /**
     * Test for postSettings
     */
    public function testPostSettings()
    {
        $user = factory(App\Models\User::class)->create();
        $user->save();
        echo $user->rapper;
        echo $user->notifications;

        $this->post('/account/settings', ['rapper' => TRUE , 'notifications' => FALSE ]);
        
        $this->assertEquals(TRUE, $user->rapper);
    }	

    /**
     * Test for getUsername
     */
    public function testGetUsername()
    {
        $user = factory(App\Models\User::class)->create(['username' => 'smithjones']);
        $user->save();
        echo $user->username;




        $this->get('/account/useranme', ['id' => $user->id]);

        
        $this->assertEquals('smithjones', $user->username);
             
    }
    /**
     * Test for postUsername
     */
    public function testPostUsername()
    {
        $user = factory(App\Models\User::class)->create(['username' => 'smies']);
        $user->save();
        echo $user->username;
        


        $this->post('/account/useranme', ['username' => 'testuser']);

        //checking the updation
        $this->assertEquals('testuser', $user->username);

             
    }  
    /**
     * Test for postPassword
     */ 
    public function testPostPassword()
    {
        $user = factory(App\Models\User::class)->create();
        $user->save();

        echo $user->password;

        $options = array('cost' => 15);
        $new_password = 'roxtar';
        $this->post('/account/password', ['old_password' => $user->password, 'password' => $new_password]);

        //checking updated password after hashing
        $this->assertEquals($user->password, password_hash($new_password,PASSWORD_BCRYPT,$options));
        

             
    }   	

}
