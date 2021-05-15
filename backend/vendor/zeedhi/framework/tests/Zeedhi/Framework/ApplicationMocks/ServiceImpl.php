<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

class ServiceImpl implements \Zeedhi\Framework\Security\OAuth\Service {
	private $clientId;
	private $secretId;
	private $name;

	public function __construct($name, $clientId = null, $clientSecret = null) {
		$this->clientId = empty($clientId) ? $name . "_ID" : $clientId;
		$this->secretId = empty($clientSecret) ? $name . "_SECRET" : $clientSecret;
		$this->name = $name;
	}

	public function getClientId() {
		return $this->clientId;
	}

	public function getClientSecret() {
		return $this->secretId;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
}