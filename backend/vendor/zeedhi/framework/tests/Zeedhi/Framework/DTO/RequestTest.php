<?php
namespace tests\Zeedhi\Framework\DTO;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DTO\Request;

class RequestTest extends \PHPUnit\Framework\TestCase {

    public function testRequest() {
        $request = new Request("Method", "/route/path", uniqid());
        $request->setParameter("foo", "bar");
        $this->assertEquals("bar", $request->getParameter("foo"));
        $this->expectException(DTO\Exception::class);
        $this->expectExceptionMessage('Parameter baz not found.');
        $request->getParameter("baz");
        $this->assertEquals(array('foo' => 'bar'), $request->getParameters());
    }
}
