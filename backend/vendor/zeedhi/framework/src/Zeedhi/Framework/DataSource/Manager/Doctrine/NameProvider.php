<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine;

/**
 * Interface NameProvider
 *
 * The interface used by Doctrine\ManagerImpl to be used to convert TABLE_NAME into \Namespaced\ClassName
 * and load configuration of the DataSource
 *
 * @package Zeedhi\Framework\DataSource\Manager\Doctrine
 */
interface NameProvider {

    /**
     * Convert a TABLE_NAME into \Namespaced\ClassName.
     *
     * @param string $tableName The TABLE_NAME
     *
     * @return string The \Namespaced\ClassName.
     */
    public function getClassName($tableName);

    /**
     * Return the configuration of data source.
     *
     * @param string $dataSourceName The name of the dataSource
     *
     * @return \Zeedhi\Framework\DataSource\Configuration Return configuration the dataSource
     */
    public function getDataSourceByName($dataSourceName);

} 