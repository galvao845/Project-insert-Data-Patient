<?php
namespace Zeedhi\Framework\Remote;

interface HttpInterface {

    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';

    const STATUS_CODE_OK        = 200;
    const STATUS_CODE_NOT_FOUND = 404;

    const CONTENT_TYPE_HEADER = 'Content-Type';
    const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    /**
     * setBaseUrl
     *
     * Set the base URL that will be use in request. The URL may be appended with requestPath.
     *
     * @param string $url
     *
     * @return void
     */
    public function setBaseUrl($url);

    /**
     * setMethod
     *
     * Chose which HTTP method will be used to request (POST, GET, PUT, DELETE, ...)
     *
     * @param string $httpMethod
     *
     * @return void
     */
    public function setMethod($httpMethod);

    /**
     * setHeaders
     *
     * Pass values to be used in HTTP request Headers.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers);

    /**
     * request
     *
     * Receive request path and request field values and execute the request.
     *
     * @param string $requestPath Route that will be requested to.
     * @param array  $fields      Array with values that will be set by post.
     *
     * @return string The response body of request.
     */
    public function request($requestPath, array $fields = array());

}