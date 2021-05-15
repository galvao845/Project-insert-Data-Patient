<?php
namespace tests\Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DTO\Request\DataSet;
use Zeedhi\Framework\Routing\Router;

class DataSetTest extends \PHPUnit\Framework\TestCase
{
    CONST USER_ID = "bhlb9n2oq8lac3di";

    public function testCreateRequestDataSet()
    {
        $requestDataSet = new DataSet(new \Zeedhi\Framework\DataSource\DataSet('REGIONS'), Router::METHOD_POST, "/blog", self::USER_ID);
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\DataSet', $requestDataSet->getDataSet(), 'It is expected an instance of DataSet.');
    }

}
