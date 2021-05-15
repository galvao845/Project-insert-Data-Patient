<?php
namespace Zeedhi\Framework\HTTP\Response;

use Zeedhi\Framework\HTTP\Response;

/**
 * Class JSON
 *
 * Response represents an HTTP response in JSON format.
 *
 * @package Zeedhi\Framework\HTTP\Response
 */
class JSON extends Response {

    const CONTENT_TYPE = 'application/json';
    /**
     * Encodings of JSON
     *
     * @link http://php.net/manual/en/json.constants.php
     * @var int
     */
    protected $encodingOptions;

    /**
     * Constructor.
     *
     * @param mixed $content    The response data
     * @param int   $statusCode The response status code
     *
     * @throws \Exception
     */
    public function __construct($content = null, $statusCode = null) {
        if (null === $content) {
            $content = new \ArrayObject();
        }
        $this->encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $content = json_encode($content, $this->encodingOptions);
        if ($content === false) {
            throw new \Exception("Cant encode response content: ".json_last_error_msg());
        }

        parent::__construct($content, self::CONTENT_TYPE, $statusCode);
    }
} 