<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

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
        // create users
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();

        // create OpenBattle
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

        // check if it is in database
        $this->seeInDatabase('open_battles', ['rapper1_id' => $user1->id,
                                            'rapper2_id' => $user2->id,
//                                            'phase' => 2,
                                            'beat1_id' => 1,
                                            'beat2_id' => 2,
                                            'rapper1_round1' => '/somewhere/somefile',
                                            'rapper2_round1' => '/somewhere/otherfile',
                                            'rapper1_round2' => '/somewhere/file3',
                                            'rapper2_round2' => '/somewhere/file4']);

        // can rappers be retrieved from OpenBattle?
        $this->assertNotNull($openBattle->rapper1()->get()->values()->keyBy('id')->get($user1->id));
        $this->assertNotNull($openBattle->rapper2()->get()->values()->keyBy('id')->get($user2->id));
    }

    /**
     * Test for hasFirstRounds() and hasAllRounds()
     */
    public function testHasAllRounds(){
        $openBattle1 = new OpenBattle;
        $openBattle2 = new OpenBattle;
        $openBattle2->rapper1_round1 = 'rapper1_round1.mp4';
        $openBattle3 = new OpenBattle;
        $openBattle3->rapper1_round1 = 'rapper1_round1.mp4';
        $openBattle3->rapper2_round1 = 'rapper2_round1.mp4';
        $openBattle3->phase = 2;
        $openBattle4 = new OpenBattle;
        $openBattle4->rapper1_round1 = 'rapper1_round1.mp4';
        $openBattle4->rapper2_round1 = 'rapper2_round1.mp4';
        $openBattle4->rapper1_round2 = 'rapper1_round2.mp4';
        $openBattle4->phase = 2;
        $openBattle4 = new OpenBattle;
        $openBattle4->rapper1_round1 = 'rapper1_round1.mp4';
        $openBattle4->rapper2_round1 = 'rapper2_round1.mp4';
        $openBattle4->rapper1_round2 = 'rapper1_round2.mp4';
        $openBattle4->rapper2_round2 = 'rapper2_round2.mp4';
        $openBattle4->phase = 2;

        $this->assertFalse($openBattle1->hasFirstRounds());
        $this->assertFalse($openBattle1->hasAllRounds());

        $this->assertFalse($openBattle2->hasFirstRounds());
        $this->assertFalse($openBattle2->hasAllRounds());

        $this->assertTrue($openBattle3->hasFirstRounds());
        $this->assertFalse($openBattle3->hasAllRounds());

        $this->assertTrue($openBattle4->hasFirstRounds());
        $this->assertTrue($openBattle4->hasAllRounds());
    }

    public function testIsOpen(){
        $battle11 = factory(App\Models\OpenBattle::class)->create();
        $battle12 = factory(App\Models\OpenBattle::class)->create();
        $battle21 = factory(App\Models\OpenBattle::class)->create();
        $battle22 = factory(App\Models\OpenBattle::class)->create();
        $battle3 = factory(App\Models\OpenBattle::class)->create();

        $battle11->setPhaseAttribute(1);
        $battle21->setPhaseAttribute(2);
        $battle3->setPhaseAttribute(3);

        $now = Carbon::now();

        Carbon::setTestNow(Carbon::now()->subHours(config('rap-battle.phase1time', 24) + 1));
        $battle12->setPhaseAttribute(1);
        Carbon::setTestNow(Carbon::now()->subHours(config('rap-battle.phase2time', 24) + 1));
        $battle22->setPhaseAttribute(2);

        Carbon::setTestNow($now);
        $this->assertTrue($battle11->isOpen());
        $this->assertTrue($battle21->isOpen());
        $this->assertFalse($battle12->isOpen());
        $this->assertFalse($battle22->isOpen());
        $this->assertFalse($battle3->isOpen());
    }
}
