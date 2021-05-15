<?php
namespace tests\Zeedhi\Framework\File\Image\Processor;

use Zeedhi\Framework\File\Image\Image;
use Zeedhi\Framework\File\Image\Processor\Resize;

class ResizeTest extends \PHPUnit\Framework\TestCase {

    const FILE_PATH = __DIR__.'/file.jpg';

    protected $resizeProcessor;

    public function setUp() {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('The tests require gd extension');
        }

        $this->resizeProcessor = new Resize(200);
    }

    public function tearDown() {
        if (file_exists(self::FILE_PATH)) {
            unlink(self::FILE_PATH);
        }
    }

    public function testApply() {
        $image = new Image(imagecreatetruecolor(100, 100), 'image/jpeg');
        $newImage = $this->resizeProcessor->apply($image);

        imagejpeg($newImage->getImage(), self::FILE_PATH);

        $imageSize = getimagesize(self::FILE_PATH);

        $this->assertEquals(200, $imageSize[0]);
        $this->assertEquals(200, $imageSize[1]);
    }

}