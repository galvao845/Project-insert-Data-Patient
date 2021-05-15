<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response\Method;

class MethodTest extends \PHPUnit\Framework\TestCase
{

    public function testCreateMethod()
    {
        $method = new Method('openWindow', array('login'));
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Method', $method, 'It is expected an instance of Method.');
        $this->assertEquals('openWindow', $method->getName(), 'It is expected an method name "openWindow"');
        $this->assertContains('login', $method->getParameters(), 'It is expected params "login"');
    }

}
