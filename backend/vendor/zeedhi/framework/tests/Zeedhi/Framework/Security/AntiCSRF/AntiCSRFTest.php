<?php
namespace test\Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;
use Zeedhi\Framework\Security\AntiCSRF\AntiCSRF;

class AntiCSRFTest extends \PHPUnit\Framework\TestCase {

    /** @var AntiCSRF|\PHPUnit_Framework_MockObject_MockObject */
    protected $antiCSRF;
    /** @var Cache|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    public function setUp(){
        $this->cache = $this->createMock(Cache::class);
        $this->antiCSRF = new AntiCSRF($this->cache, 30*60);
    }

    public function testConstantValues(){
        $this->assertEquals(AntiCSRF::TOKEN_NAME, 'zeedhi-token');
        $this->assertEquals(AntiCSRF::COOKIE_NAME, 'zeedhi-cookie');
        $this->assertEquals(AntiCSRF::DATASET_NAME, 'zeedhi-dataset-token');
        $this->assertEquals(AntiCSRF::PARAMETER_NAME, 'zeedhi-request-parameter');
    }

    public function testGenerateTokenAndCookie(){
        $currentTime = time();
        $cachedData = null;
        $cachedKey = null;

        $this->cache->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($key) use (&$cachedKey) {
                    $cachedKey = $key;
                    return true;
                }),
                $this->callback(function($data) use (&$cachedData) {
                    $cachedData = $data;
                    return true;
                }),
                60*30
            )
            ->willReturn(true);

        $generatedTokenAndCookie = $this->antiCSRF->generateTokenAndCookie();
        $this->assertEquals($cachedKey, 'zeedhi_secutiry_anticsrf_'.$cachedData['cookie']);
        $this->assertEquals($cachedData, $generatedTokenAndCookie);
        $this->assertRegExp('/[a-f0-9]{40}/', $cachedData['token']);
        $this->assertRegExp('/[a-f0-9]{40}/', $cachedData['cookie']);
        $this->assertGreaterThanOrEqual($currentTime, $cachedData['created']);
    }

    public function testIsTokenValid(){
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('zeedhi_secutiry_anticsrf_cookieValue')
            ->willReturn(array('token'=> 'tokenValue'));
        $this->assertTrue($this->antiCSRF->isTokenValid('cookieValue', 'tokenValue'));
    }

    public function testIsTokenValidWrongCookieValue(){
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('zeedhi_secutiry_anticsrf_cookieValue')
            ->willReturn(array('token'=> 'tokenValue'));
        $this->assertFalse($this->antiCSRF->isTokenValid('cookieValue', 'wrongTokenValue'));
    }

    public function testIsTokenValidCacheReturnNull(){
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('zeedhi_secutiry_anticsrf_cookieValue')
            ->willReturn(null);
        $this->assertFalse($this->antiCSRF->isTokenValid('cookieValue', 'tokenValue'));
    }

    public function testGetTokenAndCookie() {
        $cachedObject = array(
            'token' => 'tokenValue',
            'cookie' => 'cookieValue',
            'created' => time()
        );
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('zeedhi_secutiry_anticsrf_cookieValue')
            ->willReturn($cachedObject);

        $this->assertEquals($cachedObject, $this->antiCSRF->getTokenAndCookie('cookieValue'));
    }

    public function testGetTokenAndCookieNotFound() {
        $expectedException = Exception::valueNotFound('zeedhi_secutiry_anticsrf_cookieValue');

        $this->cache->expects($this->once())
            ->method('fetch')
            ->with('zeedhi_secutiry_anticsrf_cookieValue')
            ->willThrowException($expectedException);;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedException->getMessage());
        $this->expectExceptionCode($expectedException->getCode());

        $this->antiCSRF->getTokenAndCookie('cookieValue');
    }
}
