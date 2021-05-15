<?php
namespace tests\Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DTO\Request\Row;
use Zeedhi\Framework\Routing\Router;

class RowTest extends \PHPUnit\Framework\TestCase
{
    CONST USER_ID = "bhlb9n2oq8lac3di";

    public function testCreateRequestDataSet()
    {
        $currentRow = array('REGION_ID' => 4, 'REGION_NAME' => 'Africa');
        $requestRow = new Row($currentRow, Router::METHOD_POST, "/blog", self::USER_ID);
        $this->assertEquals($currentRow, $requestRow->getRow(), 'It is expected an row witch equals "$currentRow".');
    }
}
