<?php
namespace Zeedhi\Framework\Cache\Type;

use Doctrine\Common\Cache\MemcachedCache;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;

/**
 * Class MemcacheImpl
 *
 * Class to provide an implementation of Memcached Cache Driver
 *
 * @package Zeedhi\Framework\Cache
 */
class MemcachedImpl extends MemcachedCache implements Cache {

	/**
	 * Constructor
	 *
	 * @param \Memcache $memcache
	 *
	 * @todo create an wrapper for Memcached
	 */
	public function __construct(\Memcached $memcache) {
		$this->setMemcached($memcache);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($key, $value, $lifeTime = 0) {
		return $this->doSave($key, $value, $lifeTime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($key) {
		if ($data = $this->doFetch($key)) {
			return $data;
		}
		throw Exception::valueNotFound($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($key) {
		return $this->doDelete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function contains($key){
		return $this->doContains($key);
	}
}