<?php
namespace Zeedhi\Framework\HTTP;

/**
 * Class Server
 *
 * This class is a container for HTTP headers from the $_SERVER variable.
 *
 * @package Zeedhi\Framework\HTTP
 */
class Server extends Parameter {

	/**
	 * Gets the HTTP headers.
	 *
	 * @return array
	 */
	public function getHeaders() {
		$headers = array();
		$contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
		foreach ($this->parameters as $key => $value) {
			if (0 === strpos($key, 'HTTP_')) {
				$headers[substr($key, 5)] = $value;
			} // CONTENT_* are not prefixed with HTTP_
			elseif (isset($contentHeaders[$key])) {
				$headers[$key] = $value;
			}
		}
		return $headers;
	}

}