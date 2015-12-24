<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Battle;
use App\Models\Vote;
use App\Models\BattleRequest;
use App\Models\OpenBattle;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // config
        $usercnt = 20;
        $userRappercnt = 20;
        $battlecnt = 20;
        $battleRequestcnt = rand($userRappercnt / 4, $userRappercnt / 2); // max $userRappercnt / 2
        $openBattlecnt = rand($userRappercnt / 4, $userRappercnt / 2); // max $userRappercnt / 2
 
        // create users
        $users = factory(App\Models\User::class, $usercnt)->create(); // rapper and non-rapper
        $usersRapper = factory(App\Models\User::class, 'rapper', $userRappercnt)->create(); // rapper only

        //----------------------------------------------
        // create battles
        for($i = 0; $i  < $battlecnt; $i++){
            
            $battle = new Battle;

            // get first rapper
            $battle->rapper1_id = $usersRapper->random()->id;
                
            // get second rapper != first rapper
            do {
                $battle->rapper2_id = $usersRapper->random()->id;
            } while($battle->rapper1_id == $battle->rapper2_id);

            $battle->save();

            //-----------------------------------------
            // create votes
            // create list of all created users
            $usersAll = $users->keyBy('id')->merge($usersRapper->keyBy('id'));
            $usersAll->shuffle();
            $userVotescnt = rand(0, $usersAll->count());

            for($j = 0; $j < $userVotescnt; $j++){
                $vote = new Vote;
                $vote->user_id = $usersAll->get($j)->id;
                $vote->battle_id = $battle->id;
                $vote->rapper_number = rand(0, 1);
                $vote->save();

                // update vote counter
                if($vote->rapper_number == 0){
                    $battle->votes_rapper1++;
                } else {
                    $battle->votes_rapper2++;
                }
            }

            // save vote count in battle
            $battle->save();
            $battle->rapper1->updateRating();
            $battle->rapper2->updateRating();
        }

        //----------------------------------------------
        // create battle_requests
        for($i = 0; $i < $battleRequestcnt*2; $i+=2){
            $battleRequest = new BattleRequest();
            $battleRequest->challenger_id = $usersRapper->get($i)->id;
            $battleRequest->challenged_id = $usersRapper->get($i+1)->id;
            $battleRequest->save();
        }
        
        //----------------------------------------------
        // create open battles
        $usersRapper->shuffle();

        for($i = 0; $i < $openBattlecnt*2; $i+=2){
            $openBattle = new OpenBattle;
            $openBattle->rapper1_id = $usersRapper->get($i)->id;
            $openBattle->rapper2_id = $usersRapper->get($i+1)->id;
            $openBattle->phase = rand(1, 2); // TODO: how many phases?
            $openBattle->beat1_id = rand(0, 2);
            $openBattle->beat2_id = rand(0, 2);
            $openBattle->save();
        }

        Model::reguard();
    }
}
