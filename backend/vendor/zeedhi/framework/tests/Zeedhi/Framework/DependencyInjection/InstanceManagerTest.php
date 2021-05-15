<?php
namespace tests\Zeedhi\Framework\DependencyInjection;

use \Zeedhi\Framework\DependencyInjection\InstanceManager;

class InstanceManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var InstanceManager $managerDI */
    protected $managerDI;

    public function setUp()
    {
        $this->managerDI = InstanceManager::getInstance();
    }

    public function testCreateInstanceOfDI()
    {
        $this->assertInstanceOf('Zeedhi\Framework\DependencyInjection\InstanceManager', $this->managerDI, 'Return must be expected an instance of InstanceManager');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $this->managerDI->getContainer(), 'Return must be expected an instance of InstanceManager');
    }

    public function testRegisterService() {
        $this->managerDI->registerService('Hello', new \tests\Zeedhi\Framework\ApplicationMocks\Hello());
        $this->assertInstanceOf('tests\Zeedhi\Framework\ApplicationMocks\Hello', $this->managerDI->getService('Hello'), 'Return must be expected an instance of Hello');
    }

    public function testSetDependencyAsParameter() {
        $this->managerDI->setParameter('Hello', new \tests\Zeedhi\Framework\ApplicationMocks\Hello());
        $this->assertInstanceOf('tests\Zeedhi\Framework\ApplicationMocks\Hello', $this->managerDI->getParameter('Hello'), 'Return must be expected an instance of Hello');
    }


    public function testLoadDependencyXMLFromFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $services = __DIR__.$ds.'..'.$ds.'ApplicationMocks'.$ds.'mocksInjection'.$ds.'services.xml';
        $this->managerDI->loadFromFile($services, InstanceManager::XML);
        $this->assertInstanceOf('tests\Zeedhi\Framework\ApplicationMocks\Hello', $this->managerDI->getService('Hello'), 'Return must be expected an instance of Hello');
    }

    public function testLoadDependencyPHPFromFile()
    {
        $this->markTestSkipped('Cause the include bug tests in running with @runInSeparateProcess');
        $ds = DIRECTORY_SEPARATOR;
        $services = __DIR__.$ds.'..'.$ds.'ApplicationMocks'.$ds.'mocksInjection'.$ds.'services.php';
        $this->managerDI->loadFromFile($services, InstanceManager::PHP);
        $this->assertInstanceOf('tests\Zeedhi\Framework\ApplicationMocks\Hello', $this->managerDI->getService('Hello'), 'Return must be expected an instance of Hello');
    }

    public function testLoadDependencyYAMLFromFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $services = __DIR__.$ds.'..'.$ds.'ApplicationMocks'.$ds.'mocksInjection'.$ds.'services.yaml';
        $this->managerDI->loadFromFile($services, InstanceManager::YAML);
        $this->assertInstanceOf('tests\Zeedhi\Framework\ApplicationMocks\Hello', $this->managerDI->getService('Hello'), 'Return must be expected an instance of Hello');
    }

    public function testCompileDependencyContainer() {
        $ds = DIRECTORY_SEPARATOR;
        $services = __DIR__.$ds.'..'.$ds.'ApplicationMocks'.$ds.'mocksInjection'.$ds.'services.xml';
        $this->managerDI->loadFromFile($services, InstanceManager::XML);
        $this->managerDI->compile();
        $this->assertTrue($this->managerDI->getContainer()->isFrozen(), 'Container must be in a state of frozen');
    }


}
