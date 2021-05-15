<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\IdProvider;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\Manager\IdProvider\ManagerImpl;
use Zeedhi\Framework\DataSource\Manager\IdProvider\IdProvider;

use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;

class ManagerImplTest extends \PHPUnit\Framework\TestCase {

    const DATA_SOURCE_NAME = 'phoneList';
    const SEQUENCE_NAME = 'SEQUENCE_NAME';

    /** @var Manager\Doctrine\NameProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $nameProvider;
    /** @var Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;
    /** @var ManagerImpl */
    protected $idProviderManager;
    /** @var IdProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $idProvider;
    /** @var Configuration|\PHPUnit_Framework_MockObject_MockObject */
    protected $configuration;


    public function setUp()
    {
        $this->nameProvider = $this->getMockForAbstractClass(Manager\Doctrine\NameProvider::class);

        $this->manager = $this->getMockBuilder('\Zeedhi\Framework\DataSource\Manager')
                              ->disableOriginalConstructor()
                              ->setMethods( array('persist', 'delete', 'findBy', 'populateDataSet', 'buildAllDataFilter') )
                              ->getMock();

        $this->idProvider = $this->getMockForAbstractClass(IdProvider::class);

        $this->configuration = $this->getMockBuilder('\Zeedhi\Framework\DataSource\Configuration')
                                    ->disableOriginalConstructor()
                                    ->setMethods(array('getSequentialColumn'))
                                    ->getMock();

        $this->idProviderManager = new ManagerImpl($this->nameProvider, $this->manager, $this->idProvider);
    }

    public function testPersist()
    {
        $contactName =      'Mateus';
        $contactEmail =     'mateus@teknisa.com';
        $contactPhone =     '(31) 3345-5678';
        $contactCellphone = '(31) 99845-4321';

        $data = array(
            new Row(array(
                'ID'        => null,
                'NAME'      => $contactName,
                'EMAIL'     => $contactEmail,
                'PHONE'     => $contactPhone,
                'CELLPHONE' => $contactCellphone,
                '__is_new'  => true
            ))
        );

        $this->idProvider->expects($this->once())
                         ->method('getNextId')
                         ->willReturn(1);

        $rows = array(
            new Row(array(
                'ID'        => 1,
                'NAME'      => $contactName,
                'EMAIL'     => $contactEmail,
                'PHONE'     => $contactPhone,
                'CELLPHONE' => $contactCellphone,
                '__is_new'  => true
            ))
        );
        $persistDataSet = new DataSet(self::DATA_SOURCE_NAME, $rows);
        $this->manager->expects($this->once())
                      ->method('persist')
                      ->with($persistDataSet)
                      ->willReturn(array(array('ID' => 1)));

        $this->configuration->expects($this->once())
                            ->method('getSequentialColumn')
                            ->willReturn('ID');

        $this->nameProvider->expects($this->once())
                           ->method('getDataSourceByName')
                           ->with(self::DATA_SOURCE_NAME)
                           ->willReturn($this->configuration);

        $dataSet = new DataSet(self::DATA_SOURCE_NAME, $data);

        $persistedRows = $this->idProviderManager ->persist($dataSet);

        $this->assertCount(1, $persistedRows);
        $this->assertContains(array('ID' => 1), $persistedRows);
    }

    public function testPersistWithoutNewRows() {

        $contactName =      'Mateus';
        $contactEmail =     'mateus@teknisa.com';
        $contactPhone =     '(31) 3345-5678';
        $contactCellphone = '(31) 99845-4321';

        $data = array(
            new Row(array(
                'ID'        => 1,
                'NAME'      => $contactName,
                'EMAIL'     => $contactEmail,
                'PHONE'     => $contactPhone,
                'CELLPHONE' => $contactCellphone,
                '__is_new'  => false
            ))
        );

        $this->idProvider->expects($this->never())
            ->method('getNextId');

        $rows = array(
            new Row(array(
                'ID'        => 1,
                'NAME'      => $contactName,
                'EMAIL'     => $contactEmail,
                'PHONE'     => $contactPhone,
                'CELLPHONE' => $contactCellphone,
                '__is_new'  => false
            ))
        );

        $persistDataSet = new DataSet(self::DATA_SOURCE_NAME, $rows);
        $this->manager->expects($this->once())
            ->method('persist')
            ->with($persistDataSet)
            ->willReturn(array(array('ID' => 1)));

        $this->configuration->expects($this->once())
            ->method('getSequentialColumn')
            ->willReturn('ID');

        $this->nameProvider->expects($this->once())
            ->method('getDataSourceByName')
            ->with(self::DATA_SOURCE_NAME)
            ->willReturn($this->configuration);

        $dataSet = new DataSet(self::DATA_SOURCE_NAME, $data);

        $persistedRows = $this->idProviderManager->persist($dataSet);

        $this->assertCount(1, $persistedRows);
        $this->assertContains(array('ID' => 1), $persistedRows);
    }

    public function testDelete() {
        $contactId = 1;

        $rows = array(array('ID' => $contactId));
        $dataSet = new DataSet(self::DATA_SOURCE_NAME, $rows);

        $this->manager->expects($this->once())
                      ->method('delete')
                      ->with($dataSet)
                      ->willReturn(array(array('ID' => 1)));


        $deletedRows = $this->idProviderManager ->delete($dataSet);

        $this->assertCount(1, $deletedRows);
        $this->assertContains(array('ID' => 1), $deletedRows);
    }

    public function testFindBy() {
        $id = 1;

        $expectedContactName =      'Mateus';
        $expectedContactEmail =     'mateus@teknisa.com';
        $expectedContactPhone =     '(31) 3345-5678';
        $expectedContactCellphone = '(31) 99845-4321';

        $conditions = array(
            'columnName' => 'ID',
            'operator' => '=',
            'value' => $id
        );
        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME, $conditions);
        $this->manager->expects($this->once())
                      ->method('findBy')
                      ->with($filterCriteria)
                      ->willReturn(array(array(
                            'ID'        => $id,
                            'NAME'      => $expectedContactName,
                            'EMAIL'     => $expectedContactEmail,
                            'PHONE'     => $expectedContactPhone,
                            'CELLPHONE' => $expectedContactCellphone
                        )));

        $rows = $this->idProviderManager->findBy($filterCriteria);

        $this->assertCount(1, $rows);
        $this->assertContains(array(
            'ID'        => $id,
            'NAME'      => $expectedContactName,
            'EMAIL'     => $expectedContactEmail,
            'PHONE'     => $expectedContactPhone,
            'CELLPHONE' => $expectedContactCellphone
        ), $rows);
    }

    public function testPopulateDataSet(){
        $dataSet = new DataSet('simple_regions', array(
            new Row(array('column' => array('__ALL' => [])))
        ));
        $rows = array(array('ID' => 1));
        $dataSetToReturn = new DataSet(self::DATA_SOURCE_NAME, $rows);

        $this->manager->expects($this->once())
                      ->method('findBy')
                      ->willReturn($dataSetToReturn);

        $expectedResult = new DataSet('simple_regions', array(
            new Row(array(
                'column' => $rows
            ))
        ));

        $finalResult = $this->idProviderManager->populateDataSet($dataSet);

        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => 'NAME', 'operator' => '=', 'value' => 'Europe')));
        $rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
        $expectedFilterCriteria = new FilterCriteria('simple_regions');
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
        $expectedFilterCriteria->addCondition('NAME', '=', 'Europe');
        $finalResult = $this->idProviderManager->buildAllDataFilter($rows, $value, 'column', 'simple_regions');

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }
}