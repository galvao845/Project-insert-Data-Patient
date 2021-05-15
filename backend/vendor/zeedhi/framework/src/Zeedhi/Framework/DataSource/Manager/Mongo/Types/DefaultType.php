<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo\Types;


class DefaultType extends Type {

	public function convertToDatabaseValue($value) {
		return $value;
	}

	public function convertToPHPValue($value) {
		return $value;
	}
}