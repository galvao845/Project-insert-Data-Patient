<?php
namespace tests\Zeedhi\Framework\Controller;

use Zeedhi\Framework\Controller\Crud;

class CrudImpl extends Crud {

    protected $dataSourceName = 'regions';

    /**
     * @param string $dataSourceName
     */
    public function setDataSourceName($dataSourceName) {
        $this->dataSourceName = $dataSourceName;
    }
}