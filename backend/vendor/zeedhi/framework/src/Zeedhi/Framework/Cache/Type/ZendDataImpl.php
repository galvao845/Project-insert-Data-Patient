<?php
namespace Zeedhi\Framework\Cache\Type;

use Doctrine\Common\Cache\ZendDataCache;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;

/**
 * Class ZendImpl
 *
 * Class to provide an implementation of Zend Cache Driver
 *
 * @package Zeedhi\Framework\Cache
 */
class ZendDataImpl extends ZendDataCache implements Cache {

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
		$data = $this->doFetch($key);
		if ($data) {
			return $data;
		} else {
			throw Exception::valueNotFound($key);
		}
	}

    /**
     * {@inheritdoc}
     */
    public function contains($key){
        return $this->doContains($key);
    }

	/**
	 * {@inheritdoc}
	 */
	public function delete($key) {
		return $this->doDelete($key);
	}
}