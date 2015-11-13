<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\BattleRequest;

class ModelBattleRequestTest extends TestCase
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

        $battleRequest = new BattleRequest;
        $battleRequest->challenger_id = $user1->id;
        $battleRequest->challenged_id = $user2->id;
        $battleRequest->save();

        // is request in database?
        $this->seeInDatabase('battle_requests', ['challenger_id' => $user1->id,
                                                'challenged_id' => $user2->id]);

        // can challenger and challenged user be retrieved?
        $this->assertNotNull($battleRequest->challenger()->get()->values()->keyBy('id')->get($user1->id));
        $this->assertNotNull($battleRequest->challenged()->get()->values()->keyBy('id')->get($user2->id));

        // can users retrieve their open battles?
        $this->assertNotNull($user1->BattleRequestsChallenger()->get()->values()->keyBy('id')->get($battleRequest->id));
        $this->assertNotNull($user2->BattleRequestsChallenged()->get()->values()->keyBy('id')->get($battleRequest->id));

    }
}
