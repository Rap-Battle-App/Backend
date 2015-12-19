<?php

use App\Http\Controllers\SearchController;
use App\Models\Battle;
use App\Models\OpenBattle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ControllerSearchTest extends TestCase
{
    use WithoutMiddleware;

    //make database transactions temporary, does not seem to work?
    use DatabaseTransactions;

    /**
     * 
     *	Testing the SearchController
     * 
     */


   /**
     *  Creatied some users with a common name and then sent a short string 
     *  to get user 1,2,3, with string matching
     */
    public function testPostSearch()
    {
        //$this->withoutMiddleware();
        $user1 = factory(App\Models\User::class)->create(['username' => 'user1']);
        $user2 = factory(App\Models\User::class)->create(['username' => 'user12']);
        $user3 = factory(App\Models\User::class)->create(['username' => 'user123']);
        $user4 = factory(App\Models\User::class)->create(['username' => 'tomcruise1']);
        $user5 = factory(App\Models\User::class)->create(['username' => 'jack34']);
        


        $this->post('/search', ['search_string' => 'use']);
            
    
    }
	
}    
