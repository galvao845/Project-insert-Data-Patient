<?php
namespace Zeedhi\Framework\DataSource;
/**
 * Interface Manager
 *
 * Definition of the methods used to persist a DataSet.
 *
 * @package Zeedhi\Framework\DataSource
 */
interface Manager {

    /**
     * Persist all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     */
    public function persist(DataSet $dataSet);

    /**
     * Delete all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     */
    public function delete(DataSet $dataSet);

    /**
     * Return a DataSet with rows that match the given criteria.
     *
     * @param FilterCriteria $filterCriteria
     *
     * @return DataSet The result of the filter criteria.
     */
    public function findBy(FilterCriteria $filterCriteria);

    /**
     * Return a populated dataSet.
     *
     * Verify if the dataSet contains a ALL_DATA's flag and populate it.
     *
     * @param DataSet $dataSet
     *
     * @return DataSet $dataSet
     */
    public function populateDataSet(DataSet $dataSet);

    /**
     * Return a filterCriteria.
     *
     * Build a filterCriteria based on dataSourceFilter.
     *
     * @param $rows
     * @param $value
     * @param $column
     *
     * @return FilterCriteria $filterCriteria
     */
    public function buildAllDataFilter($rows, $value, $column, $dataSourceName);

}