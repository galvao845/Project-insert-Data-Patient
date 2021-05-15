<?php
namespace Zeedhi\Framework\Util;

/**
 * Class Exception
 *
 * Thrown when there is a error that could not be explained.
 *
 * @package Zeedhi\Framework\util
 */
class Exception extends \Exception {

	/**
	 * @return static
	 */
	public static function internalError() {
        // The class and the function are generic for security purposes, return a error on
        // the encryptation would make no sense.
		return new static("An error occurred.");
	}
} 