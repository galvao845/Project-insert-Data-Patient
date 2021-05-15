<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Doctrine;

use tests\Zeedhi\Framework\DataSource\Manager\ManagerTestCase;
use HumanRelation\Util\DataSource\NameProvider;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\Doctrine\ManagerImpl;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DataSource\DataSet;

class ManagerImplTest extends ManagerTestCase {

    /**
     * @return Manager
     */
    protected function getDataSourceManager() {
        $managerImpl = new ManagerImpl($this->entityManager, new NameProvider(), $this->parameterBag);
        $managerImpl->setDateTimeFormat('Y-m-d H:i:s');
        return $managerImpl;
    }

    public function testPersistInsertWithSequencialId() {
        $row = new Row(array(
            "__is_new" => true,
            "ID" => null,
            "TEXT" => "teste"
        ));
        $dataSet = new DataSet("testSequence", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertTrue(is_array($persistedRows));
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        $this->assertArrayHasKey(0, $persistedRows);
        $this->assertTrue(is_array($persistedRows[0]));
        $this->assertArrayHasKey("ID", $persistedRows[0]);
        $this->assertTrue(is_numeric($persistedRows[0]["ID"]));
    }
}