<?php
namespace Zeedhi\Framework\Socket\Server\Client;

/**
 * Interface ClientProviderInterface
 *
 * @package Zeedhi\Framework\Socket\Server\Client
 */
interface ClientProviderInterface {
	/**
	 * Returns a client found by the access token.
	 *
	 * @param string $accessToken
	 *
	 * @return ClientInterface
	 */
	public function findByAccessToken($accessToken = null);

	/**
	 * Updates the given client in the underlying data layer.
	 *
	 * @param ClientInterface $client
	 *
	 * @return void
	 */
	public function updateClient(ClientInterface $client);
}