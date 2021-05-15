<?php
namespace Zeedhi\Framework\Socket\Server;

/**
 * Class Payload
 *
 * @package Zeedhi\Framework\Socket\Server
 */
class Payload {
	/**
	 * @var string
	 */
	protected $event;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Validates the given json data format. Returns true when the given json format is valid, false otherwise.
	 *
	 * @param array $jsonData
	 *
	 * @return boolean
	 */
	public static function isValid(array $jsonData) {
		if (!isset($jsonData['event'])) {
			return false;
		}

		if (!isset($jsonData['data'])) {
			return false;
		}

		return true;
	}

	/**
	 * Decodes the given string input and returns an array of data for this payload.
	 * Throws InvalidArgumentException on decoding errors.
	 *
	 * @param string $msg
	 *
	 * @return array
	 */
	public static function decode($msg) {
		$data = json_decode($msg, true);
		return $data;
	}

	/**
	 * Returns a json encoded with the data for this payload.
	 *
	 * @return string
	 */
	public function encode() {
		return json_encode(
			array(
				'event' => $this->getEvent(),
				'data' => $this->getData()
			)
		);
	}

	/**
	 * Create payload from json
	 *
	 * @param string $json
	 *
	 * @return Payload
	 */
	public static function createFromJson($json) {
		return static::createFromArray(static::decode($json));
	}

	/**
	 * Create payload from array
	 *
	 * @param array $data
	 *
	 * @return Payload
	 */
	public static function createFromArray(array $data) {
		if (static::isValid($data)) {

			return new static($data['event'], $data['data']);
		}

		return null;
	}

	/**
	 * Constructor
	 *
	 * @param string $event The name of the event in payload
	 * @param mixed  $data  The data contained in payload
	 */
	public function __construct($event, $data) {
		$this->event = $event;
		$this->data = $data;
	}

	/**
	 * Returns the data for this payload.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Returns the name of the event for this payload
	 *
	 * @return string
	 */
	public function getEvent() {
		return $this->event;
	}
}