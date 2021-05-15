<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\DTO\Response\File;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DTO\Response\Error;
use Zeedhi\Framework\DTO\Response\Method;
use Zeedhi\Framework\DTO\Response\Message;

class ResponseTest extends \PHPUnit\Framework\TestCase {

    protected $regions = array(
        array(
            'REGION_ID' => 4,
            'REGION_NAME' => 'Africa'
        )
    );

    protected $countries = array(
        array(
            'COUNTRY_ID' => 'CH',
            'COUNTRY_NAME' => 'Switzerland'
        )
    );

    public function testResponseWithDataSets()
    {
        $response = new Response();
        $regions = new DataSet('REGIONS', $this->regions);
        $response->addDataSet($regions);
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DataSource\DataSet', $response->getDataSets(), 'It is expected that the response contains an instance of DataSet');
        $this->assertCount(1, $response->getDataSets(), 'Return must contain only one dataSet');
    }

    public function testResponseWithMultiplesDataSets()
    {
        $response = new Response();
        $dataSets = array(new DataSet('REGIONS', $this->regions), new DataSet('COUNTRIES', $this->countries));
        $response->setDataSets($dataSets);
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DataSource\DataSet', $response->getDataSets(), 'It is expected that the response contains an instance of DataSet');
        $this->assertCount(2, $response->getDataSets(), 'Return must contain only two dataSets');
    }

    public function testResponseStatus()
    {
        $response = new Response();
        $this->assertEquals(Response::STATUS_SUCCESS, $response->getStatus(), 'It is expected default status on response "SUCCESS"');
        $response->setStatus(Response::STATUS_ERROR);
        $this->assertEquals(Response::STATUS_ERROR, $response->getStatus(), 'It is expected status in response "ERROR"');
    }

    public function testResponseWithMessage()
    {
        $response = new Response();
        $message = new Message('Fork me on GitHub');
        $response->addMessage($message);
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DTO\Response\Message', $response->getMessages(), 'It is expected that the response contains an instance of Message');
        $this->assertCount(1, $response->getMessages(), 'Return must contain only one message');
    }

    public function testResponseWithMethods()
    {
        $response = new Response();
        $method = new Method('openWindow', array('login'));
        $response->addMethod($method);
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DTO\Response\Method', $response->getMethods(), 'It is expected that the response contains an instance of Method');
        $this->assertCount(1, $response->getMethods(), 'Return must contain only one method');
    }

    public function testResponseWithError()
    {
        $response = new Response();
        $error = new Error('Bad Gateway', 502);
        $response->setError($error);
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Error', $response->getError(), 'It is expected that the response contains an instance of Error');
    }

    public function testResponseWithErrorCode(){
        $response = new Response();
        $error = new Error('Bad Gateway', 502);
        $response->setError($error);

        $expectedErrorCode = $error->getErrorCode();
        $currentErrorCode = $response->getError()->getErrorCode();
        $this->assertEquals($expectedErrorCode, $currentErrorCode);
    }

    public function testResponseWithNotification() {
        $notification = new Response\Notification("expected message");
        $response = new Response();
        $response->addNotification($notification);
        $currentNotification = current($response->getNotifications());
        $this->assertEquals($notification, $currentNotification);
    }

    public function testSetAndGetFileToDownload(){
        $file = new File('foo');
        $response = new Response();
        $response->setFileToDownload($file);
        $this->assertEquals($response->getFileToDownload(), $file);
    }

    public function testIsFileDownloadResponse(){
        $response = new Response();
        $file = new File('foo');
        $response->setFileToDownload($file);
        $this->assertEquals($response->isFileDownloadResponse(), true);
    }
}

