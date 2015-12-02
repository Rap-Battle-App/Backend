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
        // create rappers
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 10]);

        // create battle
        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->votes_rapper1 = 45;
        $battle->votes_rapper2 = 86;
        $battle->votes_rapper1 = '/path/to/file1';
        $battle->votes_rapper2 = '/path/to/file2';
        $battle->save();

        // check
        $this->seeInDatabase('battles', ['rapper1_id' => $user1->id,
                                        'rapper2_id' => $user2->id,
                                        'votes_rapper1' => 45,
                                        'votes_rapper2' => 86,
                                        'votes_rapper1' => '/path/to/file1',
                                        'votes_rapper2' => '/path/to/file2']);
    }

    /**
     * Tests whether the data can be retrieved from the database
     */
    public function testExtract()
    {
        // create rappers
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create battle
        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->save();

        // check: can rappers be retrieved from database via the rappers?
        $this->assertNotNull($battle->rapper1()->get()->values()->keyBy('id')->get($user1->id));
        $this->assertNotNull($battle->rapper2()->get()->values()->keyBy('id')->get($user2->id));
    }


    /**
     * Test for scopeTrending
     */
    public function testScopeTrending()
    {
        // get trending time limit
        $trendingperiod = config('rap-battle.trendingperiod', 168);
        $timeoldest = new Carbon();
        $timeoldest->subHours($trendingperiod);

        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create battles
        for($i = 0; $i < $max_battles = 40; ++$i){
            $battle = new Battle;
            $battle->rapper1_id = $user1->id;
            $battle->rapper2_id = $user2->id;
            $battle->votes_rapper1 = rand(0, 2 * $i);
            $battle->votes_rapper2 = rand(0, 2 * $i);

            // creation date: random value in valid range, every second out of range
            $date = Carbon::now()->subHours(rand(0, $trendingperiod - 1) + ($i & 0x1 ? 0 : $trendingperiod));
            $battle->created_at = $date->toDateTimeString();

            // fake votes
            if($i < 20){ // each second of these battles will be trending
                $battle->votes_rapper1 = 1000 + $i;
                $battle->votes_rapper2 = 500 + 2 * $i;
            } else { // these battles won't be trending
                $battle->votes_rapper1 = 100 + $i;
                $battle->votes_rapper2 = 50 + 2 * $i;
            }

            $battle->save();
        }

        // checks
        $trending = Battle::trending()->get()->take(10)->keyBy('id');

        // 10 trending battles out of 20 possible in time range
        $this->assertEquals(10, $trending->count());

        foreach($trending as $battle){
            // check time range
            $timediff = Carbon::parse($battle->created_at)->gt($timeoldest);
            $this->assertTrue($timediff);

            // check vote counts
            $this->assertTrue(1000 <= $battle->votes_rapper1 && (1000 + $max_battles / 2) > $battle->votes_rapper1);
            $this->assertTrue(500 <= $battle->votes_rapper2 && (500 + $max_battles) > $battle->votes_rapper2);
        }
    }

    /**
     * Test for scopeOpenVoting and scopeCompleted
     */
    public function testScopesOpenCompleted()
    {
        // create rappers
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        $votingperiod = config('rap-battle.votingperiod', 24);
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

        // test scopeOpenVoting
        // get battles from database
        $openBattles = Battle::openVoting()->get()->values()->keyBy('id');
        $this->assertNotNull($openBattles->get($battle1->id));
        $this->assertNull($openBattles->get($battle2->id));

        // test scopeCompleted
        // get battles from database
        $completedBattles = Battle::completed()->get()->values()->keyBy('id');
        $this->assertNull($completedBattles->get($battle1->id));
        $this->assertNotNull($completedBattles->get($battle2->id));
    }
}
