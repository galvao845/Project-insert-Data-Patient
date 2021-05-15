<?php
namespace test\Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\HTTP\Kernel;
use Zeedhi\Framework\Security\AntiCSRF\AntiCSRF;
use Zeedhi\Framework\Security\AntiCSRF\Exception;
use \Zeedhi\Framework\HTTP\Request as HTTPRequest;
use Zeedhi\Framework\Security\AntiCSRF\PreDispatchListener;

class PreDispatchListenerTest extends \PHPUnit\Framework\TestCase {

    /** @var AntiCSRF|\PHPUnit_Framework_MockObject_MockObject */
    private $antiCSRFMock;
    /** @var Kernel|\PHPUnit_Framework_MockObject_MockObject */
    private $kernelMock;
    /** @var PreDispatchListener */
    private $preDispatchListener;

    public function setUp(){
        $this->kernelMock = $this->getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getHTTPRequest'))
            ->getMock();

        $this->antiCSRFMock = $this->getMockBuilder(AntiCSRF::class)
            ->disableOriginalConstructor()
            ->setMethods(array('isTokenValid'))
            ->getMock();

        $this->preDispatchListener = new PreDispatchListener(
            $this->kernelMock,
            $this->antiCSRFMock,
            '/routeToGenerate'
        );
    }

    public function testPreDispatchGenerateTokenRoute(){
        $query = array();
        $request = array();
        $server = array(
            'HTTP_HOST' => 'localhost',
            'HTTP_Origin' => 'http://localhost'
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->never())->method('isTokenValid');

        $dtoRequest = new Request('GET', '/routeToGenerate', 'user-1');
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testPreDispatchWithWrongOrigin() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_HOST' => 'localhost',
            'HTTP_Origin' => 'http://malicioussite.com'
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->never())->method('isTokenValid');

        $dtoRequest = new Request('GET', '/routeToGenerate', 'user-1');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid access.");
        $this->expectExceptionCode(Exception::INVALID_ACCESS);
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testPreDispatchWithValidToken() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => AntiCSRF::COOKIE_NAME.'=cookieValue',
            'HTTP_'.AntiCSRF::TOKEN_NAME => 'tokenValue',
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->once())
            ->method('isTokenValid')
            ->with('cookieValue', 'tokenValue')
            ->willReturn(true);

        $dtoRequest = new Request('GET', '/normalRoute', 'user-1');
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testPreDispatchWithInvalidToken() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => AntiCSRF::COOKIE_NAME.'=cookieValue',
            'HTTP_'.AntiCSRF::TOKEN_NAME => 'tokenValue',
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->once())
            ->method('isTokenValid')
            ->with('cookieValue', 'tokenValue')
            ->willReturn(false);

        $dtoRequest = new Request('GET', '/normalRoute', 'user-1');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid token/cookie.");
        $this->expectExceptionCode(Exception::INVALID_TOKEN);
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testPreDispatchWithoutCookie() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => '',
            'HTTP_'.AntiCSRF::TOKEN_NAME => 'tokenValue',
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->never())
            ->method('isTokenValid');

        $dtoRequest = new Request('GET', '/normalRoute', 'user-1');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cookie not found in request header.");
        $this->expectExceptionCode(Exception::COOKIE_NOT_FOUND_REQUEST);
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testPreDispatchWithoutToken() {
        $query = array();
        $request = array();
        $server = array(
            'HTTP_COOKIE' => AntiCSRF::COOKIE_NAME.'=cookieValue',
        );

        $this->kernelMock->expects($this->once())
            ->method('getHTTPRequest')
            ->willReturn(new HTTPRequest($query, $request, $server));

        $this->antiCSRFMock->expects($this->never())
            ->method('isTokenValid');

        $dtoRequest = new Request('GET', '/normalRoute', 'user-1');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Token not found in request header.");
        $this->expectExceptionCode(Exception::TOKEN_NOT_FOUND_REQUEST);
        $this->preDispatchListener->preDispatch($dtoRequest);
    }

    public function testFactoryWithPublicRoutesFile() {
        $routesFilePath = __DIR__."/publicRoutes.json";
        file_put_contents($routesFilePath, json_encode(array('/publicRoute')));
        $preDispatch = PreDispatchListener::factoryWithPublicRoutesFile(
            $this->kernelMock,
            $this->antiCSRFMock,
            '/routeToGenerate',
            $routesFilePath
        );

        unlink($routesFilePath);
    }
}