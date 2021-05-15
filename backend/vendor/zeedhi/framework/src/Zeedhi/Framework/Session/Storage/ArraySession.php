<?php
namespace Zeedhi\Framework\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zeedhi\Framework\Session\SessionBagInterface;

/**
 * Class ArraySession
 *
 * Proxy to MockArraySessionStorage that is used in unit tests
 *
 * @package Zeedhi\Framework\Session\Storage
 */
class ArraySession extends MockArraySessionStorage implements SessionStorageInterface {

	protected function clean() {
		/** @var SessionBagInterface $bag */
		foreach ($this->bags as $bag) {
			$bag->clear();
		}
		$this->data = array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy() {
		$this->clean();
		$this->closed = true;
		$this->started = false;
		return true;
	}
}