<?php
namespace Zeedhi\Framework\Log;

use Psr\Log\LogLevel;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger {

    abstract protected function writeLine($message);

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    protected function interpolate($message, array $context = array()) {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $this->handleValue($val);
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    protected function validateMessageLevel($level) {
        static $validLevels = array(
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        );

        if (!in_array($level, $validLevels)) {
            throw Exception::invalidLogLevel($level);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @throws Exception Invalid log level.
     *
     * @return null
     */
    public function log($level, $message, array $context = array()) {
        $this->validateMessageLevel($level);
        $interpolatedMessage = $this->interpolate($message, $context);
        $this->writeLine("[{$level}] {$interpolatedMessage}");
    }

    /**
     * @param $val
     * @return string
     */
    protected function handleIfObject($val) {
        if (is_object($val)) {
            if (method_exists($val, "__toString")) {
                $val = $val->__toString();
            } else {
                $val = get_class($val);
            }
        }
        return $val;
    }

    /**
     * @param $val
     * @return string
     */
    protected function handleIfArray($val) {
        // http://php.net/manual/pt_BR/function.strtr.php#112930
        if (is_array($val)) {
            $val = json_encode($val);
        }
        return $val;
    }

    /**     *
     * @param $val
     * @return string
     */
    protected function handleValue($val) {
        $val = $this->handleIfObject($val);
        $val = $this->handleIfArray($val);
        return (string) $val;
    }
}