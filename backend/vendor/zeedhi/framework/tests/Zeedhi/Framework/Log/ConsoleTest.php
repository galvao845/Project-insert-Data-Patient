<?php
namespace tests\Zeedhi\Framework\Log;

use tests\Zeedhi\Framework\Log\Test\LoggerInterfaceTest;
use Zeedhi\Framework\Log\Console;

class ConsoleTest extends LoggerInterfaceTest {

    /** @var Console */
    protected $logger;
    /** @var string */
    protected $printedLines;

    protected function setUp() {
        $this->logger = new Console();
        ob_start();
    }

    protected function tearDown() {
        ob_end_clean();
    }

    /**
     * @return \Psr\Log\LoggerInterface
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
        $messages = explode("\n", trim(ob_get_contents()));
        foreach($messages as &$message) {
            $message = str_replace(array("[", "]"), "", $message);
        }

        return $messages;
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
