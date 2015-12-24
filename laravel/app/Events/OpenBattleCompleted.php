<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Storage;

use App\Models\OpenBattle;

class OpenBattleCompleted extends VideoWasUploaded
{
    use SerializesModels;

    public $openBattle;
    public $outfilename;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OpenBattle $openBattle)
    {
        $this->openBattle = $openBattle;

        // input files in filesystem
        $infiles = array();
        $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($openBattle->rapper1_round1);
        $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($openBattle->rapper2_round1);
        $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($openBattle->rapper1_round2);
        $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($openBattle->rapper2_round2);

         // new video file name
        $this->outfilename = '' . $openBattle->id . '.mp4';
        $outfile = Storage::disk('videos')->getAdapter()->applyPathPrefix($this->outfilename);

        parent::__construct($outfile, $infiles, false);
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
