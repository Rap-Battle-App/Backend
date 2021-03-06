<?php

namespace App\Listeners;

use App\Events\SomeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

use App\Events\VideoWasUploaded;
use FFMpeg;
use FFMpeg\Media\Video;
use FFMpeg\Filters\Audio\SimpleFilter;
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
     * @param  VideoWasUploaded  $event
     * @return void
     */
    public function handle(VideoWasUploaded $event)
    {
        Log::info('Calling video conversion handler', ['infiles' => $event->infiles,
                                                    'outfile' => $event->outfile]);

        if(empty($event->outfile) || empty($event->infiles)){
            throw new InvalidArgumentException('No video files given');
        } else {
            // get ffmpeg configuration
            $ffmpegBinary = config('rap-battle.ffmpeg_binary');
            $ffprobeBinary = config('rap-battle.ffprobe_binary');
            $timeout = config('rap-battle.ffmpeg_timeout', 3600);
            $ffmpegThreads = config('rap-battle.ffmpeg_threads', 12);

            $conf = array();
            if(!empty($ffmpegBinary))   $conf['ffmpeg.binaries']  = $ffmpegBinary;
            if(!empty($ffprobeBinary))  $conf['ffprobe.binaries'] = $ffprobeBinary;
            if(!empty($timeout))        $conf['timeout']          = $timeout;
            if(!empty($ffmpegThreads))  $conf['ffmpeg.threads']   = $ffmpegThreads;
            
            $ffmpeg = FFMpeg\FFMpeg::create($conf);

            // create video objects
            foreach($event->infiles as $file){
                $videos[] = $ffmpeg->open($file);
            }

            $width = config('rap-battle.video_width', 1920);
            $height = config('rap-battle.video_height', 1080);

            /**
             * The concatenation filter needs all videos to be the same size
             * therefore the resize filter will be added if only a single video
             * will be converted, otherwise the concatenation filter will be used
             */
            if(count($videos) == 1){ // only one input file
                // add resize filter
                // $resizefilter = new ResizeFilter(new Dimension($width, $height), ResizeFilter::RESIZEMODE_INSET);
                $resizefilter = new ResizeFilter(new Dimension($width, $height), ResizeFilter::RESIZEMODE_FIT);
                $videos[0]->addFilter($resizefilter);

                // apply setsar filter to correct pixel aspect ratio
                $setdar = new SimpleFilter(array('-vf', 'setdar=' . $height . '/' . $width));
                $videos[0]->addFilter($setdar);
            } else { // multiple input files: create concatenation filter
                // create concat filter
                $concatfilter = new ConcatFilter;
                $videos[0]->addFilter($concatfilter);

                // add videos
                for($i = 1; $i < count($videos); $i++){
                    $concatfilter->addVideo($videos[$i]);
                }
            }

            // create simple filter to add '-movflags faststart' parameter (to enable streaming)
            $movflags = new SimpleFilter(array('-movflags', 'faststart'));
            $videos[0]->addFilter($movflags);

            // set video format
            $format = new FFMpeg\Format\Video\X264(/*'libmp3lame'*/);

            $videobitrate = config('rap-battle.video_bitrate', $format->getKiloBitrate());
            $format->setKiloBitrate($videobitrate);
            $audiobitrate = config('rap-battle.audio_bitrate', $format->getAudioKiloBitrate());
            $format->setAudioKiloBitrate($audiobitrate);
            //$audiocodec = config('rap-battle.audio_codec', $format->getAudioCodec());
            //$format->setAudioCodec($audiocodec);
            $format->setAudioCodec(null);

            // convert / concatenate video
            try {
                Log::info('Starting video conversion', ['infiles' => $event->infiles,
                                                        'outfiles' => $event->outfile]);

                $videos[0]->save($format, $event->outfile);
                Log::info('Video conversion successful', ['infiles' => $event->infiles,
                                                        'outfiles' => $event->outfile]);
            } catch(Exception $e){
                // TODO: handle exception: video could not be converted / concatenated
                Log::error('Video conversion failed', ['exception' => $e->getMessage(),
                                                    'infiles' => $event->infiles,
                                                    'outfile' => $event->outfile]);
                return true;
                //throw $e;
            }

            // delete original video file(s) after successful conversion
            if($event->deleteOnSuccess)
                foreach($event->infiles as $file) unlink($file);
            // fire following event
            if($event->followingEvent != null) \Event::fire($event->followingEvent);
        }
    }
}
