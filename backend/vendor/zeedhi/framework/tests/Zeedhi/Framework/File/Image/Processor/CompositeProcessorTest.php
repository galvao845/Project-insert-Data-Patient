<?php
namespace tests\Zeedhi\Framework\File\Image\Processor;

use Zeedhi\Framework\File\Image\Image;
use Zeedhi\Framework\File\Image\Processor\CompositeProcessor;
use Zeedhi\Framework\File\Image\Processor\Processor;

class CompositeProcessorTest extends \PHPUnit\Framework\TestCase {

    protected $firstProcessor;
    protected $secondProcessor;
    protected $compositeProcessor;

    public function setUp() {
        $this->firstProcessor = $this->getMockForAbstractClass(Processor::class);
        $this->secondProcessor = $this->getMockForAbstractClass(Processor::class);
        $this->compositeProcessor = new CompositeProcessor($this->firstProcessor, $this->secondProcessor);
    }

    public function testApply() {
        $image = new Image('image', 'type');
        $secondImage = new Image('image2', 'type');
        $thirdImage = new Image('image3', 'type');

        $this->firstProcessor->expects($this->once())
                             ->method('apply')
                             ->with($image)
                             ->will($this->returnValue($secondImage));

        $this->secondProcessor->expects($this->once())
                             ->method('apply')
                             ->with($secondImage)
                             ->will($this->returnValue($thirdImage));


        $returnedImage = $this->compositeProcessor->apply($image);

        $this->assertEquals($thirdImage, $returnedImage);
    }

}