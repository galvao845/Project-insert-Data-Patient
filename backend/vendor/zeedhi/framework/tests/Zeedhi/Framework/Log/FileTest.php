<?php
namespace tests\Zeedhi\Framework\Log;

use tests\Zeedhi\Framework\Log\Test\LoggerInterfaceTest;
use Zeedhi\Framework\Log\File;

class FileTest extends LoggerInterfaceTest {

    /** @var string */
    protected $fileName;
    /** @var File */
    protected $logger;

    protected function setUp() {
        $this->fileName = __DIR__ . "/logger.log";
        $this->logger = new File($this->fileName);
    }

    protected function tearDown() {
        unset($this->logger);
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }

    /**
     * @return File
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
        $messages = explode("\n", trim(file_get_contents($this->fileName)));
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
