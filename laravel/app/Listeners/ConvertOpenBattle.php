<?php

namespace App\Listeners;

use App\Events\OpenBattleCompleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Battle;

/**
 * This Listener converts an OpenBattle to a Battle
 */
class ConvertOpenBattle implements ShouldQueue
// This listener should queue so it will be called after the video concatenation job (they're dependant)
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  OpenBattleCompleted  $event
     * @return void
     */
    public function handle(OpenBattleCompleted $event)
    {
        $openBattle = $event->openBattle;

        // create new Battle
        $battle = new Battle;
        $battle->rapper1_id = $openBattle->rapper1_id;
        $battle->rapper2_id = $openBattle->rapper2_id;
        // TODO: check / wait if file doesn't exist
        $battle->video = $event->outfilename;
        $battle->save();

        // delete OpenBattle
        $openBattle->delete();
    }
}
