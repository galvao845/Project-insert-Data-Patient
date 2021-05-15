<?php
namespace Zeedhi\Framework\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Class NativeSession
 *
 * @package Zeedhi\Framework\Session\Storage
 */
class NativeSession extends NativeSessionStorage implements SessionStorageInterface {

	protected function clean() {
		// clear out the bags
		foreach ($this->bags as $bag) {
			$bag->clear();
		}
		// clear out the session
		$_SESSION = array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy() {
		$this->clean();
		if (ini_get("session.use_cookies")) {
			if (!headers_sent($file, $line)) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}
		}
		$ret = session_destroy();
		$this->closed = true;
		$this->started = false;
		return $ret;
	}

}