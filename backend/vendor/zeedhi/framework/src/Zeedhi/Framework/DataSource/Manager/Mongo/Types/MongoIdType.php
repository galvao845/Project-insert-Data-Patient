<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\BSON\ObjectId;

class MongoIdType extends Type {

	public function convertToDatabaseValue($value) {
		if ($value === null) {
			return null;
		}
		if (!$value instanceof ObjectId) {
			try {
				$value = new ObjectId($value);
			} catch (InvalidArgumentException $e) {
				$value = new ObjectId();
			}
		}
		return $value;
	}

	public function convertToPHPValue($value) {
		return $value instanceof ObjectId ? (string)$value : $value;
	}
}