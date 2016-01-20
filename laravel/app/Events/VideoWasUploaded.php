<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Log;

/**
 * This Event calls the video conversion listener which will convert (and
 * concatenate, if multiple files given) the file to a common format
 *
 */
class VideoWasUploaded extends Event
{
    use SerializesModels;

    public $outfile;
    public $infiles;
    public $deleteOnSuccess;
    public $followingEvent;

    /**
     * Create a new event instance.
     *
     * @param $outfile path and name of the resulting video file will be
     * @param $infile array of the input video files add multiple files to concatenate them
     * @param $deleteOnSuccess delete the original video file if the conversion succeeded
     * @param $followingEvent will be called after successsful video conversion
     *
     * @return void
     */
    public function __construct($outfile, $infiles, $deleteOnSuccess = false, $followingEvent = null)
    {
        if(!is_array($infiles)) $infiles = [$infiles];

        $this->outfile = $outfile;
        $this->infiles = $infiles;
        $this->deleteOnSuccess = $deleteOnSuccess;
        $this->followingEvent = $followingEvent;

        Log::info('Video conversion event created', ['infiles' => $infiles,
                                                    'outfile' => $outfile,
                                                    'deleteOnSuccess' => $deleteOnSuccess]);
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
