<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\Security\OAuth\Exception;

class ServiceProviderImpl implements \Zeedhi\Framework\Security\OAuth\ServiceProvider {

	private $services = array();

	public function __construct() {
		for ($i = 0; $i < 10; $i++) {
			$this->services[] = new ServiceImpl("Zeedhi Application {$i}");
		}
	}

	public function findByClientAndSecretId($clientId, $clientSecret) {
		/** @var ServiceImpl $service */
		foreach ($this->services as $service) {
			if ($service->getClientId() === $clientId && $service->getClientSecret($clientSecret)) {
				return $service;
			}
		}

        throw Exception::serviceNotFound($clientId);
	}

}