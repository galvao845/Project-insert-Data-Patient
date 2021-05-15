<?php
namespace Zeedhi\Framework\Security\Support;

/**
 * Class CorsOptions
 *
 * This class contains
 *
 * @package Zeedhi\Framework\Security\Support
 */
class CorsOptions {

	protected $allowedOrigins;
	protected $allowedMethods;
	protected $allowedHeaders;
	protected $maxAge;
	protected $exposedHeaders;
	protected $supportCredentials;

	/**
	 * Constructor..
	 *
	 * @param array|string $allowedOrigins     The list of allowed origins domain with protocol.
	 * @param array|string $allowedHeaders     The list of allowed headers
	 * @param array|string $allowedMethods     The list of allowed HTTP methods
	 * @param int          $maxAge             The max age of the authorize request in seconds.
	 * @param array|bool   $exposedHeaders     The list of exposed headers.
	 * @param bool         $supportCredentials Allow CORS request with credential
	 */
	public function __construct($allowedOrigins = array(), $allowedHeaders = array(), $allowedMethods = array(), $maxAge = 0, $exposedHeaders = false, $supportCredentials = false) {
		$this->allowedOrigins = $allowedOrigins;
		$this->allowedHeaders = $allowedHeaders;
		$this->allowedMethods = $allowedMethods;
		$this->maxAge = $maxAge;
		$this->exposedHeaders = $exposedHeaders;
		$this->supportCredentials = $supportCredentials;
	}

	/**
	 * Returns the list of allowed origins domain with protocol
	 *
	 * @return mixed
	 */
	public function getAllowedOrigins() {
		return $this->allowedOrigins;
	}

	/**
	 * Returns the list of allowed HTTP methods
	 *
	 * @return mixed
	 */
	public function getAllowedMethods() {
		return $this->allowedMethods;
	}

	/**
	 * Returns the list of allowed headers
	 *
	 * @return mixed
	 */
	public function getAllowedHeaders() {
		return $this->allowedMethods;
	}

	/**
	 * Returns the max age of the authorized request
	 *
	 * @return mixed
	 */
	public function getMaxAge() {
		return $this->maxAge;
	}

	/**
	 * Returns the list of exposed headers
	 *
	 * @return mixed
	 */
	public function getExposedHeaders() {
		return $this->exposedHeaders;
	}

	/**
	 * Returns if allow cors request with credentials
	 *
	 * @return bool
	 */
	public function isSupportCredentials() {
		return $this->supportCredentials;
	}

}