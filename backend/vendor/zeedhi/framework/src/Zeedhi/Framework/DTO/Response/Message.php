<?php
namespace Zeedhi\Framework\DTO\Response;
/**
 * Class Message
 *
 * Contains the messages that are returned in response
 *
 * @package Zeedhi\Framework\DTO\Response
 */
class Message
{
    /**
     * Defines message as WARNING.
     * @const  TYPE_WARNING
     */
    const TYPE_WARNING = 'W';
    /**
     * Defines message as MESSAGE.
     * @const  TYPE_MESSAGE
     */
    const TYPE_MESSAGE = 'M';
    /**
     * Defines message as ERROR.
     * @const  TYPE_ERROR
     */
    const TYPE_ERROR   = 'E';

    /** @var string */
    protected $type;
    /** @var string */
    protected $message;
    /** @var integer */
    protected $fadeTime;
    /** @var array */
    protected $variables;

    /**
     * Constructor
     *
     * @param string           $message  The message that are returned in response
     * @param null|string      $type     Optional
     * @param null|int         $fadeTime Optional
     */
    function __construct($message, $type = null, $fadeTime = null, $variables = array())
    {
        $this->message = $message;
        $this->type = $type ? : Message::TYPE_MESSAGE;
        $this->fadeTime = $fadeTime ? : self::fadeTimeByType($this->type);
        $this->variables = $variables;
    }


    /**
     * Returns the time to fade in accordance with the type of the message.
     *
     * @param null|string $type Optional
     * @return int
     */
    public static function fadeTimeByType($type = Message::TYPE_MESSAGE)
    {
        $fadeTime = 5000;
        switch ($type) {
            case Message::TYPE_ERROR:
                $fadeTime = 30000;
                break;
            case Message::TYPE_WARNING:
            case Message::TYPE_MESSAGE:
                break;
        }
        return $fadeTime;
    }

    /**
     * Returns message in this Message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns type of this Message.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns time of fadeTime in this Message.
     *
     * @return int
     */
    public function getFadeTime()
    {
        return $this->fadeTime;
    }

    /**
     * Returns all variables of this Message.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}