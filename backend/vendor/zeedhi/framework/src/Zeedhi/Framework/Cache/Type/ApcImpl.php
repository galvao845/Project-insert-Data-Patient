<?php
namespace Zeedhi\Framework\Cache\Type;

use Doctrine\Common\Cache\ApcCache;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;

/**
 * Class ApcImpl
 *
 * Class to provide an implementation of APC Cache Driver
 *
 * @package Zeedhi\Framework\Cache
 */
class ApcImpl extends ApcCache implements Cache {

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