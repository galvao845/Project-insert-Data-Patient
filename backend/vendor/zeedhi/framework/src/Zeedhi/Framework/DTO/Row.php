<?php
namespace Zeedhi\Framework\DTO;

/**
 * Class Row
 *
 * This class allows objects to work as arrays.
 *
 * @package Zeedhi\Framework\DTO
 */
class Row extends \ArrayObject {

	/**
	 * Returns the value at the specified property name
	 *
	 * @param mixed $propertyName The property name with the value.
	 *
	 * @return mixed The value at the specified property name or false.
	 */
	public function get($propertyName) {
		return $this->offsetGet($propertyName);
	}

	/**
	 * Sets the value at the specified property name to newval
	 *
	 * @param mixed $propertyName The property name being set.
	 * @param mixed $newValue     The new value for the <propertyName>
	 *
	 * @return void
	 */
	public function set($propertyName, $newValue) {
		$this->offsetSet($propertyName, $newValue);
	}

	/**
	 * Returns whether the requested property name exists
	 *
	 * @param mixed $propertyName The property name being checked.
	 *
	 * @return bool True if the requested property name exists, otherwise false
	 */
	public function has($propertyName) {
		return $this->offsetExists($propertyName);
	}


	/**
	 * Unsets the value at the specified property name
	 *
	 * @param mixed $propertyName The property name being unset.
	 *
	 * @return void
	 */
	public function remove($propertyName) {
		$this->offsetUnset($propertyName);
	}


}