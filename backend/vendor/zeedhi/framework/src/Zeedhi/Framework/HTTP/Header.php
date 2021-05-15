<?php
namespace Zeedhi\Framework\HTTP;

/**
 * Class Header
 *
 * This class is a container for HTTP headers.
 *
 * @package Zeedhi\Framework\HTTP
 */
class Header {

	protected $headers = array();
	protected $headerNames = array();

	/**
	 * Constructor.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function __construct(array $headers = array()) {
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}

	/**
	 * Returns the headers.
	 *
	 * @return array An array of headers
	 */
	public function all() {
		return $this->headers;
	}

	/**
	 * Returns the parameter keys.
	 *
	 * @return array An array of parameter keys
	 */
	public function keys() {
		return array_keys($this->headers);
	}

	/**
	 * Replaces the current HTTP headers by a new set.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function replace(array $headers = array()) {
		$this->headerNames = array();
		$this->headers = array();
		$this->add($headers);
	}

	/**
	 * Adds new headers the current HTTP headers set.
	 *
	 * @param array $headers An array of HTTP headers
	 */
	public function add(array $headers) {
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}

	/**
	 * Returns a header value by name.
	 *
	 * @param string $key     The header name
	 * @param mixed  $default The default value
	 * @param bool   $first   Whether to return the first value or all header values
	 *
	 * @return string|array The first header value if $first is true, an array of values otherwise
	 */
	public function get($key, $default = null, $first = true) {
		$key = strtr(strtolower($key), '_', '-');
		if (!array_key_exists($key, $this->headers)) {
			if (null === $default) {
				return $first ? null : array();
			}
			return $first ? $default : array($default);
		}
		if ($first) {
			return count($this->headers[$key]) ? $this->headers[$key][0] : $default;
		}
		return $this->headers[$key];
	}

	/**
	 * Sets a header by name.
	 *
	 * @param string       $key     The key
	 * @param string|array $values  The value or an array of values
	 * @param bool         $replace Whether to replace the actual value or not (true by default)
	 */
	public function set($key, $values, $replace = true) {
		$uniqueKey = strtr(strtolower($key), '_', '-');
		$values = array_values((array)$values);
		if (true === $replace || !isset($this->headers[$uniqueKey])) {
			$this->headers[$uniqueKey] = $values;
		} else {
			$this->headers[$uniqueKey] = array_merge($this->headers[$uniqueKey], $values);
		}
		$this->headerNames[$uniqueKey] = $key;
	}

	/**
	 * Returns true if the HTTP header is defined.
	 *
	 * @param string $key The HTTP header
	 *
	 * @return bool true if the parameter exists, false otherwise
	 */
	public function has($key) {
		return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers);
	}

	/**
	 * Returns true if the given HTTP header contains the given value.
	 *
	 * @param string $key   The HTTP header name
	 * @param string $value The HTTP value
	 *
	 * @return bool true if the value is contained in the header, false otherwise
	 */
	public function contains($key, $value) {
		return in_array($value, $this->get($key, null, false));
	}

	/**
	 * Removes a header.
	 *
	 * @param string $key The HTTP header name
	 */
	public function remove($key) {
		$uniqueKey = strtr(strtolower($key), '_', '-');
		unset($this->headers[$uniqueKey], $this->headerNames[$uniqueKey]);
	}

	/**
	 * Returns the HTTP header value converted to a date.
	 *
	 * @param string    $key     The parameter key
	 * @param \DateTime $default The default value
	 *
	 * @return null|\DateTime The parsed DateTime or the default value if the header does not exist
	 *
	 * @throws \RuntimeException When the HTTP header is not parseable
	 */
	public function getDate($key, \DateTime $default = null) {
		if (null === $value = $this->get($key)) {
			return $default;
		}
		if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
			throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
		}
		return $date;
	}

	/**
	 * Returns the headers, with original capitalizations.
	 *
	 * @return array An array of headers
	 */
	public function allPreserveCase() {
		$headers = array();
		if(sizeof($this->headers) > 0) {
			$headers = array_combine($this->headerNames, $this->headers);
		}
		return $headers;
	}
}