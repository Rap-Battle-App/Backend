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
        $user = factory(App\Models\User::class)->create(['wins' => 4, 'defeats' => 10]);

        $this->get('/user/' . $user->id)->seeJson([
                'id' => (string) $user->id,
                'username' => $user->username,
                'profile_picture' => $user->picture,
                'city' => $user->city,
                'about_me' => $user->about_me,
                'statistics' => [
                    'wins' => (string) $user->wins,
                    'defeats' => (string) $user->defeats
                ],
                'rapper' => $user->rapper
            ]);
    }

    /**
     * Test for postProfileInformation
     */
    public function testPostProfileInformation()
    {
        // create users
        $user = factory(App\Models\User::class)->create();

        $city = 'Testcity';
        $about_me = 'Hello, this is a test string.';

        $this->actingAs($user)->post('/profile', ['city' => $city , 'about_me' => $about_me]);

        //checking
        $this->assertEquals($city, $user->city);
        $this->assertEquals($about_me, $user->about_me);
    }

    /**
     * Test for postProfilePicture
     * TODO: needs work here
     */
    public function testPostProfilePicture()
    {
        //$user = factory(App\Models\User::class)->create();
        $this->markTestIncomplete();
    }

    /**
     * Test for getSettings
     * TODO: need some work on getting id 
     */
    public function testGetSettings()
    {
        $user = factory(App\Models\User::class)->create();
        
        $this->actingAs($user)->get('/account/settings')->seeJson([
                    'rapper' => $user->rapper,
                    'notifications' => $user->notifications            
            ]);
    }	

    /**
     * Test for postSettings
     */
    public function testPostSettings()
    {
        $user = factory(App\Models\User::class)->create(['rapper' => true, 'notifications' => true]);

        $this->actingAs($user)->post('/account/settings', ['rapper' => false, 'notifications' => false]);
        $this->assertFalse($user->rapper);
        $this->assertFalse($user->rapper);
        
        $this->actingAs($user)->post('/account/settings', ['rapper' => true, 'notifications' => true]);
        $this->assertTrue($user->rapper);
        $this->assertTrue($user->rapper);
    }	

    /**
     * Test for postUsername
     */
    public function testPostUsername()
    {
        $oldname = 'smies';
        $newname = 'testname';

        $user = factory(App\Models\User::class)->create(['username' => $oldname]);

        $this->actingAs($user)->post('/account/username', ['username' => $newname]);

        //checking the updation
        $this->assertEquals($newname, $user->username);     
    }
 
    /**
     * Test for postPassword
     */ 
    public function testPostPassword()
    {
        $old_password = 'Pa$$w0rd';
        $new_password = 'roxtar';

        $user = factory(App\Models\User::class)->create(['password' => bcrypt($old_password)]);

        $this->actingAs($user)->post('/account/password', ['old_password' => $old_password, 'password' => $new_password]);

        //checking updated password after hashing
        $this->assertEquals(bcrypt($new_password), $user->password);
            
    }   	

}
