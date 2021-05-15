<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo;

class NameProvider extends \Zeedhi\Framework\Util\NameProvider
    implements  \Zeedhi\Framework\DataSource\Manager\Doctrine\NameProvider {

    /**
     * {@inheritdoc}
     */
    public function getDataSourceByName($dataSourceName) {
        return Configuration::factoryFromFileLocation($this->dataSourceDirectory, $dataSourceName);
    }

}