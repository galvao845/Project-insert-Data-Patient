<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\IdProvider\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Zeedhi\Framework\DataSource\Manager\IdProvider\Providers\SqlSequence;

class SqlSequenceTest extends \PHPUnit\Framework\TestCase {

    const SEQUENCE_NAME = 'SEQUENCE_NAME';

    /** @var SqlSequence */
    protected $sequenceIDProvider;
    /** @var Connection|\PHPUnit_Framework_MockObject_MockObject */
    protected $connection;
    /** @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataBasePlatform;

    public function setUp()
    {
        $this->dataBasePlatform = $this->getMockBuilder(AbstractPlatform::class)
            ->setMethods(
                array(
                    'getSequenceNextValSQL',
                    'getBooleanTypeDeclarationSQL',
                    'getIntegerTypeDeclarationSQL',
                    'getBigIntTypeDeclarationSQL',
                    'getSmallIntTypeDeclarationSQL',
                    '_getCommonIntegerTypeDeclarationSQL',
                    'initializeDoctrineTypeMappings',
                    'getClobTypeDeclarationSQL',
                    'getBlobTypeDeclarationSQL',
                    'getName'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(Connection::class)
             ->setMethods(array('getDatabasePlatform', 'fetchAssoc'))
             ->disableOriginalConstructor()
             ->getMock();

        $this->sequenceIDProvider = new SqlSequence($this->connection, self::SEQUENCE_NAME);
    }

    public function testGetId()
    {
        $this->dataBasePlatform->expects($this->once())
                                ->method('getSequenceNextValSQL')
                                ->with(self::SEQUENCE_NAME)
                                ->willReturn('SELECT ' . self::SEQUENCE_NAME . '.nextval FROM DUAL');

        $this->connection->expects($this->once())
                            ->method('getDatabasePlatform')
                            ->willReturn($this->dataBasePlatform);

        $this->connection->expects($this->once())
                            ->method('fetchAssoc')
                            ->with('SELECT ' . self::SEQUENCE_NAME . '.nextval FROM DUAL')
                            ->willReturn( array('NEXTVAL' => 1) );

        $id = $this->sequenceIDProvider->getNextId();

        $this->assertEquals(1, $id);
    }
}