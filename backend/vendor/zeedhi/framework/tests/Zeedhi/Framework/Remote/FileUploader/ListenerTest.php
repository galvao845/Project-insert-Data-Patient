<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DataSource;
use Zeedhi\Framework\DTO;
use Zeedhi\Framework\Remote\FileUploader\Listener;
use Zeedhi\Framework\Remote\FileUploader\Server;
use Zeedhi\Framework\Remote\HttpInterface;

/**
 * Created by PhpStorm.
 * User: pauloneto
 * Date: 27/07/2016
 * Time: 19:01
 */
class ListenerTest extends \PHPUnit\Framework\TestCase {

    /** @var \Zeedhi\Framework\Remote\FileUploader\Listener */
    protected $listener;

    /** @var Server|\PHPUnit_Framework_MockObject_MockObject */
    protected $serverMock;

    protected function setUp() {
        $this->serverMock = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();
        $this->listener = new Listener($this->serverMock, array(
            '/upload' => array(
                'fileField'    => 'FILE',
                'customFolder' => 'custom_folder'
            )
        ));
    }

    public function testNotUploadRoute() {
        $this->serverMock->expects($this->never())->method($this->anything());
        $this->listener->preDispatch(new DTO\Request\DataSet(
            new DataSource\DataSet('notUpload', array()),
            HttpInterface::METHOD_POST,
            '/not_upload',
            'userId'
        ));
    }

    public function testUploadRouteWithSingleRow() {
        $file = array(
            'b64File' => '"data:text/plain;base64,LmlkZWEvKg0KbmJwcm9qZWN0LyoNCnRlc3QvbW9iaWxlL25vZGVfbW9kdWxlcy8qIA0KYXNzZXRzL3Nhc3MvdGhlbWUvLnNhc3MtY2FjaGUvKg0KLnNvbmFyLyoNCnRvb2xzL2FwYWNoZS1hY3RpdmVtcS01LjguMC8qDQouc2V0dGluZ3MvKg0KLmJ1aWxkcGF0aA0KLnByb2plY3QNCi9wcm9qZWN0cy90aW1ldHJhY2tlci9uYnByb2plY3QvcHJpdmF0ZS8NCi9wcm9qZWN0cy90b19kby9uYnByb2plY3QvcHJpdmF0ZS8NCiouc3VibGltZS13b3Jrc3BhY2UNCiouc3VibGltZS1wcm9qZWN0DQpiYWNrZW5kL3ZlbmRvci8qDQpiYWNrZW5kL2NvbXBvc2VyLmxvY2sNCmJvd2VyX2NvbXBvbmVudHMvKg0KZnJvbnRlbmQvbm9kZV9tb2R1bGVzLyoNCmJhY2tlbmQvdGVtcC8qDQp0ZXN0L21vYmlsZS9pbmRleC5odG1sDQp0ZXN0L2JhY2tlbmQvdmVuZG9yDQp0ZXN0L2JhY2tlbmQvY29tcG9zZXIubG9jaw0KdGVzdC9iYWNrZW5kL3VwbG9hZHMNCm5vZGVfbW9kdWxlcy8="',
            'lastModified' => 1450806562407,
            'lastModifiedDate' => '2015-12-22T17:49:22.407Z',
            'name' => '.gitignore',
            'size' => 488,
            'type' => 'text/plain',
            'webkitRelativePath' => '',
        );

        $row = new DTO\Row(array(
            'FILE'   => array($file),
            'OTHER_COLUMN' => 'OTHER_VALUE'
        ));

        $this->serverMock->expects($this->once())->method('uploadFilesInRow')->with($row, 'FILE', 'custom_folder');
        $this->serverMock->expects($this->once())->method('uploadFilesInRow')->with($row, 'FILE', 'custom_folder');
        $this->listener->preDispatch(new DTO\Request\Row(
            $row,
            HttpInterface::METHOD_POST,
            '/upload',
            'userId'
        ));
    }

    public function testUploadRowWithDataSet() {
        $file = array(
            'b64File' => '"data:text/plain;base64,LmlkZWEvKg0KbmJwcm9qZWN0LyoNCnRlc3QvbW9iaWxlL25vZGVfbW9kdWxlcy8qIA0KYXNzZXRzL3Nhc3MvdGhlbWUvLnNhc3MtY2FjaGUvKg0KLnNvbmFyLyoNCnRvb2xzL2FwYWNoZS1hY3RpdmVtcS01LjguMC8qDQouc2V0dGluZ3MvKg0KLmJ1aWxkcGF0aA0KLnByb2plY3QNCi9wcm9qZWN0cy90aW1ldHJhY2tlci9uYnByb2plY3QvcHJpdmF0ZS8NCi9wcm9qZWN0cy90b19kby9uYnByb2plY3QvcHJpdmF0ZS8NCiouc3VibGltZS13b3Jrc3BhY2UNCiouc3VibGltZS1wcm9qZWN0DQpiYWNrZW5kL3ZlbmRvci8qDQpiYWNrZW5kL2NvbXBvc2VyLmxvY2sNCmJvd2VyX2NvbXBvbmVudHMvKg0KZnJvbnRlbmQvbm9kZV9tb2R1bGVzLyoNCmJhY2tlbmQvdGVtcC8qDQp0ZXN0L21vYmlsZS9pbmRleC5odG1sDQp0ZXN0L2JhY2tlbmQvdmVuZG9yDQp0ZXN0L2JhY2tlbmQvY29tcG9zZXIubG9jaw0KdGVzdC9iYWNrZW5kL3VwbG9hZHMNCm5vZGVfbW9kdWxlcy8="',
            'lastModified' => 1450806562407,
            'lastModifiedDate' => '2015-12-22T17:49:22.407Z',
            'name' => '.gitignore',
            'size' => 488,
            'type' => 'text/plain',
            'webkitRelativePath' => '',
        );

        $row = new DTO\Row(array(
            'FILE'   => array($file),
            'OTHER_COLUMN' => 'OTHER_VALUE'
        ));

        $this->serverMock->expects($this->once())->method('uploadFilesInRow')->with($row, 'FILE', 'custom_folder');
        $this->serverMock->expects($this->once())->method('uploadFilesInRow')->with($row, 'FILE', 'custom_folder');
        $this->listener->preDispatch(new DTO\Request\DataSet(
            new DataSource\DataSet("upload", array($row)),
            HttpInterface::METHOD_POST,
            '/upload',
            'userId'
        ));
    }
}
