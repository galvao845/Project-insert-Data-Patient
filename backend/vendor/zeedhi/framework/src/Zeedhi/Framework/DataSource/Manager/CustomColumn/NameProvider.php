<?php
namespace Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DataSource\Manager\Doctrine;
use Zeedhi\Framework\Util;

class NameProvider extends Util\NameProvider implements Doctrine\NameProvider {

    /**
     * getDataSourceByName
     * Return the configuration of data source.
     *
     * @param string $dataSourceName The name of the dataSource
     *
     * @return Configuration Return configuration the dataSource
     */
    public function getDataSourceByName($dataSourceName) {
        return Configuration::factoryFromFileLocation($this->dataSourceDirectory, $dataSourceName);
    }

}