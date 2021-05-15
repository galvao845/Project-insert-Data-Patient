<?php
namespace Zeedhi\Framework\DataSource;
/**
 * Class DataSet
 *
 * Contain data|rows that a DataSource\Manager will persist, and all necessary meta-data about it.
 *
 * @package Zeedhi\Framework\DataSource
 */
class DataSet implements AssociatedWithDataSource{

    /** @var string */
    protected $dataSourceName;

    /** @var array */
    protected $rows;

    /**
     * @param string $dataSourceName The name of the dataSource of this data set.
     * @param array  $rows      Optional.
     */
    public function __construct($dataSourceName, array $rows = array()) {
        $this->dataSourceName = $dataSourceName;
        $this->rows = $rows;
    }

    /**
     * Return name of table of this DataSet.
     *
     * @return string
     */
    public function getDataSourceName() {
        return $this->dataSourceName;
    }

    /**
     * Set name of the dataSource of this dataSet
     *
     * @param string $dataSourceName
     */
    public function setDataSourceName($dataSourceName)
    {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * Return the rows that is this DataSet.
     *
     * @return array
     */
    public function getRows() {
        return $this->rows;
    }
}