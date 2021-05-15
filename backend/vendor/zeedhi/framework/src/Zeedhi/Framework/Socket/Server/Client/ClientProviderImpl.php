<?php
namespace Zeedhi\Framework\Socket\Server\Client;

/**
 * Class ClientProviderImpl
 *
 * @package Zeedhi\Framework\Socket\Client
 */
class ClientProviderImpl implements ClientProviderInterface {
	/** @var ClientImpl */
	protected $clients = array();

	/**
	 * {@inheritDoc}
	 */
	public function findByAccessToken($accessToken = null) {
		if ($accessToken === null) {
			$client = new ClientImpl();
			$this->clients[$client->getAccessToken()] = $client;
		} else {
			$client = isset($this->clients[$accessToken]) ? $this->clients[$accessToken] : null;
		}

		return $client;
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateClient(ClientInterface $client) {
		if (isset($this->clients[$client->getAccessToken()])) {
			$this->clients[$client->getAccessToken()] = $client;
		}
	}
}