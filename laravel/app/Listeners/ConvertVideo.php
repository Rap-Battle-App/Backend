<?php

namespace App\Listeners;

use App\Events\SomeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\VideoWasUploaded;
use FFMpeg;
use FFMpeg\Media\Video;
use FFMpeg\Filters\Video\ConcatFilter;

class ConvertVideo
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
     * @param  VideoWasUploaded  $event
     * @return void
     */
    public function handle(VideoWasUploaded $event)
    {
        if(empty($event->outfile) || empty($event->infiles)){
            throw new InvalidArgumentException('No video files given');
        } else {
            $ffmpeg = FFMpeg\FFMpeg::create();

            // create video objects
            foreach($event->infiles as $file){
                $videos[] = $ffmpeg->open($file);
            }

            // if multiple input files: create concatenation filter
            if(count($videos) > 1){ // convert single file
                // create filter
                $concatfilter = new ConcatFilter;
                $videos[0]->addFilter($concatfilter);

                // add videos
                for($i = 1; $i < count($videos); $i++){
                    $concatfilter->addVideo($videos[$i]);
                }
            }

            // set video format
            $format = new FFMpeg\Format\Video\X264('libmp3lame');

            // save video
            $videos[0]->save($format, $event->outfile);
        }
    }
}
