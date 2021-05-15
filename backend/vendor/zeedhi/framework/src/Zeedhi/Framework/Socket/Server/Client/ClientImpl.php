<?php
namespace Zeedhi\Framework\Socket\Server\Client;

/**
 * Class ClientImpl
 *
 * @package Zeedhi\Framework\Socket\Server\Client
 */
class ClientImpl implements ClientInterface {

	const IS_AUTHENTICATED_ANONYMOUSLY = 'IS_AUTHENTICATED_ANONYMOUSLY';
	const IS_AUTHENTICATED = 'IS_AUTHENTICATED';
	/** @var string */
	protected $accessToken;
	/** @var string */
	protected $isAuthenticated;

	/**
	 * Constructor
	 *
	 * @param null $accessToken
	 */
	public function __construct($accessToken = null) {
		if ($accessToken === null) {
			$this->accessToken = hash('sha256', uniqid(microtime(true)));
			$this->isAuthenticated = false;
		} else {
			$this->accessToken = $accessToken;
			$this->isAuthenticated = true;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAccessToken($accessToken) {
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return $this->isAuthenticated ? array(self::IS_AUTHENTICATED) : array(self::IS_AUTHENTICATED_ANONYMOUSLY);
	}
}