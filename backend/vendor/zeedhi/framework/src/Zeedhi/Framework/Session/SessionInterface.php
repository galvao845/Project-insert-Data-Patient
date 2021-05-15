<?php
namespace Zeedhi\Framework\Session;

/**
 * Interface SessionInterface
 *
 * Interface for the session.
 *
 * @package Zeedhi\Framework\Session
 */
interface SessionInterface extends \Symfony\Component\HttpFoundation\Session\SessionInterface {
	/**
	 * Force the session destroy
	 * @return bool True if session destroyed, false if error.
	 */
	public function destroy();
} 