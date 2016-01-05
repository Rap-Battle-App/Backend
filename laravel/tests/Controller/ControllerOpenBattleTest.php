<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\OpenBattleController;

class ControllerOpenBattleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetBattle()
    {
        $battle = factory(App\Models\OpenBattle::class)->create();
        
        $this->get('/open-battle/{id}' , ['id'=> $battle->id])->seeJson([
                
                    'id' => $battle->id
                
            ]); 
    }
    public function testPostRound()
    {
        $battle = factory(App\Models\OpenBattle::class)->create(['phase' => 1]);
        
        $this->get('/open-battle/{id}/round' , ['id'=> $battle->id , 'beat_id'=>2 , 'video'=>"abc.mp4"])->seeJson([
                
                    'phase' => 2
                
            ]); 
    }


}
