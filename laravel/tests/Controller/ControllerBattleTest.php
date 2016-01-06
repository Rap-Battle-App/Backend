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

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

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
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->video = '/path/to/file';
        $battle->votes_rapper1 = 45;
        $battle->votes_rapper2 = 86;
        $battle->save();

        // TODO: authenticate user

        $this->get('/battle/' . $battle->id)->seeJSON([
                'rapper1_id' => (string) $user1->id,
                'rapper2_id' => (string) $user2->id,
                'video' => $battle->video,
                'votes_rapper1' => (string) $battle->votes_rapper1,
                'votes_rapper2' => (string) $battle->votes_rapper2]);
    }
    
    /**
     * Test for getTrending
     */
    public function testGetTrending()
    {
        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        $battle = array();
        $data = array();
        // create battles
        for($i = 0; $i < 10; ++$i){
            $battle[$i] = new Battle;
            $battle[$i]->rapper1_id = $user1->id;
            $battle[$i]->rapper2_id = $user2->id;
            $battle[$i]->video = '/path/to/file';
            $battle[$i]->votes_rapper1 = ($i+1);
            $battle[$i]->votes_rapper2 = ($i+2);
            $battle[$i]->save();

            $data[] = [
                    'battle_id' => (string) $battle[$i]->id,
                    'rapper1' => [
                        'user_id' => (string) $user1->id,
                        'username' => $user1->username,
                        'profile_picture' => $user1->picture],
                    'rapper2' => [
                        'user_id' => (string) $user2->id,
                        'username' => $user2->username,
                        'profile_picture' => $user2->picture]
                    ];
        }

        //check possible conflicts with array
        $this->get('/battles/trending')->seeJson([
                'current_page' => 1,
                'data' => $data
        ]);
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

        $this->get('/battles/open-voting')->seeJson([
                'current_page' => 1,
                'data' => [[
                    'battle_id' => (string) $battle1->id,
                    'rapper1' => [
                        'user_id' => (string) $user1->id,
                        'username' => $user1->username,
                        'profile_picture' => $user1->picture],
                    'rapper2' => [
                        'user_id' => (string) $user2->id,
                        'username' => $user2->username,
                        'profile_picture' => $user2->picture]
                ]]
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

        $this->get('/battles/completed')->seeJson([
                'current_page' => 1,
                'data' => [[
                    'battle_id' => (string) $battle2->id,
                    'rapper1' => [
                        'user_id' => (string) $user3->id,
                        'username' => $user3->username,
                        'profile_picture' => $user3->picture],
                    'rapper2' => [
                        'user_id' => (string) $user4->id,
                        'username' => $user4->username,
                        'profile_picture' => $user4->picture]
                ]]
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
        $battle2->rapper1_id = $user1->id;
        $battle2->rapper2_id = $user2->id;
        $battle2->phase = 2;
        $battle2->beat1_id = 2;
        $battle2->rapper1_round1 = "/path/to/rapper1_round1_b";
        $battle2->rapper2_round2 = "/path/to/rapper2_round2_b";
        $battle2->beat2_id = 1;
        $battle2->rapper2_round1 = "/path/to/rapper2_round1_b";
        $battle2->rapper1_round2 = "/path/to/rapper1_round2_b";
        $battle2->save();

        // TODO: authenticate user
        
        $this->get('/battles/open')
             ->seeJson([
                'rapper1_id' => "$user1->id",
                'rapper2_id' => "$user2->id",
                'phase' => '1',
                'beat1_id' => '1',
                'rapper1_round1' => "/path/to/rapper1_round1",
                'rapper2_round2' => "/path/to/rapper2_round2",
                'beat2_id' => '2',
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
        


        $this->post('/battle/{id}/vote', ['id' => $battle->id, 'rapper_number' => 1]);

        //the voting user does not exists, possibly failed raising votes
        $this->assertEquals(2, $battle->votes_rapper1);

             
    }	

}
