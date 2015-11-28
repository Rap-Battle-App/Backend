<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use FFMpeg\Filters\Video\ConcatFilter;

/**
 * Unit tests for the video subsystem
 * the tests will be skipped, if the input files are not present
 */
class VideoTest extends TestCase
{
    /**
     * This tests the video concatenation
     *
     * @return void
     */
    public function testConcatenation()
    {
        $input = ['./tests/Video/VID_20151120_114645.3gp',
                  './tests/Video/VID_20151120_114737.3gp',
                  './tests/Video/VID_20151126_105050.3gp'];

        if(!$this->check_input($input)) $this->markTestSkipped('Input files not found.');
        $event = new App\Events\VideoWasUploaded('./tests/Video/concat.mp4', $input);

        Event::fire($event);
    }

    /**
     * This tests the video conversion
     *
     * @return void
     */
    public function testConversion(){
        // This example file is smaller than the default resolution, it will be upscaled
        $input = ['./tests/Video/VID_20151120_114737_720.mp4'];

        if(!$this->check_input($input)) $this->markTestSkipped('Input file not found.');
        $event = new App\Events\VideoWasUploaded('./tests/Video/convert.mp4', $input);

        Event::fire($event);
    }

    /**
     * Checks if all files in $input exist
     *
     * @return bool true, if files exis
     */
    private function check_input(Array $input){
        foreach($input as $file){
            if(!file_exists($file)) return false;
        }
        return true;
    }
}
