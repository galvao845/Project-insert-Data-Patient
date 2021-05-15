<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\File\Image\Image;
use Zeedhi\Framework\File\Image\Processor\Processor;
use Zeedhi\Framework\Remote\FileUploader\Server;
use Zeedhi\Framework\Remote\FileUploader\UploadFilesListener;

class UploadFilesListenerTest extends \PHPUnit\Framework\TestCase {

    protected $server;
    protected $listener;

    public function setUp() {
        $this->server = $this->getMockBuilder(Server::class)
                             ->setMethods(array('uploadFilesInRow'))
                             ->disableOriginalConstructor()
                             ->getMock();

        $configuration = array(
            '/route' => array(
                'fileFields' => ['imageField-1', 'imageField-2'],
                'customFolder' => 'folderPath'
            )
        );

        $this->listener = new UploadFilesListener($this->server, $configuration);
    }

    public function testPreDispatchRow() {
        $row = new Row(array(
            'imageField-1' => array(array(
                'name' => 'test-1.png',
                'b64File' => 'data:image/png;base64,image-1'
            )),
            'imageField-2' => array(array(
                'name' => 'test-2.png',
                'b64File' => 'data:image/png;base64,image-2'
            ))
        ));

        $this->server->expects($this->at(0))
                     ->method('uploadFilesInRow')
                     ->with($row, 'imageField-1', 'folderPath');

        $this->server->expects($this->at(1))
                     ->method('uploadFilesInRow')
                     ->with($row, 'imageField-2', 'folderPath');

        $request = new Request\Row($row, 'POST', '/route', 'user-1');

        $this->listener->preDispatch($request);
    }

    public function testPreDispatchDataSet() {
        $row = new Row(array(
            'imageField-1' => array(array(
                'name' => 'test-1.png',
                'b64File' => 'data:image/png;base64,image-1'
            )),
            'imageField-2' => array(array(
                'name' => 'test-2.png',
                'b64File' => 'data:image/png;base64,image-2'
            ))
        ));

        $this->server->expects($this->at(0))
                     ->method('uploadFilesInRow')
                     ->with($row, 'imageField-1', 'folderPath');

        $this->server->expects($this->at(1))
                     ->method('uploadFilesInRow')
                     ->with($row, 'imageField-2', 'folderPath');

        $dataSet = new DataSet('', array($row));

        $request = new Request\DataSet($dataSet, 'POST', '/route', 'user-1');

        $this->listener->preDispatch($request);
    }

}