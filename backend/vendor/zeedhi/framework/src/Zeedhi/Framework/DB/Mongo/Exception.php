<?php
namespace Zeedhi\Framework\DB\Mongo;

class Exception extends \Exception {

	public static function dbNotSetted() {
		return new static("Db not setted");
	}

    public static function aggregateError(\MongoDB\Driver\Exception\Exception $e) {
        return new static("Error while executing aggregate: ".$e->getMessage(), $e->getCode(), $e);
    }
}