<?php
namespace Zeedhi\Framework\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Zeedhi\Framework\Session\SessionBagInterface;

/**
 * Class FileSession
 *
 * Proxy to MockFileSession that is used to mock sessions for
 * functional testing when done in a single PHP process.
 *
 * @package Zeedhi\Framework\Session\Storage
 */
class FileSession extends MockFileSessionStorage implements SessionStorageInterface {

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