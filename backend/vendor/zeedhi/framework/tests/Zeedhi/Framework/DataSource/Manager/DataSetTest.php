<?php
namespace tests\Zeedhi\Framework\DataSource\Manager;

use Zeedhi\Framework\DataSource\DataSet;

class DataSetTest extends \PHPUnit\Framework\TestCase
{

    protected $regions = array(
        array(
            "REGION_ID" => 4,
            "REGION_NAME" => "Africa"
        ),
        array("REGION_ID" => 6,
            "REGION_NAME" => "Antarctica")
    );

    public function testCreateDataSet()
    {
        $dataSet = new DataSet('REGIONS', $this->regions);
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\DataSet', $dataSet, 'It is expected an instance of DataSet.');
        $this->assertEquals('REGIONS', $dataSet->getDataSourceName(), 'It is expected dataSet name "REGIONS"');
        $this->assertEquals($this->regions, $dataSet->getRows(), 'Content of dataSet ins\'t equals of regions an array.');
    }

    public function testSetDataSourceNameAfterDelay() {
        $dataSet = new DataSet('', $this->regions);
        $dataSet->setDataSourceName('REGIONS');
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\DataSet', $dataSet, 'It is expected an instance of DataSet.');
        $this->assertEquals('REGIONS', $dataSet->getDataSourceName(), 'It is expected dataSet name "REGIONS"');
    }
}
