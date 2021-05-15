<?php
namespace tests\Zeedhi\Framework\Log;

use tests\Zeedhi\Framework\Log\Test\LoggerInterfaceTest;
use Zeedhi\Framework\Log\Memory;

class MemoryTest extends LoggerInterfaceTest {

    /** @var Memory */
    protected $logger;

    protected function setUp() {
        $this->logger = new Memory();
    }

    /**
     * @return Memory
     */
    function getLogger() {
        return $this->logger;
    }

    /**
     * This must return the log messages in order with a simple formatting: "<LOG LEVEL> <MESSAGE>"
     *
     * Example ->error('Foo') would yield "error Foo"
     *
     * @return string[]
     */
    function getLogs() {
        $logLines = explode("\n", trim($this->logger->getLog()));
        foreach($logLines as &$logLine) {
            $logLine = str_replace(array("[", "]"), "", $logLine);
        }

        return $logLines;
    }


    public function testObjectCastToString() {
        $dummy = $this->getMockBuilder('Psr\Log\Test\DummyTest')
            ->setMethods(array('__toString'))->getMock();
        $dummy->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('DUMMY'));

        $this->getLogger()->warning($dummy);
    }
}
