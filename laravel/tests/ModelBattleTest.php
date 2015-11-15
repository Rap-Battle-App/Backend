<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

use App\Models\Battle;

class ModelBattleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Insertion test
     *
     * @return void
     */
    public function testInsert()
    {
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 10]);

        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->votes_rapper1 = 45;
        $battle->votes_rapper2 = 86;
        $battle->save();

        $this->seeInDatabase('battles', ['rapper1_id' => $user1->id,
                                        'rapper2_id' => $user2->id,
                                        'votes_rapper1' => 45,
                                        'votes_rapper2' => 86]);
    }

    /**
     * Tests whether the data can be retrieved from the database
     */
    public function testExtract()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->save();

        // can rappers be retrieved?
        $this->assertNotNull($battle->rapper1()->get()->values()->keyBy('id')->get($user1->id));
        $this->assertNotNull($battle->rapper2()->get()->values()->keyBy('id')->get($user2->id));

        // can users retrieve their battles?
        $this->assertNotNull($user1->battlesRapper1()->get()->values()->keyBy('id')->get($battle->id));
        $this->assertNotNull($user2->battlesRapper2()->get()->values()->keyBy('id')->get($battle->id));
    }


    /**
     * Test for scopeTrending
     */
    public function testScopeTrending()
    {
        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create battles
        for($i = 0; $i < 10; ++$i){
            $battle = new Battle;
            $battle->rapper1_id = $user1->id;
            $battle->rapper2_id = $user2->id;
            $battle->votes_rapper1 = rand(0, 2 * $i);
            $battle->votes_rapper2 = rand(0, 2 * $i);
            $battle->save();
        }

        //check trending battle count
        $trendingcnt = config('rap-battle.trendingcnt', 5);
        $trending = Battle::trending()->get();
        $this->assertEquals($trendingcnt, $trending->count());
    }

    /**
     * Test for scopeOpenVoting
     */
    public function testScopeOpenVoting()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        $votingperiod = Config::get('rap-battle.votingperiod', 24);
        // battles older than this date are closed:
        $timeoldest = new Carbon();
        $timeoldest->subHours($votingperiod + 1);

        // create two battles
        $battle1 = new Battle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->save();
        
        $battle2 = new Battle;
        $battle2->rapper1_id = $user1->id;
        $battle2->rapper2_id = $user2->id;
        $battle2->created_at = $timeoldest->toDateTimeString();
        $battle2->save();

        // get battles from database
        $openBattles = Battle::openVoting()->get()->values()->keyBy('id');

        // check
        $this->assertNotNull($openBattles->get($battle1->id));
        $this->assertNull($openBattles->get($battle2->id));
    }
}
