<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\Vote;
use App\Models\Battle;

class ModelVoteTest extends TestCase
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
        $user2 = factory(App\Models\User::class)->create(['rating' => 5]);

        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->save();

        $vote1 = new Vote;
        $vote1->user_id = $user1->id;
        $vote1->battle_id = $battle->id;
        $vote1->rapper_number = 1;
        $vote1->save();

        // is vote in database?
        $this->seeInDatabase('votes', ['user_id' => $user1->id,
                                        'battle_id' => $battle->id,
                                        'rapper_number' => 1]);
    }

    /**
     * Tests whether the data can be retrieved from the database
     */
    public function testExtract()
    {
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 5]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 5]);

        $battle = new Battle;
        $battle->rapper1_id = $user1->id;
        $battle->rapper2_id = $user2->id;
        $battle->save();

        $vote1 = new Vote;
        $vote1->user_id = $user1->id;
        $vote1->battle_id = $battle->id;
        $vote1->rapper_number = 1;
        $vote1->save();

        $vote2 = new Vote;
        $vote2->user_id = $user2->id;
        $vote2->battle_id = $battle->id;
        $vote2->rapper_number = 0;
        $vote2->save();

        $vote3 = new Vote;
        $vote3->user_id = $user3->id;
        $vote3->battle_id = $battle->id;
        $vote3->rapper_number = 0;
        $vote3->save();

        // can user be retrieved?
        $this->assertEquals($user3->id, $vote3->user->id);

        // can battle be retrieved?
        $this->assertEquals($battle->id, $vote3->battle->id);

        // can a battle get it's votes?
        $battlevotecnt = $battle->votes()->count();
        $this->assertEquals(3, $battlevotecnt);

        $battlevotes = $battle->votes()->get()->values()->keyBy('user_id');
        $this->assertNotNull($battlevotes->get($user1->id));
        $this->assertNotNull($battlevotes->get($user2->id));
        $this->assertNotNull($battlevotes->get($user3->id));

        // can a user get their votes?
        $vote1id = $user1->votes()->get()->values()->keyBy('user_id')->get($user1->id)->id;
        $this->assertEquals($vote1->id, $vote1id);
    }
}
