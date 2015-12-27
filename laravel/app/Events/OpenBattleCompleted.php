<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Storage;

use App\Models\OpenBattle;

class OpenBattleCompleted
{
    use SerializesModels;

    public $openBattle;
    public $outfilename;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OpenBattle $openBattle, $outfilename)
    {
        $this->openBattle = $openBattle;
        $this->outfilename = $outfilename;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
