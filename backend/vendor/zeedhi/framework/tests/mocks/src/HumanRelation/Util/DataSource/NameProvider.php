<?php
namespace HumanRelation\Util\DataSource;

use Doctrine\Common\Inflector\Inflector;
use Zeedhi\Framework\DataSource\Configuration;

class NameProvider implements \Zeedhi\Framework\DataSource\Manager\Doctrine\NameProvider
{

    public function getClassName($tableName)
    {
        return "\\HumanRelation\\Entities\\" . Inflector::classify(strtolower($tableName));
    }

    public function getDataSourceByName($dataSourceName) {
        $ds = DIRECTORY_SEPARATOR;
        $dataSourceDirectory = __DIR__.$ds.'..'.$ds.'gen'.$ds.'datasources';
        return Configuration::factoryFromFileLocation($dataSourceDirectory, $dataSourceName);
    }
} 