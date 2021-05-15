<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo\Types;


abstract class Type {

	const MONGO_ID = 'mongoId';
	const MONGO_DATE = 'mongoDate';
	const COLLECTION = 'collection';
	const DEFAULT_TYPE = 'default';

	private static $typeObjects = array();
	private static $typesMap = array(
		self::MONGO_ID => 'Zeedhi\Framework\DataSource\Manager\Mongo\Types\MongoIdType',
		self::MONGO_DATE => 'Zeedhi\Framework\DataSource\Manager\Mongo\Types\DateType',
		self::COLLECTION => 'Zeedhi\Framework\DataSource\Manager\Mongo\Types\CollectionType',
		self::DEFAULT_TYPE => 'Zeedhi\Framework\DataSource\Manager\Mongo\Types\DefaultType'
	);

	abstract public function convertToDatabaseValue($value);

	abstract public function convertToPHPValue($value);

	/**
	 * @param $type
	 *
	 * @return Type
	 */
	public static function getType($type) {
		if (!isset(self::$typesMap[$type])) {
			throw new \InvalidArgumentException('Mongo Type ' . $type . ' not defined');
		}
		if (!isset(self::$typeObjects[$type])) {
			$className = self::$typesMap[$type];
			self::$typeObjects[$type] = new $className;
		}
		return self::$typeObjects[$type];
	}

}