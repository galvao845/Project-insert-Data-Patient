<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;


use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\Remote\FileUploader\Server;
use Zeedhi\Framework\Remote\HttpInterface;
use Zeedhi\Framework\Remote\Exception;
use Zeedhi\Framework\Remote\ServerException;

class ServerTest extends \PHPUnit\Framework\TestCase {

    /** @var Server */
    protected $server;
    /** @var HttpInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpInterface;

    protected function setUp() {
        $this->httpInterface = $this->getMockBuilder(HttpInterface::class)->getMock();
        $this->server = new Server($this->httpInterface, 'apiKey');
    }

    public function testDeleteFile() {
        $filePath = "path/to/myFile.txt";
        $this->httpInterface->expects($this->once())->method('setHeaders')->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'Content-Type' => 'application/json'));
        $expectedRequestFields = array('row' => array('PATH' => $filePath), 'requestType' => 'Row');
        $this->httpInterface->expects($this->once())->method('request')->with('/delete', $expectedRequestFields)->willReturn('{}');
        $this->server->deleteFile("path/to/myFile.txt");
    }

    public function testUploadFilesInRow() {
        $fileData = array(
            'b64File' => '"data:text/plain;base64,LmlkZWEvKg0KbmJwcm9qZWN0LyoNCnRlc3QvbW9iaWxlL25vZGVfbW9kdWxlcy8qIA0KYXNzZXRzL3Nhc3MvdGhlbWUvLnNhc3MtY2FjaGUvKg0KLnNvbmFyLyoNCnRvb2xzL2FwYWNoZS1hY3RpdmVtcS01LjguMC8qDQouc2V0dGluZ3MvKg0KLmJ1aWxkcGF0aA0KLnByb2plY3QNCi9wcm9qZWN0cy90aW1ldHJhY2tlci9uYnByb2plY3QvcHJpdmF0ZS8NCi9wcm9qZWN0cy90b19kby9uYnByb2plY3QvcHJpdmF0ZS8NCiouc3VibGltZS13b3Jrc3BhY2UNCiouc3VibGltZS1wcm9qZWN0DQpiYWNrZW5kL3ZlbmRvci8qDQpiYWNrZW5kL2NvbXBvc2VyLmxvY2sNCmJvd2VyX2NvbXBvbmVudHMvKg0KZnJvbnRlbmQvbm9kZV9tb2R1bGVzLyoNCmJhY2tlbmQvdGVtcC8qDQp0ZXN0L21vYmlsZS9pbmRleC5odG1sDQp0ZXN0L2JhY2tlbmQvdmVuZG9yDQp0ZXN0L2JhY2tlbmQvY29tcG9zZXIubG9jaw0KdGVzdC9iYWNrZW5kL3VwbG9hZHMNCm5vZGVfbW9kdWxlcy8="',
            'lastModified' => 1450806562407,
            'lastModifiedDate' => '2015-12-22T17:49:22.407Z',
            'name' => '.gitignore',
            'size' => 488,
            'type' => 'text/plain',
            'webkitRelativePath' => '',
        );

        $expectedRequestFields = array('dataset' => array(array('FILE' => array($fileData), 'FOLDER' => 'custom_folder')), 'requestType' => 'DataSet');
        $this->httpInterface->expects($this->once())->method('setHeaders')->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'Content-Type' => 'application/json'));
        $this->httpInterface->expects($this->once())->method('setHeaders')->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'Content-Type' => 'application/json'));
        $requestReturn = array(
            'dataset' => array(
                'new_files' => array(
                    array('FILE' => array(array(
                        'DATE' => (new \DateTime())->format("d/m/Y H:i:s"),
                        'NAME' => '.gitignore',
                        'PATH' => 'org_folder/custom_folder/321321.txt',
                        'SIZE' => 488,
                        'TYPE' => 'text/plain',
                    )))
                )
            )
        );

        $this->httpInterface->expects($this->once())->method('request')->with('/upload', $expectedRequestFields)
            ->willReturn(json_encode($requestReturn));

        $row = new Row(array(
            'FILE'        => array($fileData),
            'OTHER_FIELD' => 'OTHER_VALUE'
        ));
        $this->server->uploadFilesInRow($row, 'FILE', 'custom_folder');

        $this->assertArrayHasKey('path', $row['FILE'][0]);
        $this->assertEquals('org_folder/custom_folder/321321.txt', $row['FILE'][0]['path']);
    }

    public function testRequestError() {
        $fileData = array(
            'b64File' => '"data:text/plain;base64,LmlkZWEvKg0KbmJwcm9qZWN0LyoNCnRlc3QvbW9iaWxlL25vZGVfbW9kdWxlcy8qIA0KYXNzZXRzL3Nhc3MvdGhlbWUvLnNhc3MtY2FjaGUvKg0KLnNvbmFyLyoNCnRvb2xzL2FwYWNoZS1hY3RpdmVtcS01LjguMC8qDQouc2V0dGluZ3MvKg0KLmJ1aWxkcGF0aA0KLnByb2plY3QNCi9wcm9qZWN0cy90aW1ldHJhY2tlci9uYnByb2plY3QvcHJpdmF0ZS8NCi9wcm9qZWN0cy90b19kby9uYnByb2plY3QvcHJpdmF0ZS8NCiouc3VibGltZS13b3Jrc3BhY2UNCiouc3VibGltZS1wcm9qZWN0DQpiYWNrZW5kL3ZlbmRvci8qDQpiYWNrZW5kL2NvbXBvc2VyLmxvY2sNCmJvd2VyX2NvbXBvbmVudHMvKg0KZnJvbnRlbmQvbm9kZV9tb2R1bGVzLyoNCmJhY2tlbmQvdGVtcC8qDQp0ZXN0L21vYmlsZS9pbmRleC5odG1sDQp0ZXN0L2JhY2tlbmQvdmVuZG9yDQp0ZXN0L2JhY2tlbmQvY29tcG9zZXIubG9jaw0KdGVzdC9iYWNrZW5kL3VwbG9hZHMNCm5vZGVfbW9kdWxlcy8="',
            'lastModified' => 1450806562407,
            'lastModifiedDate' => '2015-12-22T17:49:22.407Z',
            'name' => '.gitignore',
            'size' => 488,
            'type' => 'text/plain',
            'webkitRelativePath' => '',
        );

        $expectedRequestFields = array('dataset' => array(array('FILE' => array($fileData), 'FOLDER' => 'custom_folder')), 'requestType' => 'DataSet');
        $this->httpInterface->expects($this->once())->method('setHeaders')->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'Content-Type' => 'application/json'));
        $this->httpInterface->expects($this->once())->method('request')->with('/upload', $expectedRequestFields)
            ->willReturn(json_encode(array('error' => 'Internal server error.')));

        $row = new Row(array('FILE' => array($fileData), 'OTHER_FIELD' => 'OTHER_VALUE'));
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error in remote server: Internal server error.');
        $this->server->uploadFilesInRow($row, 'FILE', 'custom_folder');
    }

    public function testMalFormattedResponse() {
        try {

            $fileData = array(
                'b64File' => '"data:text/plain;base64,LmlkZWEvKg0KbmJwcm9qZWN0LyoNCnRlc3QvbW9iaWxlL25vZGVfbW9kdWxlcy8qIA0KYXNzZXRzL3Nhc3MvdGhlbWUvLnNhc3MtY2FjaGUvKg0KLnNvbmFyLyoNCnRvb2xzL2FwYWNoZS1hY3RpdmVtcS01LjguMC8qDQouc2V0dGluZ3MvKg0KLmJ1aWxkcGF0aA0KLnByb2plY3QNCi9wcm9qZWN0cy90aW1ldHJhY2tlci9uYnByb2plY3QvcHJpdmF0ZS8NCi9wcm9qZWN0cy90b19kby9uYnByb2plY3QvcHJpdmF0ZS8NCiouc3VibGltZS13b3Jrc3BhY2UNCiouc3VibGltZS1wcm9qZWN0DQpiYWNrZW5kL3ZlbmRvci8qDQpiYWNrZW5kL2NvbXBvc2VyLmxvY2sNCmJvd2VyX2NvbXBvbmVudHMvKg0KZnJvbnRlbmQvbm9kZV9tb2R1bGVzLyoNCmJhY2tlbmQvdGVtcC8qDQp0ZXN0L21vYmlsZS9pbmRleC5odG1sDQp0ZXN0L2JhY2tlbmQvdmVuZG9yDQp0ZXN0L2JhY2tlbmQvY29tcG9zZXIubG9jaw0KdGVzdC9iYWNrZW5kL3VwbG9hZHMNCm5vZGVfbW9kdWxlcy8="',
                'lastModified' => 1450806562407,
                'lastModifiedDate' => '2015-12-22T17:49:22.407Z',
                'name' => '.gitignore',
                'size' => 488,
                'type' => 'text/plain',
                'webkitRelativePath' => '',
            );

            $expectedRequestFields = array('dataset' => array(array('FILE' => array($fileData), 'FOLDER' => 'custom_folder')), 'requestType' => 'DataSet');
            $this->httpInterface->expects($this->once())->method('setHeaders')->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'Content-Type' => 'application/json'));
            $this->httpInterface->expects($this->once())->method('request')->with('/upload', $expectedRequestFields)
                ->willReturn("408 - Request Timeout");

            $row = new Row(array('FILE' => array($fileData), 'OTHER_FIELD' => 'OTHER_VALUE'));
            $this->server->uploadFilesInRow($row, 'FILE', 'custom_folder');
        } catch (ServerException $e) {
            $this->assertEquals('Error parsing response', $e->getMessage());
            $this->assertEquals('408 - Request Timeout', $e->getResponseBody());
            $this->assertEquals(ServerException::RESPONSE_ERROR, $e->getCode());
        }
    }

    public function testSendHeaders() {
        $headers = array(
            'name' => 'value'
        );
        $this->httpInterface = $this->getMockBuilder(HttpInterface::class)->getMock();
        $this->server = new Server($this->httpInterface, 'apiKey', $headers);

        $this->httpInterface->expects($this->once())
                            ->method('setHeaders')
                            ->with(array('apiKey' => 'apiKey', 'userId' => 'userId', 'name' => 'value', 'Content-Type' => 'application/json'));

        $expectedRequestFields = array('row' => array('PATH' => 'path/to/myFile.txt'), 'requestType' => 'Row');

        $this->httpInterface->expects($this->once())
                            ->method('request')
                            ->with('/delete', $expectedRequestFields)
                            ->willReturn('{}');

        $this->server->deleteFile('path/to/myFile.txt');
    }

}