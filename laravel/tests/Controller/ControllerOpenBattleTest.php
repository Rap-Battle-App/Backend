<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\OpenBattleController;
use App\Models\OpenBattle;

class ControllerOpenBattleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetBattle()
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
        
        $this->actingAs($user1)->get('/open-battle/' . $battle1->id)->seeJson([
                    // TODO: check actual JSON format from API definition
                    'id' => $battle1->id
            ]); 
    }
    public function testPostRound()
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
        
        $this->post('/open-battle/' . $battle1->id . '/round' , ['beat_id'=>2 , 'video'=>"abc.mp4"]);

        // TODO: check if OpenBattle changed, video expects an actual video file, not sure if there is a way to fake one
        $this->markTestIncomplete();
    }


}
