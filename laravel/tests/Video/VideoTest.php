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
        $output = './tests/Video/concat.mp4';

        if(!$this->checkFilesExist($input)) $this->markTestSkipped('Input files not found.');
        $event = new App\Events\VideoWasUploaded($output, $input);

        Event::fire($event);
        
        $this->assertTrue($this->checkFilesExist($input));
        $this->assertTrue($this->checkFilesExist($output));
    }

    /**
     * This tests the video conversion
     *
     * @return void
     */
    public function testConversion()
    {
        // This example file is smaller than the default resolution, it will be upscaled
        $input = ['./tests/Video/VID_20151120_114737_720.mp4'];
        $output = './tests/Video/convert.mp4';

        if(!$this->checkFilesExist($input)) $this->markTestSkipped('Input file not found.');
        $event = new App\Events\VideoWasUploaded($output, $input);

        Event::fire($event);

        $this->assertTrue($this->checkFilesExist($input));
        $this->assertTrue($this->checkFilesExist($output));
    }

    /**
     * This tests the video conversion and deletion of original file
     *
     * @return void
     */
    public function testConversionDeletion()
    {
        // This example file is smaller than the default resolution, it will be upscaled
        $input = ['./tests/Video/VID_20151120_114737_720.mp4'];
        $output = './tests/Video/convertDelete.mp4';

        if(!$this->checkFilesExist($input)) $this->markTestSkipped('Input file not found.');
        copy($input[0], $input[0] . '.tmp');
        $input = ['./tests/Video/VID_20151120_114737_720.mp4.tmp'];

        $event = new App\Events\VideoWasUploaded($output, $input, true);

        Event::fire($event);

        // input file should be deleted now
        $this->assertFalse($this->checkFilesExist($input));
        $this->assertTrue($this->checkFilesExist($output));
    }

    /**
     * Checks if all files in $input exist
     *
     * @return bool true, if files exis
     */
    private function checkFilesExist($input)
    {
        // make $input an array if it isn't one already
        if(!is_array($input)) $input = [ $input ];

        foreach($input as $file){
            if(!file_exists($file)) return false;
        }
        return true;
    }
}
