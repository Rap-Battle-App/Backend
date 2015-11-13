<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for scopeOpenVoting
     */
    public function testScopeOpenVoting()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }
}
