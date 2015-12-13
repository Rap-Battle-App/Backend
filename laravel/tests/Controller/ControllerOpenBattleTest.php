<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\OpenBattleController;

class ControllerOpenBattleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        /// TODO: write controller tests
        $this->markTestIncomplete();
    }

    /**
     * Test for 64bit string increment
     */
    public function testInt64Increment(){
        $openBattleController = new OpenBattleController;

        // use reflection to access private method
        $reflection = new \ReflectionClass(get_class($openBattleController));
        $method = $reflection->getMethod('int64Increment');
        $method->setAccessible(true);

        $this->assertEquals('00000000000000000000000000123abd',
                $method->invokeArgs($openBattleController, ['00000000000000000000000000123abc']));
        // before overflow low->center (see method definition)
        $this->assertEquals('00000000000000000000ffffffffffff',
                $method->invokeArgs($openBattleController, ['00000000000000000000fffffffffffe']));
        // overflow low->center
        $this->assertEquals('00000000000000000001000000000000',
                $method->invokeArgs($openBattleController, ['00000000000000000000ffffffffffff']));
        // before overflow center->high
        $this->assertEquals('0000000000ffffffffffffffffffffff',
                $method->invokeArgs($openBattleController, ['0000000000fffffffffffffffffffffe']));
        // overflow center->high
        $this->assertEquals('00000000010000000000000000000000',
                $method->invokeArgs($openBattleController, ['0000000000ffffffffffffffffffffff']));
        // overflow
        $this->assertEquals('ffffffffffffffffffffffffffffffff',
                $method->invokeArgs($openBattleController, ['fffffffffffffffffffffffffffffffe']));
        $this->assertEquals('00000000000000000000000000000000',
                $method->invokeArgs($openBattleController, ['ffffffffffffffffffffffffffffffff']));
    }

    /**
     * Test the video file hash to file name conversion
     */
    public function testFilePath()
    {
        $openBattleController = new OpenBattleController;

        // use reflection to access private method
        $reflection = new \ReflectionClass(get_class($openBattleController));
        $method = $reflection->getMethod('filePath');
        $method->setAccessible(true);

        $filename = hash('md5', 'semirandomteststring');
        $filenamepath = $method->invokeArgs($openBattleController, [$filename]);

        // compare resulting filename and path
        $this->assertEquals(substr($filename, 0, 2) . '/' . $filename, $filenamepath);
    }

    /**
     * Test whether a video file will be moved to the right location in the filesystem
     */
    public function testMoveVideoFile()
    {
        // create test file (copy)
        $file = './tests/Video/VID_20151120_114737_720.mp4';
        if(!file_exists($file)) $this->markTestSkipped('Input file not found.');
        copy($file, $input = $file . '.tmp');

        $openBattleController = new OpenBattleController;

        // use reflection to access private method
        $reflection = new \ReflectionClass(get_class($openBattleController));
        $method = $reflection->getMethod('moveVideoFile');
        $method->setAccessible(true);

        // move file
        $newfile = $method->invokeArgs($openBattleController, [$input]);

        // create reference path for comparation
        $hash = hash_file('md5', $file, true);
        $hashstr = bin2hex($hash);
        $filenameref = substr($hashstr, 0, 2) . '/' . $hashstr;

        // see if file is at the right location
        $this->assertTrue(Storage::disk('videos')->has($newfile), 'File does not exist at target location.');
        // check whether files are equal
        // create hash for file in filesystem
        $stream = Storage::disk('videos')->readStream($newfile);
        $hc = hash_init('md5');
        hash_update_stream($hc, $stream);
        $this->assertEquals(bin2hex($hash), hash_final($hc), 
                'Original and target file are not equal.');

        // delete test file(s)
        Storage::disk('videos')->delete($newfile);
    }
}
