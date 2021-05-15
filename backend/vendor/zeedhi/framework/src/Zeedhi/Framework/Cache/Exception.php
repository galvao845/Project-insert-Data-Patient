<?php
namespace Zeedhi\Framework\Cache;

/**
 * Class Exception
 *
 * Thrown when a key could not be found while evaluating
 *
 * @package Zeedhi\Framework\Cache
 */
class Exception extends \Exception {

	/**
	 * @param $key
	 *
	 * @return static
	 */
	public static function valueNotFound($key) {
		return new static("Value for key {$key} not found in cache.");
	}
} 