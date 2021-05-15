<?php
namespace Zeedhi\Framework\DataSource\Manager\Security;

class Exception extends \Exception {

	public static function columnNameNotSafe($columnName){
		return self::sqlNotSafe('column name', $columnName);
	}

	public static function operatorNotSafe($operator){
		return self::sqlNotSafe('operator', $operator);
	}

	public static function groupByNotSafe($groupByParam){
		return self::sqlNotSafe('group by', $groupByParam);
	}

	public static function sqlNotSafe($part, $value){
		return new static("The {$part} \"{$value}\" is not safe to be executed.");
	}
}