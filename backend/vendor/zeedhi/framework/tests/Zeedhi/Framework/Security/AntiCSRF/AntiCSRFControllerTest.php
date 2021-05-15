<?php
namespace test\Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\HTTP\Kernel;
use Zeedhi\Framework\HTTP\Request as HTTPRequest;
use Zeedhi\Framework\Security\AntiCSRF\AntiCSRF;
use Zeedhi\Framework\Security\AntiCSRF\AntiCSRFController;

class AntiCSRFControllerTest extends \PHPUnit\Framework\TestCase {

    /** @var AntiCSRFController */
    private $antiCSRFController;
    /** @var AntiCSRF|\PHPUnit_Framework_MockObject_MockObject */
    private $antiCSRFMock;
    /** @var Kernel|\PHPUnit_Framework_MockObject_MockObject */
    private $kernelMock;

    public function setUp(){
        $this->kernelMock = $this->getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getHTTPRequest'))
            ->getMock();

        $this->antiCSRFMock = $this->getMockBuilder(AntiCSRF::class)
            ->disableOriginalConstructor()
            ->setMethods(array('generateTokenAndCookie', 'getTokenAndCookie'))
            ->getMock();
        $this->antiCSRFController = new AntiCSRFController($this->kernelMock, $this->antiCSRFMock);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateSessionCookieAndToken(){
        $query = array();
        $request = array();
        $server = array();

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $tokenAndCookie = array(
            'token' => 'tokenValue',
            'cookie' => 'cookieValue'
        );
        $this->antiCSRFMock->expects($this->once())
            ->method('generateTokenAndCookie')
            ->willReturn($tokenAndCookie);

        $request = new DTO\Request('GET', '/route', 'user-1');
        $response = new DTO\Response();
        $expectedDataSet = new DataSet(AntiCSRF::DATASET_NAME, array('token'=> 'tokenValue'));
        $this->antiCSRFController->generateSessionCookieAndToken($request, $response);
        $this->assertEquals($expectedDataSet, $response->getDataSets()[0]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateSessionCookieAndTokenInATabWithPreviousCookie(){
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => AntiCSRF::COOKIE_NAME.'=cookieValue'
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $tokenAndCookie = array(
            'token' => 'tokenValue',
            'cookie' => 'cookieValue',
            'created' => time()
        );

        $this->antiCSRFMock->expects($this->once())
            ->method('getTokenAndCookie')
            ->with('cookieValue')
            ->willReturn($tokenAndCookie);


        $request = new DTO\Request('GET', '/route', 'user-1');
        $response = new DTO\Response();
        $expectedDataSet = new DataSet(AntiCSRF::DATASET_NAME, array('token'=> 'tokenValue'));
        $this->antiCSRFController->generateSessionCookieAndToken($request, $response);
        $this->assertEquals($expectedDataSet, $response->getDataSets()[0]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCookieHeadersWithoutAntiCSRFCookie() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => 'PHPSESSID=sessionCookieValue'
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $tokenAndCookie = array(
            'token' => 'tokenValue',
            'cookie' => 'cookieValue',
            'created' => time()
        );

        $this->antiCSRFMock->expects($this->once())
            ->method('generateTokenAndCookie')
            ->willReturn($tokenAndCookie);


        $request = new DTO\Request('GET', '/route', 'user-1');
        $response = new DTO\Response();
        $expectedDataSet = new DataSet(AntiCSRF::DATASET_NAME, array('token'=> 'tokenValue'));
        $this->antiCSRFController->generateSessionCookieAndToken($request, $response);
        $this->assertEquals($expectedDataSet, $response->getDataSets()[0]);

    }
}