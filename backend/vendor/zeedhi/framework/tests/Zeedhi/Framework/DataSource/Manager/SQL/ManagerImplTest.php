<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\SQL;

use HumanRelation\Util\DataSource\NameProvider;
use tests\Zeedhi\Framework\DataSource\Manager\ManagerTestCase;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\SQL\ManagerImpl;

class ManagerImplTest extends ManagerTestCase {

    /**
     * @return Manager
     */
    protected function getDataSourceManager() {
        return new ManagerImpl($this->connection, new NameProvider(), $this->parameterBag);
    }

    public function testFindAssertingColumnTypesInRelation() {
        $this->markTestSkipped("This can't be granted in SQL DataSources, due oracle driver return integers as strings.");
        parent::testFindAssertingColumnTypesInRelation();
    }

}