<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener;
use Zeedhi\Framework\Remote\FileUploader\CompositeListener;

class CompositeListenerTest extends \PHPUnit\Framework\TestCase {

    protected $firstListener;
    protected $secondListener;
    protected $compositeListener;

    public function setUp() {
        $this->firstListener = $this->getMockBuilder(Listener::class)
                                    ->setMethods(array('preDispatch'))
                                    ->getMock();

        $this->secondListener = $this->getMockBuilder(Listener::class)
                                    ->setMethods(array('preDispatch'))
                                    ->getMock();

        $this->compositeListener = new CompositeListener($this->firstListener, $this->secondListener);
    }

    public function testPreDispatch() {
        $request = new Request('POST', '/route', 'user-1');
        $this->compositeListener->preDispatch($request);

        $this->firstListener->expects($this->once())
                            ->method('preDispatch')
                            ->with($request);

        $this->secondListener->expects($this->once())
                             ->method('preDispatch')
                             ->with($request);

        $this->compositeListener->preDispatch($request);
    }

}