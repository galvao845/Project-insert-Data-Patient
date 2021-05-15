<?php
namespace Zeedhi\Framework\DataSource;

interface AssociatedWithDataSource {


    /**
     * Return name of table of this DataSet.
     *
     * @return string
     */
    public function getDataSourceName();

}