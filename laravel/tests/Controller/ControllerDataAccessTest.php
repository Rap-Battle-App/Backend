<?php

use App\Http\Controllers\DataAccessController;
use App\Models\Battle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerDataAccessTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the getPicture
     * 
     */



    public function testGetPicture()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);

        $this->get('/picture/{id}', ['id' => $user1->id]);
             
    }
	
    
    /**
     * Test for getVideo
     */
    public function testGetVideo()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 6]);

        $battle = new Battle;
 
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->video = "/path/to/file";
        $battle->votes_rapper1 = 45;
        $battle->votes_rapper2 = 86;
        $battle->save();
        //echo $battle->id;



        $this->get('/video/{id}', ['id' => $battle->id]);
    }
}
