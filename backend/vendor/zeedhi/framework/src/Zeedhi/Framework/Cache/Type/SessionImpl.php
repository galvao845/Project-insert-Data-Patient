<?php
namespace Zeedhi\Framework\Cache\Type;

use Zeedhi\Framework\Session\SessionInterface;
use Zeedhi\Framework\Cache\Cache;

/**
 * Class SessionImpl
 *
 * Class to provide an implementation of Session cache driver
 *
 * @package Zeedhi\Framework\Cache
 */
class SessionImpl implements Cache {

	/** @var SessionInterface */
	protected $session;

	public function __construct(SessionInterface $session) {
		$this->session = $session;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($id) {
		return $this->session->get($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($id, $data, $lifeTime = 0) {
		$this->session->set($id, $data);
		return $this->session->has($id);
	}

    /**
     * {@inheritdoc}
     */
    public function contains($key){
        return $this->session->has($key);
    }

	/**
	 * {@inheritdoc}
	 */
	public function delete($id) {
		// @TODO No implemented yet.
	}
}