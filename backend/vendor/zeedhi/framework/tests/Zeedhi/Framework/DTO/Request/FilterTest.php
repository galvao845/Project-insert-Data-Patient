<?php
namespace tests\Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DTO\Request\Filter;
use Zeedhi\Framework\Routing\Router;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    CONST USER_ID = "bhlb9n2oq8lac3di";

    public function testCreateRequestDataSet()
    {
        $requestFilter = new Filter(new \Zeedhi\Framework\DataSource\FilterCriteria('REGIONS'), Router::METHOD_POST, "/blog", self::USER_ID);
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\FilterCriteria', $requestFilter->getFilterCriteria(), 'It is expected an instance of FilterCriteria.');
    }
}
