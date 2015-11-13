<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\OpenBattle;

class ModelOpenBattleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Insertion test
     *
     * @return void
     */
    public function testInsert()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        $openBattle = new OpenBattle;
        $openBattle->rapper1_id = $user1->id;
        $openBattle->rapper2_id = $user2->id;
        $openBattle->phase = 2;
        $openBattle->beat1_id = 1;
        $openBattle->beat2_id = 2;
        $openBattle->rapper1_round1 = '/somewhere/somefile';
        $openBattle->rapper2_round1 = '/somewhere/otherfile';
        $openBattle->rapper1_round2 = '/somewhere/file3';
        $openBattle->rapper2_round2 = '/somewhere/file4';
        $openBattle->save();

        $this->seeInDatabase('open_battles', ['rapper1_id' => $user1->id,
                                            'rapper2_id' => $user2->id,
                                            'phase' => 2,
                                            'beat1_id' => 1,
                                            'beat2_id' => 2,
                                            'rapper1_round1' => '/somewhere/somefile',
                                            'rapper2_round1' => '/somewhere/otherfile',
                                            'rapper1_round2' => '/somewhere/file3',
                                            'rapper2_round2' => '/somewhere/file4']);

        // can rappers be retrieved?
        $this->assertNotNull($openBattle->rapper1()->get()->values()->keyBy('id')->get($user1->id));
        $this->assertNotNull($openBattle->rapper2()->get()->values()->keyBy('id')->get($user2->id));

        // can users retrieve their open battles?
        $this->assertNotNull($user1->openBattlesRapper1()->get()->values()->keyBy('id')->get($openBattle->id));
        $this->assertNotNull($user2->openBattlesRapper2()->get()->values()->keyBy('id')->get($openBattle->id));
    }
}
