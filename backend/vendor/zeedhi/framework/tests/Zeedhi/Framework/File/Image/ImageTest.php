<?php
namespace tests\Zeedhi\Framework\File\Image;

use Zeedhi\Framework\File\Image\Image;

class ImageTest extends \PHPUnit\Framework\TestCase {

    public function setUp() {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('The tests require gd extension');
        }
    }

    public function testSettersAndGetters() {
        $image = imagecreatetruecolor(10, 10);
        $type = 'image/jpeg';

        $newImage = imagecreatetruecolor(10, 10);
        $newType = 'image/png';

        $imageObj = new Image($image, $type);

        $this->assertEquals($image, $imageObj->getImage());
        $imageObj->setImage($newImage);
        $this->assertEquals($newImage, $imageObj->getImage());

        $this->assertEquals($type, $imageObj->getMimeType());
        $imageObj->setMimeType($newType);
        $this->assertEquals($newType, $imageObj->getMimeType());
    }

}