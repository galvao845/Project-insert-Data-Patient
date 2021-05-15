<?php
namespace Zeedhi\Framework\DTO;

class Exception extends \Exception{

    public static function parameterNotFound($name) {
        return new static("Parameter {$name} not found.");
    }

}