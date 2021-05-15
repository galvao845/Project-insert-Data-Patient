<?php
namespace tests\Zeedhi\Framework\Security\OAuth;

use Exception;

use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\Security\OAuth\Exception as OAuthException;
use Zeedhi\Framework\Security\OAuth\OAuthImpl;
use tests\Zeedhi\Framework\ApplicationMocks\ServiceProviderImpl;

class OAuthImplTest extends \PHPUnit\Framework\TestCase {

	/** @var OAuthImpl $oauth */
	protected $oauth;
	protected $clientId;
	protected $clientSecret;

	public function setUp() {
		$this->oauth = new OAuthImpl(new ServiceProviderImpl(), new ArrayImpl());
		$this->clientId = "Zeedhi Application 1_ID";
		$this->clientSecret = "Zeedhi Application 1_SECRET";
	}

	private function getToken($clientId, $clientSecret) {
		return $this->oauth->grantAccessToken($clientId, $clientSecret);
	}

	public function testGrantAccessToken() {
		$token = $this->getToken($this->clientId, $this->clientSecret);
		$this->assertNotNull($token);
	}

	public function testCheckAccess() {
		$token = $this->getToken($this->clientId, $this->clientSecret);
		$service = $this->oauth->checkAccess($token, $this->clientSecret);
		$this->assertNotEmpty($service);
		$this->assertEquals($this->clientId, $service->getClientId());
		$this->assertEquals($this->clientSecret, $service->getClientSecret());
	}

	public function testGrantAccessTokenWithServiceNotFound() {
		$this->expectException(Exception::class);
		$token = $this->getToken("a45s45ds", "assasadsadsd4545sa46asd");
	}

	public function testCheckInvalidAccessToken(){
		$this->expectException(Exception::class);
		$token = $this->getToken($this->clientId, $this->clientSecret);
		$token = substr($token, 0, 10);
		$service = $this->oauth->checkAccess($token, $this->clientSecret);
	}

    public function testInvalidClientIdOnGrantAccessToken() {
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage("The service with clientID foo was not found.");
        $this->oauth->grantAccessToken("foo", "bar");
	}

}
