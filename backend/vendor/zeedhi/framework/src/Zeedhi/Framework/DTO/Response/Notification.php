<?php
namespace Zeedhi\Framework\DTO\Response;

class Notification {

    const TYPE_SUCCESS = 'success';
    const TYPE_ALERT   = 'alert';
    const TYPE_ERROR   = 'error';

    /** @var string */
    protected $message;
    /** @var string */
    protected $type;
    /** @var array */
    protected $variables;

    function __construct($message, $type = self::TYPE_SUCCESS, $variables = array()) {
        $this->message = $message;
        $this->type = $type;
        $this->variables = $variables;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}