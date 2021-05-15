<?php
namespace tests\Zeedhi\Framework\Controller;

use Zeedhi\Framework\Controller\CrudWithParameterBag;

class CrudWithParameterBagImpl extends CrudWithParameterBag {

    protected $dataSourceName = 'locations_by_region';
    protected $parameterBagColumns = array("REGION_ID");


}