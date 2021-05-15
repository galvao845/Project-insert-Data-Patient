<?php
namespace Zeedhi\Framework\HTTP;

/**
 * Class Request
 *
 * Request represents an HTTP request.
 *
 * @package Zeedhi\Framework\HTTP
 */
class Request {
	/**
	 * Query string parameters ($_GET)
	 *
	 * @var Parameter
	 */
	protected $query;
	/**
	 * Request body parameters ($_POST)
	 *
	 * @var Parameter
	 */
	protected $request;
	/**
	 * Server and execution environment parameters ($_SERVER)
	 *
	 * @var Parameter
	 */
	protected $server;

	/**
	 * Headers (taken from the $_SERVER).
	 *
	 * @var Header
	 */
	protected $headers;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var string
	 */
	protected $requestUri;

	public function __construct(array $query = array(), array $request = array(), array $server = array(), $content = null) {
		$this->query = new Parameter($query);
		$this->request = new Parameter($request);
		$this->server = new Server($server);
		$this->headers = new Header($this->server->getHeaders());
		$this->content = $content;
	}


	/**
	 * Gets a "parameter" value.
	 *
	 * @param string $key     the key
	 * @param mixed  $default the default value
	 * @param bool   $deep    is parameter deep in multidimensional array
	 *
	 * @return mixed
	 */
	public function get($key, $default = null, $deep = false) {
		return $this->query->get($key, $this->request->get($key, $default, $deep), $deep);
	}

	/**
	 * Gets the request "intended" method.
	 *
	 * @return string
	 */
	public function getMethod() {
		if (null === $this->method) {
			$this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
		}
		return $this->method;
	}

	/**
	 * Returns the requested URI.
	 *
	 * @return string The raw URI (i.e. not urldecoded)
	 */
	public function getRequestUri() {
		if (null === $this->requestUri) {
			$this->requestUri = $this->server->get('REQUEST_URI');
			$this->requestUri ? parse_url($this->requestUri, PHP_URL_PATH) : parse_url('/', PHP_URL_PATH);
		}
		return $this->requestUri;
	}

	/**
	 * Returns the request Type.
	 *
	 * @return string
	 */
	public function getRequestType() {
		return $this->get('requestType');
	}

	/**
	 * Returns the User-Id of the request header.
	 *
	 * @return string
	 */
	public function getUserId() {
		return $this->headers->get('User-Id');
	}

	/**
	 * Creates a new request with values from PHP's super globals.
	 *
	 * @return Request A new request
	 */
	public static function initFromGlobals() {
		$request = new static($_GET, $_POST, $_SERVER);

		if (strpos($request->getContentType(), 'application/json') !== false) {
			$data = json_decode($request->getContent(), true);
			$request->request = new Parameter((array)$data);
		}

		return $request;
	}

	/**
	 * Returns the request body content.
	 *
	 * @param bool $asResource If true, a resource will be returned
	 *
	 * @return string|resource The request body content or a resource to read the body stream.
	 */
	public function getContent($asResource = false) {
		if (!$this->content) {
			if ($asResource) {
				$this->content = fopen('php://input', 'rb');
			} else {
				$this->content = file_get_contents('php://input');
			}
		}
		return $this->content;
	}

	/**
	 * Gets the format associated with the request.
	 *
	 * @return string|null The format (null if no content type is present)
	 */
	public function getContentType() {
		return $this->headers->get('Content-Type');
	}

	/**
	 * Returns the headers taken from $_SERVER
	 *
	 * @return Header
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Checks whether the request is secure or not.
	 *
	 * @return bool
	 */
	public function isSecure() {
		$https = $this->server->get('HTTPS');
		return !empty($https) && 'off' !== strtolower($https);
	}

	/**
	 * Gets the request's scheme.
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->isSecure() ? 'https' : 'http';
	}


	/**
	 * Returns the port on which the request is made.
	 *
	 * @return string
	 */
	public function getPort() {
		if ($host = $this->headers->get('HOST')) {
			if ($host[0] === '[') {
				$pos = strpos($host, ':', strrpos($host, ']'));
			} else {
				$pos = strrpos($host, ':');
			}
			if (false !== $pos) {
				return intval(substr($host, $pos + 1));
			}
			return 'https' === $this->getScheme() ? 443 : 80;
		}
		return $this->server->get('SERVER_PORT');
	}

	/**
	 * Returns the host name.
	 *
	 * @return string
	 *
	 * @throws \UnexpectedValueException when the host name is invalid
	 */
	public function getHost() {
		$host = $this->headers->get('HOST');
		if (!$host) {
			$host = $this->server->get('SERVER_NAME');
			if (!$host) {
				$host = $this->server->get('SERVER_ADDR', '');
			}
		}
		$host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
		if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
			throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
		}
		return $host;
	}

	/**
	 * Returns the HTTP host being requested.
	 *
	 * The port name will be appended to the host if it's non-standard.
	 *
	 * @return string
	 */
	public function getHttpHost() {
		$scheme = $this->getScheme();
		$port = $this->getPort();
		if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
			return $this->getHost();
		}
		return $this->getHost() . ':' . $port;
	}

	/**
	 * Gets the scheme and HTTP host.
	 *
	 * If the URL was called with basic authentication, the user
	 * and the password are not added to the generated string.
	 *
	 * @return string The scheme and HTTP host
	 */
	public function getSchemeAndHttpHost() {
		return $this->getScheme() . '://' . $this->getHttpHost();
	}


	/**
	 * Creates a Request based on a given URI and configuration.
	 *
	 * The information contained in the URI always take precedence
	 * over the other information (server and parameters).
	 *
	 * @param string     $uri        The URI
	 * @param string     $method     The HTTP method
	 * @param array      $parameters The query (GET) or request (POST) parameters
	 * @param array      $server     The server parameters ($_SERVER)
	 * @param mixed|null $content    The body of the raw request
	 *
	 *
	 * @return Request A Request instance
	 */
	public static function create($uri, $method = 'GET', $parameters = array(), $server = array(), $content = null) {
		$server = array_replace(array(
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
			'HTTP_HOST' => 'localhost',
			'HTTP_USER_AGENT' => 'Zeedhi/2.X',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
			'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			'REMOTE_ADDR' => '127.0.0.1',
			'SCRIPT_NAME' => '',
			'SCRIPT_FILENAME' => '',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_TIME' => time(),
		), $server);

		$server['PATH_INFO'] = '';
		$server['REQUEST_METHOD'] = strtoupper($method);

		$components = parse_url($uri);
		if (isset($components['host'])) {
			$server['SERVER_NAME'] = $components['host'];
			$server['HTTP_HOST'] = $components['host'];
		}

		if (isset($components['scheme'])) {
			if ('https' === $components['scheme']) {
				$server['HTTPS'] = 'on';
				$server['SERVER_PORT'] = 443;
			} else {
				unset($server['HTTPS']);
				$server['SERVER_PORT'] = 80;
			}
		}

		if (isset($components['port'])) {
			$server['SERVER_PORT'] = $components['port'];
			$server['HTTP_HOST'] = $server['HTTP_HOST'] . ':' . $components['port'];
		}

		if (isset($components['user'])) {
			$server['PHP_AUTH_USER'] = $components['user'];
		}

		if (isset($components['pass'])) {
			$server['PHP_AUTH_PW'] = $components['pass'];
		}

		if (!isset($components['path'])) {
			$components['path'] = '/';
		}

		switch (strtoupper($method)) {
			case 'POST':
			case 'PUT':
			case 'DELETE':
				if (!isset($server['CONTENT_TYPE'])) {
					$server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
				}
			// no break
			case 'PATCH':
				$request = $parameters;
				$query = array();
				break;
			default:
				$request = array();
				$query = $parameters;
				break;
		}

		$queryString = '';
		if (isset($components['query'])) {
			parse_str(html_entity_decode($components['query']), $qs);
			if ($query) {
				$query = array_replace($qs, $query);
				$queryString = http_build_query($query, '', '&');
			} else {
				$query = $qs;
				$queryString = $components['query'];
			}
		} elseif ($query) {
			$queryString = http_build_query($query, '', '&');
		}

		$server['REQUEST_URI'] = $components['path'] . ('' !== $queryString ? '?' . $queryString : '');
		$server['QUERY_STRING'] = $queryString;
		return new static($query, $request, $server, $content);
	}

	/**
	 * Gets the query parameters
	 *
	 * @return Parameter
	 */
	public function getQueryParameters() {
		return $this->query;
	}

	/**
	 * Gets the request parameters
	 *
	 * @return Parameter
	 */
	public function getRequestParameters() {
		return $this->request;
	}

}