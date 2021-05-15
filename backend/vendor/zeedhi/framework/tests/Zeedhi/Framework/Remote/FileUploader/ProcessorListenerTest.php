<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\File\Image\Image;
use Zeedhi\Framework\File\Image\Processor\Processor;
use Zeedhi\Framework\Remote\FileUploader\ProcessorListener;

class ProcessorListenerTest extends \PHPUnit\Framework\TestCase {

    protected $processor;
    protected $processorListener;

    public function setUp() {
        $this->processor = $this->getMockBuilder(Processor::class)
                                ->getMock();

        $configuration = array(
            '/route' => array(
                'originalFileField' => 'originalField',
                'generatedFileField' => 'generatedField'
            )
        );

        $this->processorListener = $this->getMockBuilder(ProcessorListener::class)
                                        ->setMethods(array('createImageFromBase64', 'getImageInfo'))
                                        ->setConstructorArgs(array($this->processor, $configuration))
                                        ->getMock();
    }

    public function testPreDispatchRow() {
        $image = new Image(null, 'image/png');

        $this->processorListener->expects($this->once())
                                ->method('createImageFromBase64')
                                ->with('data:image/png;base64,image')
                                ->will($this->returnValue($image));

        $processedImage = new Image(null, 'image/png');

        $this->processor->expects($this->once())
                        ->method('apply')
                        ->with($image)
                        ->will($this->returnValue($processedImage));

        $imageInfo = array(
            'base64' => 'imageBase64',
            'size' => 123
        );

        $this->processorListener->expects($this->once())
                                ->method('getImageInfo')
                                ->with($processedImage)
                                ->will($this->returnValue($imageInfo));

        $row = new Row(array(
            'originalField' => array(array(
                'name' => 'test.png',
                'b64File' => 'data:image/png;base64,image'
            ))
        ));
        $request = new Request\Row($row, 'POST', '/route', 'user-1');

        $this->processorListener->preDispatch($request);

        $this->assertEquals($row['generatedField'], array(
            array(
                'b64File' => 'imageBase64',
                'name' => 'test.png',
                'size' => 123,
                'type' => 'image/png'
            )
        ));
    }

    public function testPreDispatchDataSet() {
        $image = new Image(null, 'image/png');

        $this->processorListener->expects($this->once())
                                ->method('createImageFromBase64')
                                ->with('data:image/png;base64,image')
                                ->will($this->returnValue($image));

        $processedImage = new Image(null, 'image/png');

        $this->processor->expects($this->once())
                        ->method('apply')
                        ->with($image)
                        ->will($this->returnValue($processedImage));

        $imageInfo = array(
            'base64' => 'imageBase64',
            'size' => 123
        );

        $this->processorListener->expects($this->once())
                                ->method('getImageInfo')
                                ->with($processedImage)
                                ->will($this->returnValue($imageInfo));

        $row = new Row(array(
            'originalField' => array(array(
                'name' => 'test.png',
                'b64File' => 'data:image/png;base64,image'
            ))
        ));

        $dataSet = new DataSet('', array($row));

        $request = new Request\DataSet($dataSet, 'POST', '/route', 'user-1');

        $this->processorListener->preDispatch($request);

        $this->assertEquals($row['generatedField'], array(
            array(
                'b64File' => 'imageBase64',
                'name' => 'test.png',
                'size' => 123,
                'type' => 'image/png'
            )
        ));
    }

}