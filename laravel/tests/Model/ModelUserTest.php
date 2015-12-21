<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\User;
use App\Models\Battle;
use App\Models\OpenBattle;
use App\Models\BattleRequest;
use Carbon\Carbon;

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
        $user->username = 'Peter';
        $user->email = 'peter@foo.com';
        $user->password = $password;
        $user->city = 'Musterhausen';
        $user->about_me = 'Hello, I am Peter';

        $user->save();

        $this->seeInDatabase('users', ['username' => 'Peter',
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

        $user = App\Models\User::create(['username' => 'Hans',
                                        'email' => 'Hans@bar.de',
                                        'password' => $password,
                                        'city' => 'Beispieldorf',
                                        'about_me' => 'This is my text']);

        $this->seeInDatabase('users', ['username' => 'Hans', 
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
        $battles = $user2->battles()->lists('id')->toArray();
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
        $user4 = factory(App\Models\User::class)->create();

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
        $battleRequests = $user2->battleRequests()->lists('id')->toArray();
        $this->assertCount(2, $battleRequests);
        $this->assertContains($battleRequest1->id, $battleRequests);
        $this->assertContains($battleRequest2->id, $battleRequests);
        $this->assertNotContains($battleRequest3->id, $battleRequests);

        // get battle requests, where user2 is challenger
        $battleRequestsChallenger = $user2->battleRequestsChallenger()->lists('id')->toArray();
        $this->assertCount(1, $battleRequestsChallenger);
        $this->assertContains($battleRequest2->id, $battleRequestsChallenger);

        // get battle requests, where user2 is challenged
        $battleRequestsChallenged = $user2->battleRequestsChallenged()->lists('id')->toArray();
        $this->assertCount(1, $battleRequestsChallenged);
        $this->assertContains($battleRequest1->id, $battleRequestsChallenged);

        // get battle requests of user 4 (should be none)
        $this->assertCount(0, $user4->battleRequests()->lists('id')->toArray()); 

        // tests for hasBattleRequestAgainst()
        $this->assertTrue($user1->hasBattleRequestAgainst($user2));
        $this->assertTrue($user1->hasBattleRequestAgainst($user3));
        $this->assertTrue($user2->hasBattleRequestAgainst($user1));
        $this->assertTrue($user2->hasBattleRequestAgainst($user3));
        $this->assertFalse($user1->hasBattleRequestAgainst($user4));
        $this->assertFalse($user4->hasBattleRequestAgainst($user3));

        // tests for scopeNoBattleRequestsAgainst() 
        $nbra1 = $user1->noBattleRequestsAgainst($user4)->lists('id')->toArray();
        $this->assertContains($user4->id, $nbra1);    
        $nbra4 = $user4->noBattleRequestsAgainst($user4)->lists('id')->toArray();
        $this->assertContains($user1->id, $nbra4);
        $this->assertContains($user2->id, $nbra4);
        $this->assertContains($user3->id, $nbra4);
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
        $user4 = factory(App\Models\User::class)->create();

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
        $battles = $user2->openBattles()->lists('id')->toArray();
        $this->assertCount(2, $battles);
        $this->assertContains($battle1->id, $battles);
        $this->assertContains($battle2->id, $battles);
        $this->assertNotContains($battle3->id, $battles);

        // tests for hasOpenBattleAgainst()
        $this->assertTrue($user1->hasOpenBattleAgainst($user2));
        $this->assertTrue($user1->hasOpenBattleAgainst($user3));
        $this->assertTrue($user2->hasOpenBattleAgainst($user1));
        $this->assertTrue($user2->hasOpenBattleAgainst($user3));
        $this->assertFalse($user1->hasOpenBattleAgainst($user4));
        $this->assertFalse($user4->hasOpenBattleAgainst($user3));

        // tests for scopeNoBattleRequestsAgainst() 
        $noba1 = $user1->noOpenBattleAgainst($user4)->lists('id')->toArray();
        $this->assertContains($user4->id, $noba1);
        $noba4 = $user4->noOpenBattleAgainst($user4)->lists('id')->toArray();
        $this->assertContains($user1->id, $noba4);
        $this->assertContains($user2->id, $noba4);
        $this->assertContains($user3->id, $noba4);
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
     * Test for hasDeviceToken()
     */
    public function testHasDeviceToken()
    {
        $user1 = factory(App\Models\User::class)->create(['device_token' => null]);
        $user2 = factory(App\Models\User::class)->create(['device_token' => 'XXX']);

        $this->assertFalse($user1->hasDeviceToken());
        $this->assertTrue($user2->hasDeviceToken());
    }

    /**
     * Test for getProfile()
     */
    public function testGetProfilePictureAttribute()
    {
        // TODO
        $this->markTestIncomplete();
    }

    /**
     * Test for scopeNamedLike()
     */
    public function testScopeNamedLike()
    {
        $user1 = factory(App\Models\User::class)->create(['username' => 'PeterMeier']);
        $user2 = factory(App\Models\User::class)->create(['username' => 'HansPeterMueller']);
        $user3 = factory(App\Models\User::class)->create(['username' => 'DrPeter']);
        $user4 = factory(App\Models\User::class)->create(['username' => 'AndererName']);
        $user5 = factory(App\Models\User::class)->create(['username' => 'Nochjemand']);

        $users = User::namedLike('Peter')->get()->values()->keyBy('id');

        $this->assertTrue($users->has($user1->id));
        $this->assertTrue($users->has($user2->id));
        $this->assertTrue($users->has($user3->id));
        $this->assertFalse($users->has($user4->id));
        $this->assertFalse($users->has($user5->id));
    }

    /**
     * Test for profilePreview()
     */
    public function testProfilePreview()
    {
        $user = factory(App\Models\User::class)->create();

        $this->assertEquals(['user_id' => $user->id,
                'username' => $user->username,
                'profile_picture' => $user->picture],
                $user->profilePreview());
    }

    /**
     * Test for profile()
     */
    public function testProfile()
    {
        $user = factory(App\Models\User::class)->create();

        $this->assertEquals(['id' => $user->id,
                'username' => $user->username,
                'profile_picture' => $user->picture,
                'city' => $user->city,
                'about_me' => $user->about_me,
                'statistics' => ['wins' => $user->wins, 'defeats' => $user->defeats],
                'rapper' => $user->rapper],
                $user->profile());
    }

    /**
     * Test for settings()
     */
    public function testSettings()
    {
        $user = factory(App\Models\User::class)->create();

        $this->assertEquals(['rapper' => $user->rapper,
                'notifications' => $user->notifications],
                $user->settings());
    }

    /**
     * Test for updateRating()
     */
    public function testUpdateRating()
    {
        $user1 = factory(App\Models\User::class)->create(['rating' => 0, 'wins' => 0, 'defeats' => 0]);
        $user2 = factory(App\Models\User::class)->create(['rating' => 0, 'wins' => 0, 'defeats' => 0]);

        $battles = Array();
        // create battles
        for($i = 0; $i < 4; $i++){
            $battle = new Battle;
            $battle->rapper1_id = $user1->id;
            $battle->rapper2_id = $user2->id;
            if($i != 3)
                $battle->created_at = (new Carbon())->subDays(31)->toDateTimeString();
            $battles[] = $battle;
        }

        // rating should be 0 for both users
        $this->assertEquals(0, $user1->rating, 'Rating should be 0 if there are no matches.');
        $this->assertEquals(0, $user2->rating, 'Rating should be 0 if there are no matches.');

        $battles[0]->votes_rapper1 = 0;
        $battles[0]->votes_rapper2 = 0;
        $battles[0]->save();
        $user1->updateRating();
        $user2->updateRating();

        // rating should be 0 for both users
        $this->assertEquals(0, $user1->rating);
        $this->assertEquals(0, $user2->rating);

        $battles[1]->votes_rapper1 = 1;
        $battles[1]->votes_rapper2 = 0;
        $battles[1]->save();
        $user1->updateRating();
        $user2->updateRating();

        // user1 has won once, user2 was defeated
        $this->assertEquals(3, $user1->rating);
        $this->assertEquals(1, $user2->rating);

        $battles[2]->votes_rapper1 = 0;
        $battles[2]->votes_rapper2 = 1;
        $battles[2]->save();
        $user1->updateRating();
        $user2->updateRating();

        // one win and one defeat for each user
        $this->assertEquals(4, $user1->rating);
        $this->assertEquals(4, $user2->rating);

        // TODO: why does the test for Battle::scopeCompleted() seem to work while
        // scopeCompleted does not filter the following open battle?
/*        $battles[3]->votes_rapper1 = 0;
        $battles[3]->votes_rapper2 = 1;
        $battles[3]->save();
        $user1->updateRating();
        $user2->updateRating();

        // battle is not closed yet, should not be considered for rating
        $this->assertEquals(4, $user1->rating);
        $this->assertEquals(4, $user2->rating);
*/
    }
}
