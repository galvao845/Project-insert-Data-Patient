<?php
namespace Zeedhi\Framework\Remote;

/**
 * class cURLRequest
 *
 * cURL Request abstraction
 *
 * @package Zeedhi\Notification
 */
class cURLRequest implements HttpInterface {

    /** @var string Server URL */
    protected $url;
    /** @var array Request headers */
    protected $headers = array();
    /** @var resource The cURL Handler */
    protected $curlHandler;
    /** @var string Route that will be requested to */
    protected $requestPath;
    /** @var string Request http method */
    protected $httpMethod;
    /** @var array */
    protected $curlOptions;
    /** @var array */
    protected $requestInfo = null;

    /**
     * Construct
     *
     * @param string $url
     * @param string $httpMethod
     */
    public function __construct($url, $httpMethod = self::METHOD_POST, array $curlOptions = array()) {
        $this->url  = $url;
        $this->httpMethod = $httpMethod;
        $this->curlOptions = $curlOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($url) {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($httpMethod) {
        $this->httpMethod = $httpMethod;
    }

    /**
     * setHeaders
     *
     * Set headers to be sent on request
     *
     * @param array $headers The headers values.
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
    }

    /**
     * createRequest
     *
     * Create a cURL resource and define request method as POST.
     */
    protected function createRequest() {
        $this->curlHandler = curl_init($this->url . $this->requestPath);
        curl_setopt($this->curlHandler, CURLOPT_POST, $this->httpMethod === self::METHOD_POST);
    }

    /**
     * makeResponseReturnAsString
     *
     * Ensure method doRequest(curl_exec) will return a string, with the response body.
     */
    protected function makeResponseReturnAsString() {
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * bindHeadersValues
     *
     * Call curl_setopt with option CURLOPT_HTTPHEADER with current headers values.
     */
    protected function bindHeadersValues() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $headers);
    }

    protected function isJSONRequest() {
        return isset($this->headers[self::CONTENT_TYPE_HEADER]) && $this->headers[self::CONTENT_TYPE_HEADER] === self::CONTENT_TYPE_APPLICATION_JSON;
    }

    /**
     * bindParametersValues
     *
     * Bind the given parameters to request.
     *
     * @param array $fields The parameters values.
     */
    protected function bindParametersValues(array $fields) {
        if ($this->isJSONRequest()) {
            $params = json_encode($fields);
        } else {
            $params = http_build_query($fields);
        }

        if ($this->httpMethod === self::METHOD_POST) {
            curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $params);
        } else {
            $uri = $this->encodeParams($fields);
            curl_setopt($this->curlHandler, CURLOPT_URL, $this->url . $this->requestPath . '?' . $uri);
        }
    }

    /**
     * encodeParams
     *
     * @param array $fields The parameters values.
     * @return string
     */
    protected function encodeParams($fields) {
        return implode('&', array_map(function($field, $value) {
            return $field . '=' . $value;
        }, array_keys($fields), array_values($fields)));
    }

    /**
     * bindCurlOptions
     *
     * Bind curl options to be used on request
     */
    protected function bindCurlOptions() {
        foreach ($this->curlOptions as $option => $value) {
            curl_setopt($this->curlHandler, $option, $value);
        }
    }

    /**
     * doRequest
     *
     * Call curl_exec and save request information.
     *
     * @return string
     */
    protected function doRequest() {
        $response = curl_exec($this->curlHandler);
        $this->requestInfo = curl_getinfo($this->curlHandler);
        return $response;
    }

    protected function checkCurlErrors() {
        $error = curl_error($this->curlHandler);
        $errorNum = curl_errno($this->curlHandler);
        if ($errorNum) {
            throw Exception::curlError($error, $errorNum);
        }
    }

    /**
     * closeConnection
     *
     * Close current cURL connection|session.
     */
    protected function closeConnection() {
        curl_close($this->curlHandler);
    }

    /**
     * request
     *
     * Do cURL Request
     *
     * @param string $requestPath Route that will be requested to.
     * @param array  $fields      Array with values that will be set by post.
     *
     * @return string The response body of request.
     */
    public function request($requestPath, array $fields = array()) {
        $this->requestPath = $requestPath;
        $this->createRequest();
        $this->makeResponseReturnAsString();
        $this->bindHeadersValues();
        $this->bindParametersValues($fields);
        $this->bindCurlOptions();
        $response = $this->doRequest();
        $this->checkCurlErrors();
        $this->closeConnection();
        return $response;
    }

    /**
     * getRequestInfo
     *
     * Get request information.
     *
     * @return array
     */
    public function getRequestInfo() {
        return $this->requestInfo;
    }

}