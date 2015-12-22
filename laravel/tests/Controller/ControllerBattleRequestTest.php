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



    public function testgetRequests()
    {
        //$this->withoutMiddleware();
        //created some user
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 4]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user4 = factory(App\Models\User::class)->create(['rating' => 6]);
        
        //created some battle request towards the user4
        $battelrequest1 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user1->id ,'challenged_id' => $user4->id]);
        $battelrequest2 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user3->id ,'challenged_id' => $user4->id]);
        $battelrequest3 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user2->id ,'challenged_id' => $user4->id]);
        $battelrequest4 = factory(App\Models\BattleRequest::class)->create(['challenger_id' => $user3->id ,'challenged_id' => $user1->id]);
        



        //need to send the logged in user as well. how?
        //check for the user4
        $this->get('/request');
    }
	
    
    /**
     * Test for postRequests
     */
    public function testpostRequests()
    {
   
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        


        $this->post('/request', ['user_id' => $user2->id]);
        //getting the entry from the battle_request table to verify
        $br=BattleRequest::find($user1->id);
        //comparing it with the new entry created in the battle_request table
        $this->assertEquals($user1->id, $br->challenger_id);
         
    }

    /**
     * Test for postAnswer
     */
    public function testpostAnswer()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();


        // create two battles
        $br = new BattleRequest;
        $br->challenger_id = $user1;
        $br->challenged_id = $user2;
        $br->save();
        $this->post('/request/{id}' , ['accepted' == TRUE]);
        $oP=OpenBattle::find($br->challenger_id);

        $this->assertEquals($br->challenger_id, $oP->rapper1_id);

    }

    /**
     * Test for getRandomOpponent
     */
    public function testgetRandomOpponent()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 4]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user4 = factory(App\Models\User::class)->create(['rating' => 6]);
        $user5 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user6 = factory(App\Models\User::class)->create(['rating' => 4]);



        //need to send the logged in user as well. how?
        $this->get('/request/random');
    }	
}
