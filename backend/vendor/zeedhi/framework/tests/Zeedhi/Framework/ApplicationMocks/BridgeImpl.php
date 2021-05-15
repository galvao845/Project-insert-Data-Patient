<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\Socket\Server\Bridge;

class BridgeImpl extends Bridge {

	protected $eventCalled = false;

	public static function getSubscribedEvents() {
		return array(
			'socketOnOpen' => array('dispatchCall'),
			'socketOnError' => array('dispatchCall'),
			'socketOnClose' => array('dispatchCall'),
			'onMessage' => array('dispatchCall')
		);
	}

	public function isEventCalled() {
		return $this->eventCalled;
	}

	public function restoreEvent() {
		$this->eventCalled = false;
	}

	public function dispatchCall() {
		$this->eventCalled = true;
	}
}