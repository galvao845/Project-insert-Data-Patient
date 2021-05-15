<?php
namespace Zeedhi\Framework\Security\Support;

use Zeedhi\Framework\HTTP\Request;
use Zeedhi\Framework\HTTP\Response;

/**
 * Class Cors
 *
 * This class offers a simple mechanism to handle CORS requests
 *
 * @package Zeedhi\Framework\Security\Support
 */
class Cors {

	/** @var CorsOptions */
	protected $corsOptions;

	/**
	 * Constructor
	 *
	 * @param CorsOptions $corsOptions The CORS options
	 */
	public function __construct(CorsOptions $corsOptions) {
		$this->corsOptions = $corsOptions;
	}

	/**
	 * Check if the HTTP request is a CORS request by checking if the Origin header is present and that the
	 * request URI is not the same as the one in the Origin
	 *
	 * @param  Request $request
	 *
	 * @return bool
	 */
	public function isCorsRequest(Request $request) {
		return $request->getHeaders()->has('Origin')
		&& $request->getHeaders()->get('Origin') !== $request->getSchemeAndHttpHost();
	}

	/**
	 * Check if the CORS request is a preflight request
	 *
	 * @param  Request $request
	 *
	 * @return bool
	 */
	public function isPreflightRequest(Request $request) {
		return $this->isCorsRequest($request)
		&& $request->getMethod() === 'OPTIONS'
		&& $request->getHeaders()->has('Access-Control-Request-Method');
	}

	/**
	 * Handle if the CORS request
	 *
	 * @param Request $request
	 *
	 * @return bool|Response\JSON
	 */
	public function handlePreflightRequest(Request $request) {
		$preflightRequest = $this->checkPreflightRequestConditions($request);
		if (true !== $preflightRequest) {
			return $preflightRequest;
		}
		return $this->buildPreflightResponse($request);
	}

	/**
	 * Check as conditions of the CORS request
	 *
	 * @param Request $request
	 *
	 * @return bool|Response\JSON
	 */
	private function checkPreflightRequestConditions(Request $request) {
		if (!$this->checkOriginRequest($request)) {
			return $this->createBadRequestResponse(Response::HTTP_FORBIDDEN, 'Origin not allowed');
		}
		if (!$this->checkAllowedMethod($request)) {
			return $this->createBadRequestResponse(Response::HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
		}
		// if allowedHeaders has been set to true ('*' allow all flag) just skip this check
		if ($this->corsOptions->getAllowedHeaders() !== '*' && $request->getHeaders()->has('Access-Control-Request-Headers')) {
			$headers = strtolower($request->getHeaders()->get('Access-Control-Request-Headers'));
			$requestHeaders = explode(',', $headers);
			foreach ($requestHeaders as $header) {
				if (!in_array(trim($header), $this->corsOptions->getAllowedHeaders())) {
					return $this->createBadRequestResponse(Response::HTTP_FORBIDDEN, 'Header not allowed');
				}
			}
		}
		return true;
	}


	/**
	 * Build the preflight response with headers allow CORS
	 *
	 * @param Request $request
	 *
	 * @return Response\JSON
	 */
	private function buildPreflightResponse(Request $request) {
		$response = new Response\JSON(null, Response::HTTP_OK);
		$responseHeaders = $response->getHeaders();
		$requestHeaders = $request->getHeaders();
		if ($this->corsOptions->isSupportCredentials()) {
			$responseHeaders->set('Access-Control-Allow-Credentials', 'true');
		}
		$responseHeaders->set('Access-Control-Allow-Origin', $requestHeaders->get('Origin'));
		if ($this->corsOptions->getMaxAge() > 0) {
			$responseHeaders->set('Access-Control-Max-Age', $this->corsOptions->getMaxAge());
		}
		$allowMethods = $this->corsOptions->getAllowedMethods() === '*'
			? strtoupper($requestHeaders->get('Access-Control-Request-Method'))
			: implode(', ', $this->corsOptions->getAllowedMethods());
		$responseHeaders->set('Access-Control-Allow-Methods', $allowMethods);
		$allowHeaders = $this->corsOptions->getAllowedHeaders() === '*'
			? strtoupper($requestHeaders->get('Access-Control-Request-Headers'))
			: implode(', ', $this->corsOptions->getAllowedHeaders());
		$exposedHeaders = $this->corsOptions->getExposedHeaders();
		if ($exposedHeaders) {
			$responseHeaders->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
		}
		$responseHeaders->set('Access-Control-Allow-Headers', $allowHeaders);
		return $response;
	}

	/**
	 * Add access control headers to actual response headers
	 *
	 * @param Response $response
	 * @param Request  $request
	 *
	 */
	public function addActualRequestHeaders(Response $response, Request $request) {
		$responseHeaders = $response->getHeaders();
		$responseHeaders->set('Access-Control-Allow-Origin', $request->getHeaders()->get('Origin'));
		if (!$responseHeaders->has('Vary')) {
			$responseHeaders->set('Vary', 'Origin');
		} else {
			$responseHeaders->set('Vary', $responseHeaders->get('Vary') . ', Origin');
		}
		if ($this->corsOptions->isSupportCredentials()) {
			$responseHeaders->set('Access-Control-Allow-Credentials', 'true');
		}
		$exposedHeaders = $this->corsOptions->getExposedHeaders();
		if ($exposedHeaders) {
			$responseHeaders->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
		}
	}

	/**
	 * Create an bad response
	 *
	 * @param int    $code   The code of the bad response
	 * @param string $reason The reason of the response
	 *
	 * @return Response\JSON
	 */
	private function createBadRequestResponse($code, $reason = '') {
		return new Response\JSON($reason, $code);
	}

	/**
	 * Check if the origin of the request is allowed
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function checkOriginRequest(Request $request) {
		$allowedOrigins = $this->corsOptions->getAllowedOrigins();
		// allow all '*' flag
		if ($allowedOrigins === '*') {
			return true;
		}
		$origin = $request->getHeaders()->get('Origin');
		return in_array($origin, $allowedOrigins);
	}

	/**
	 * Check if the request method is allowed
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function checkAllowedMethod(Request $request) {
		$allowedMethods = $this->corsOptions->getAllowedMethods();
		// allow all '*' flag
		if ($allowedMethods === '*') {
			return true;
		}
		$requestMethod = strtoupper($request->getHeaders()->get('Access-Control-Request-Method'));
		return in_array($requestMethod, $this->corsOptions->getAllowedMethods());
	}

}