<?php

use App\Http\Controllers\BattleController;
use App\Models\Battle;
use App\Models\OpenBattle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerBattleTest extends TestCase
{
    use WithoutMiddleware;
    /**
     * 
     *	Testing the BattleController
     * 
     */


    public function testGetBattle()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 10]);

        $battle = new Battle;
        //just experimenting
        //$battle->id = 93102; 
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->video = "/path/to/file";
        $battle->votes_rapper1 = 45;
        $battle->votes_rapper2 = 86;
        $battle->save();
        echo $battle->id;



        $this->get('/battle/{id}', ['id' => $battle->id])
             ->seeJson([
                //'rapper1_id' => $user1->id,
                //'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 45,
                'votes_rapper2' => 86,
             ]);
        /*$this->get('battle/{id}', ['id' => $battle->id])
             ->seeJson([
                'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 45,
                'votes_rapper2' => 86
             ]);
    */
    }
	
    
    /**
     * Test for getTrending
     */
    public function testGetTrending()
    {
        //$this->withoutMiddleware();

        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create battles
        for($i = 0; $i < 10; ++$i){
            $battle = new Battle;
            $battle->rapper1_id = $user1->id;
            $battle->rapper2_id = $user2->id;
            $battle->video = "/path/to/file";
            $battle->votes_rapper1 = ($i+1);
            $battle->votes_rapper2 = ($i+2);
            $battle->save();
        }

        //check trending battle count
        $trendingcnt = config('rap-battle.trendingcnt', 5);
 

        //check possible conflicts with array
        $this->get('/battles/trending')
             ->seeJson([
                //'rapper1_id' => $user1->id,
                //'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => '11',
                'votes_rapper2' => '12',

             ]);
         /*
        $this->get('battles/trending')
             ->seeJson([
                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 11,
                'votes_rapper2' => 12},

                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 10,
                'votes_rapper2' => 11},

                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 9,
                'votes_rapper2' => 10},

                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 8,
                'votes_rapper2' => 9},

                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'video' => "/path/to/file",
                'votes_rapper1' => 7,
                'votes_rapper2' => 8}

             ]);
*/
    }

    /**
     * Test for getOpenVoting
     */
    public function testGetOpenVoting()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user3 = factory(App\Models\User::class)->create();
        $user4 = factory(App\Models\User::class)->create();

        $votingperiod = Config::get('rap-battle.votingperiod', 24);
        // battles older than this date are closed:
        $timeoldest = new Carbon();
        $timeoldest->subHours($votingperiod + 1);

        // create two battles
        $battle1 = new Battle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->video = "/path/to/file";
        $battle1->save();
        
        $battle2 = new Battle;
        $battle2->rapper1_id = $user3->id;
        $battle2->rapper2_id = $user4->id;
        $battle2->video = "/path/to/file";
        $battle2->created_at = $timeoldest->toDateTimeString();
        $battle2->save(); 

        //using user id's to distinguish battles
        $this->get('/battles/open-voting')
             ->seeJson([
                'rapper1_id' => "$user1->id",
                'rapper2_id' => "$user2->id",
                'video' => "/path/to/file",
                
             ]);

    }

    /**
     * Test for getCompleted
     */
    public function testGetCompleted()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user3 = factory(App\Models\User::class)->create();
        $user4 = factory(App\Models\User::class)->create();

        $votingperiod = Config::get('rap-battle.votingperiod', 24);
        // battles older than this date are closed:
        $timeoldest = new Carbon();
        $timeoldest->subHours($votingperiod + 1);

        // create two battles
        $battle1 = new Battle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->video = "/path/to/file";
        $battle1->save();
        
        $battle2 = new Battle;
        $battle2->rapper1_id = $user3->id;
        $battle2->rapper2_id = $user4->id;
        $battle2->video = "/path/to/file";
        $battle2->created_at = $timeoldest->toDateTimeString();
        $battle2->save(); 

        //using user id's to distinguish battles
        $this->get('/battles/completed')
             ->seeJson([
                //'rapper1_id' => $user3->id,
                //'rapper2_id' => $user4->id,
                'video' => "/path/to/file",
                
             ]);
    }	

    /**
     * Test for getOpen
     */
    public function testGetOpen()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();


        // create two open battles 
        $battle1 = new OpenBattle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->phase = 1;
        $battle1->beat1_id = 1;
        $battle1->rapper1_round1 = "/path/to/rapper1_round1";
        $battle1->rapper2_round2 = "/path/to/rapper2_round2";
        $battle1->beat2_id = 2;
        $battle1->rapper2_round1 = "/path/to/rapper2_round1";
        $battle1->rapper1_round2 = "/path/to/rapper1_round2";
        $battle1->save();
        
        $battle2 = new OpenBattle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->phase = 2;
        $battle1->beat1_id = 2;
        $battle1->rapper1_round1 = "/path/to/rapper1_round1_b";
        $battle1->rapper2_round2 = "/path/to/rapper2_round2_b";
        $battle1->beat2_id = 1;
        $battle1->rapper2_round1 = "/path/to/rapper2_round1_b";
        $battle1->rapper1_round2 = "/path/to/rapper1_round2_b";
        $battle1->save();
        
        $this->get('/battles/open')
             ->seeJson([
                'rapper1_id' => "$user1->id",
                'rapper2_id' => "$user2->id",
                'phase' => 1,
                'beat1_id' => 1,
                'rapper1_round1' => "/path/to/rapper1_round1",
                'rapper2_round2' => "/path/to/rapper2_round2",
                'beat2_id' => 2,
                'rapper2_round1' => "/path/to/rapper2_round1",
                'rapper1_round2' => "/path/to/rapper1_round2"

                
             ]);
        /*       
        $this->get('battles/open')
             ->seeJson([
                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'phase' => 1,
                'beat1_id' => 1,
                'rapper1_round1' => "/path/to/rapper1_round1",
                'rapper2_round2' => "/path/to/rapper2_round2",
                'beat2_id' => 2,
                'rapper2_round1' => "/path/to/rapper2_round1",
                'rapper1_round2' => "/path/to/rapper1_round2"},

                {'rapper1_id' => $user1->id,
                'rapper2_id' => $user2->id,
                'phase' => 2,
                'beat1_id' => 2,
                'rapper1_round1' => "/path/to/rapper1_round1_b",
                'rapper2_round2' => "/path/to/rapper2_round2_b",
                'beat2_id' => 1,
                'rapper2_round1' => "/path/to/rapper2_round1_b",
                'rapper1_round2' => "/path/to/rapper1_round2_b"}

                
             ]);
         */
    }	

    /**
     * Test for postVote
     */
    public function testPostVote()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();




        // create two battles
        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->votes_rapper1 = 2;
        $battle->votes_rapper2 = 5;
        $battle->save();
        


        $this->get('/battle/{id}/vote', ['id' => $battle->id, 'rapper_number' => 1]);

        $this->assertEquals(3, $battle->votes_rapper1);

             
    }	

}
