<?php

use App\Http\Controllers\BattleRequestController;
use App\Models\Battle;
use App\Models\BattleRequest;
use App\Models\OpenBattle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerBattleRequestTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the BattleRequestController
     * 
     */
    public function testGetRequests()
    {
        //$this->withoutMiddleware();
        //created some user
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 4]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user4 = factory(App\Models\User::class)->create(['rating' => 6]);
        
        //created some battle request towards the user4
        $battleRequest1 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user1->id ,'challenged_id' => $user4->id]);
        $battleRequest2 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user3->id ,'challenged_id' => $user4->id]);
        $battleRequest3 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user2->id ,'challenged_id' => $user4->id]);
        $battleRequest4 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user3->id ,'challenged_id' => $user1->id]);


        // TODO: authenticate user

        //check for the user4
        $this->get('/requests')->seeJson([
                'requests' => ['id' => $battleRequest1->id,
                        'date' => $battleRequest1->creation_date,
                        'opponent' => ['user_id' => $user4->id,
                                'username' => $user4->username,
                                'profile_picture' => $user4->picture]]
                ]);
        }
    
    /**
     * Test for postRequests
     */
    public function testPostRequests()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // TODO: authenticate user1

        $this->post('/request', ['user_id' => $user2->id]);
        //getting the entry from the battle_request table to verify
        $br = BattleRequest::where('challenger_id', $user1->id)->first(); 
        //comparing it with the new entry created in the battle_request table
        $this->assertNotNull($br);
        $this->assertEquals($user1->id, $br->challenger_id);         
    }

    /**
     * Test for postAnswer
     */
    public function testPostAnswer()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create two battles
        $br = new BattleRequest;
        $br->challenger_id = $user1->id;
        $br->challenged_id = $user2->id;
        $br->save();

        // TODO: authenticate user

        // execute postAnswer()
        $this->post('/request/' . $br->id , ['accepted' == TRUE]);
        $oP = OpenBattle::find($br->challenger_id);

        //checking the output
        $this->assertNotNull($oP);
        $this->assertEquals($user1->id, $oP->rapper1_id);
        $this->assertEquals($user2->id, $oP->rapper2_id);
    }

    /**
     * Test for getRandomOpponent
     */
    public function testGetRandomOpponent()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 4]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user4 = factory(App\Models\User::class)->create(['rating' => 6]);
        $user5 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user6 = factory(App\Models\User::class)->create(['rating' => 4]);

        // TODO: authenticate user

        $this->get('/request/random')->seeJson([
                    'opponent' => ['user_id' => $user2->id,
                            'username' => $user2->username,
                            'profile_picture' => $user2->picture]
                    ]);
    }	
}
