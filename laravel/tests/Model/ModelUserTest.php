<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\User;
use App\Models\Battle;
use App\Models\OpenBattle;
use App\Models\BattleRequest;

class ModelUserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * This test inserts a user 'the manual way'
     *
     * @return void
     */
    public function testInsertUser()
    {
        $password = bcrypt('123&qweRTZ3');

        $user = new User;
        $user->name = 'Peter';
        $user->email = 'peter@foo.com';
        $user->password = $password;
        $user->city = 'Musterhausen';
        $user->about_me = 'Hello, I am Peter';

        $user->save();

        $this->seeInDatabase('users', ['name' => 'Peter',
                                    'email' => 'peter@foo.com',
                                    'password' => $password,
                                    'city' => 'Musterhausen',
                                    'about_me' => 'Hello, I am Peter']);
    }

    /**
     * This test inserts a user by mass assignment
     *
     * @return void
     */
    public function testInsertUserMassAssignment()
    {
        $password = bcrypt('Pa$$w0rd');

        $user = App\Models\User::create(['name' => 'Hans',
                                        'email' => 'Hans@bar.de',
                                        'password' => $password,
                                        'city' => 'Beispieldorf',
                                        'about_me' => 'This is my text']);

        $this->seeInDatabase('users', ['name' => 'Hans', 
                                        'email' => 'Hans@bar.de',
                                        'password' => $password,
                                        'city' => 'Beispieldorf',
                                        'about_me' => 'This is my text']);
    }

    /**
     * Test for battles()
     *
     * @return void
     */
    public function testBattles()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user3 = factory(App\Models\User::class)->create();

        $battle1 = new Battle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->save();

        $battle2 = new Battle;
        $battle2->rapper1_id = $user2->id;
        $battle2->rapper2_id = $user3->id;
        $battle2->save();
        
        $battle3 = new Battle;
        $battle3->rapper1_id = $user3->id;
        $battle3->rapper2_id = $user1->id;
        $battle3->save();
       
        // get battles of user2    
        $battles = $user2->battles()->get()->keyBy('id')->keys()->toArray();
        $this->assertCount(2, $battles);
        $this->assertContains($battle1->id, $battles);
        $this->assertContains($battle2->id, $battles);
        $this->assertNotContains($battle3->id, $battles);
    }

    /**
     * Test for battleRequests()
     *
     * @return void
     */
    public function testBattleRequests()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user3 = factory(App\Models\User::class)->create();

        $battleRequest1 = new BattleRequest;
        $battleRequest1->challenger_id = $user1->id;
        $battleRequest1->challenged_id = $user2->id;
        $battleRequest1->save();

        $battleRequest2 = new BattleRequest;
        $battleRequest2->challenger_id = $user2->id;
        $battleRequest2->challenged_id = $user3->id;
        $battleRequest2->save();
        
        $battleRequest3 = new BattleRequest;
        $battleRequest3->challenger_id = $user3->id;
        $battleRequest3->challenged_id = $user1->id;
        $battleRequest3->save();
        
        // get battle requests of user2    
        $battleRequests = $user2->battleRequests()->get()->keyBy('id')->keys()->toArray();
        $this->assertCount(2, $battleRequests);
        $this->assertContains($battleRequest1->id, $battleRequests);
        $this->assertContains($battleRequest2->id, $battleRequests);
        $this->assertNotContains($battleRequest3->id, $battleRequests);

        // get battle requests, where user2 is challenger
        $battleRequestsChallenger = $user2->battleRequestsChallenger()->get()->keyBy('id')->keys()->toArray();
        $this->assertCount(1, $battleRequestsChallenger);
        $this->assertContains($battleRequest2->id, $battleRequestsChallenger);

        // get battle requests, where user2 is challenged
        $battleRequestsChallenged = $user2->battleRequestsChallenged()->get()->keyBy('id')->keys()->toArray();
        $this->assertCount(1, $battleRequestsChallenged);
        $this->assertContains($battleRequest1->id, $battleRequestsChallenged);
    }

    /**
     * Test for openBattles()
     *
     * @return void
     */
    public function testOpenBattles()
    {
        $user1 = factory(App\Models\User::class)->create();
        $user2 = factory(App\Models\User::class)->create();
        $user3 = factory(App\Models\User::class)->create();

        $battle1 = new OpenBattle;
        $battle1->rapper1_id = $user1->id;
        $battle1->rapper2_id = $user2->id;
        $battle1->save();

        $battle2 = new OpenBattle;
        $battle2->rapper1_id = $user2->id;
        $battle2->rapper2_id = $user3->id;
        $battle2->save();
        
        $battle3 = new OpenBattle;
        $battle3->rapper1_id = $user3->id;
        $battle3->rapper2_id = $user1->id;
        $battle3->save();
        
        // get open battles of user2    
        $battles = $user2->openBattles()->get()->keyBy('id')->keys()->toArray();
        $this->assertCount(2, $battles);
        $this->assertContains($battle1->id, $battles);
        $this->assertContains($battle2->id, $battles);
        $this->assertNotContains($battle3->id, $battles);
    }

    /**
     * Test for scopeRapper()
     *
     * @return void
     */
    public function testScopeRapper()
    {
        $rappers = User::rapper()->count();

        // create random users
        factory(App\Models\User::class, 'rapper', 7)->create();
        factory(App\Models\User::class, 'non-rapper', 1)->create();
        factory(App\Models\User::class, 'rapper', 2)->create();
        factory(App\Models\User::class, 'non-rapper', 4)->create();

        $this->assertEquals(9, User::rapper()->count() - $rappers);
    }

    /**
     * Test for scopeRatedBetween()
     */
    public function testScopeRatedBetween()
    {
        $user1 = factory(App\Models\User::class)->create(['rating' => 3]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 10]);
        $user3 = factory(App\Models\User::class)->create(['rating' => 15]);
        $user4 = factory(App\Models\User::class)->create(['rating' => 12]);

        $users = User::ratedBetween(5, 13)->get();
        $values = $users->values()->keyBy('id');

        // users 1 and 3 should not be selected
        $this->assertNull($values->get($user1->id), 'User may not be selected');
        $this->assertNull($values->get($user3->id), 'User may not be selected');
        // users 2 and 4 should be selected
        $this->assertNotNull($values->get($user2->id), 'User is not selected');
        $this->assertNotNull($values->get($user4->id), 'User is not selected');
    }

    /**
     *
     */
    public function testIsDeviceTokenNull()
    {
        $user1 = factory(App\Models\User::class)->create(['device_token' => null]);
        $user2 = factory(App\Models\User::class)->create(['device_token' => 'XXX']);

        $this->assertTrue($user1->isDeviceTokenNull());
        $this->assertFalse($user2->isDeviceTokenNull());
    }
}
