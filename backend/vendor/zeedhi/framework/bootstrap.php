<?php
use \Doctrine\DBAL\Types\Type;
use \Zeedhi\Framework\DependencyInjection\InstanceManager;

Type::overrideType(Type::DATE, 'Zeedhi\Framework\ORM\DateType');
Type::overrideType(Type::DATETIME, 'Zeedhi\Framework\ORM\DateTimeType');
Type::overrideType(Type::INTEGER, 'Zeedhi\Framework\ORM\IntegerType');
InstanceManager::getInstance()->loadFromFile(__DIR__.DIRECTORY_SEPARATOR.'services.xml');