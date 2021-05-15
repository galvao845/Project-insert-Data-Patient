<?php
namespace Zeedhi\Framework\Cache\Type;

use Doctrine\Common\Cache\ArrayCache;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;

/**
 * Class ArrayImpl
 *
 * Class to provide an implementation of Array Cache Driver
 *
 * @package Zeedhi\Framework\Cache
 */
class ArrayImpl extends ArrayCache implements Cache {

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