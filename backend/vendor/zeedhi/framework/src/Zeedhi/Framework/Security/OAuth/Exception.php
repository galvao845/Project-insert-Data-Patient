<?php
namespace Zeedhi\Framework\Security\OAuth;

/**
 * Class Exception
 *
 * Should group all variety of expected messages used in this Exception Class.
 *
 * @package Zeedhi\Framework\Security\OAuth
 */

class Exception extends \Exception {

	/**
	 * @param $clientId
	 *
	 * @return static
	 */
	public static function serviceNotFound($clientId) {
		return new static("The service with clientID {$clientId} was not found.");
	}

	/**
	 * @return static
	 */
	public static function invalidFormatToken() {
		return new static("An error was encountered while creating token.");
	}

	/**
	 * @return static
	 */
	public static function invalidToken() {
		return new static("The token provided is not valid. Try again or request another token.");
	}
} 