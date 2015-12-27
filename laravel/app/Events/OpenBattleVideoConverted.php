<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Models\OpenBattle;

class OpenBattleVideoConverted extends Event
{
    use SerializesModels;

    public $battle;
    public $videoFilename;
    public $videoColumn;
    public $rapperNumber;
    public $beatId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OpenBattle $battle, $videoFilename, $videoColumn, $rapperNumber, $beatId)
    {
        $this->battle = $battle;
        $this->videoFilename = $videoFilename;
        $this->videoColumn = $videoColumn;
        $this->rapperNumber = $rapperNumber;
        $this->beatId = $beatId;
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
