<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\User;

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
     * test for scopeRapper()
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
     * test for scopeRatedBetween()
     */
    public function  testScopeRatedBetween()
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
}
