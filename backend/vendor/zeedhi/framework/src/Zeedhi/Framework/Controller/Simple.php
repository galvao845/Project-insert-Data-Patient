<?php
namespace Zeedhi\Framework\Controller;

/**
 * Class Simple
 *
 * A minimum controller needs. Just add a __call method to evade that a non-existent method call triggers a error.
 * Extended this will trigger a exception instead. Use of this ins't mandatory, but is highly recommend.
 *
 * @package Zeedhi\Framework\Controller
 */
abstract class Simple {

    /**
     * Throws a exception when a non implemented method is called.
     * This is a PHP magic functions. For more information see:
     * http://php.net/manual/pt_BR/language.oop5.overloading.php#language.oop5.overloading.methods
     *
     * @param string $method The non implemented method name.
     * @param array  $params The parameter passed to the method. It's a array of mixed values.
     *
     * @throws Exception with message methodDoestNotExist.
     */
    public function __call($method, $params) {
        throw Exception::methodDoestNotExist(get_class($this), $method);
    }
} 