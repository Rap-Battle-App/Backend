<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Storage;
use Log;

use App\Events\VideoWasUploaded;
use App\Events\OpenBattleVideoConverted;
use App\Events\OpenBattleCompleted;

class UpdateOpenBattle
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OpenBattleVideoConverted  $event
     * @return void
     */
    public function handle(OpenBattleVideoConverted $event)
    {
        Log::info('Updating OpenBattle', ['battle' => $event->battle->id, 'phase' => $event->battle->phase]);

        $event->battle[$event->videoColumn] = $event->videoFilename;

        // Set beat id
        if ($event->battle->phase == 1) {
            $event->battle['beat'.$event->rapperNumber.'_id'] = $event->beatId;
            // Go to phase 2 if both 1st rounds are uploaded
            if ($event->battle->hasFirstRounds()) {
                Log::debug('Updating OpenBattle: changing phase (before)',
                        ['battle' => $event->battle->id, 'phase' => $event->battle->phase]);
                $event->battle->setPhaseAttribute(2);
                Log::debug('Updating OpenBattle: changing phase (after)',
                        ['battle' => $event->battle->id, 'phase' => $event->battle->phase]);
            }
        } else if ($event->battle->phase == 2 && $event->battle->hasAllRounds()) {
            // note: battle done, concat video, create battle, delete open battle - maybe need to do this when conversion is done
            // input files in filesystem
            $infiles = array();
            $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($event->battle->rapper1_round1);
            $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($event->battle->rapper2_round1);
            $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($event->battle->rapper2_round2);
            $infiles[] = Storage::disk('videos')->getAdapter()->applyPathPrefix($event->battle->rapper1_round2);

            // new video file name
            $this->outfilename = '' . $event->battle->id . '.mp4';
            $outfile = Storage::disk('videos')->getAdapter()->applyPathPrefix($this->outfilename);

            // concatenates the videos, converts the OpenBattle to a Battle and deletes the old video files
            \Event::fire(new VideoWasUploaded($outfile, $infiles, false,
                    new OpenBattleCompleted($event->battle, $this->outfilename)));
        }
    }
    $event->battle->save();
}
