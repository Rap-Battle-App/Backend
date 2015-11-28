<?php

namespace App\Listeners;

use App\Events\SomeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\VideoWasUploaded;
use FFMpeg;
use FFMpeg\Media\Video;
use FFMpeg\Filters\Video\ConcatFilter;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Coordinate\Dimension;

/**
 * This listener will be called to convert single videos or to concatenate
 * multiple videos. Single videos will be resized if necessary, multiple
 * videos have to be the same size.
 */
class ConvertVideo implements ShouldQueue
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

            /**
             * The concatenation filter needs all videos to be the same size
             * therefore the resize filter will be added if only a single video
             * will be converted, otherwise the concatenation filter will be used
             */
            if(count($videos) == 1){ // only one input file
                // add resize filter
                $width = config('rap-battle.video_width', 1920);
                $height = config('rap-battle.video_height', 1080);
                $resizefilter = new ResizeFilter(new Dimension($width, $height), ResizeFilter::RESIZEMODE_INSET);
                $videos[0]->addFilter($resizefilter);
            } else { // multiple input files: create concatenation filter
                // create concat filter
                $concatfilter = new ConcatFilter;
                $videos[0]->addFilter($concatfilter);

                // add videos
                for($i = 1; $i < count($videos); $i++){
                    $concatfilter->addVideo($videos[$i]);
                }
            }

            // set video format
            $format = new FFMpeg\Format\Video\X264('libmp3lame');

            $videobitrate = config('rap-battle.video_bitrate', $format->getKiloBitrate());
            $format->setKiloBitrate($videobitrate);
            $audiobitrate = config('rap-battle.audio_bitrate', $format->getAudioKiloBitrate());
            $format->setAudioKiloBitrate($audiobitrate);
            $audiocodec = config('rap-battle.audio_codec', $format->getAudioCodec());
            $format->setAudioCodec($audiocodec);

            // save video
            $videos[0]->save($format, $event->outfile);
        }
    }
}
