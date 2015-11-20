<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

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

    /**
     * Create a new event instance.
     *
     * @param $outfile path and name of the resulting video file will be
     * @param $infile array of the input video files
     *          add multiple files to concatenate them
     *
     * @return void
     */
    public function __construct($outfile, Array $infiles)
    {
        $this->outfile = $outfile;
        $this->infiles = $infiles;
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
