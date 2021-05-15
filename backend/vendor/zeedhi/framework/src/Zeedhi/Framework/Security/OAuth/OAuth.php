<?php
namespace Zeedhi\Framework\Security\OAuth;

/**
 * Interface OAuth
 *
 * Interface to provider a implementation of custom OAuth
 *
 * @package Zeedhi\Framework\Security\OAuth
 *
 */
interface OAuth {

	/**
	 * Grant an access token of a valid service.
	 *
	 * @param string $clientId     The public id of the service.
	 * @param string $clientSecret The secret id of the service.
	 *
	 * @throws Exception if the service was not found
	 *
	 * @return string The access token
	 */
	public function grantAccessToken($clientId, $clientSecret, $options = array());

	/**
	 * Validate an access token and return the session of one service
	 *
	 * @param  string $token        The access token of a service
	 * @param  string $clientSecret The secret id of the service
	 *
	 * @throws Exception If the access token is invalid
	 *
	 * @return Service The object of a service.
	 */
	public function checkAccess($token, $clientSecret, $options = array());

}