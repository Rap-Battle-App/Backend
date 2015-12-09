<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerAuthTest extends TestCase
{

    public function testPostLogin()
    {
        
        $user = factory(App\Models\User::class)->create();

        $this->post('/auth/login', ['user' => $user]);
             //->seeJson([
             //   'user_id' => "$user->id",
             //]);

    }

    public function testPostRegister()
    {

        $this->post('/auth/register', ['username' => 'testuser', 'email' => 'testuser@testing.test', 'password' => 'secret']);

        //todo: verify that int is returned
             
    }

    public function testGetLogout()
    {
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/logout');

    }

    public function testGetId()
    {
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/id');
             //->seeJson([
             //   'user_id' => "$user->id",
             //]);
    }

}


