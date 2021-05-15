<?php
namespace Zeedhi\Framework\Cache\Type;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;
use Zeedhi\Framework\DB\Mongo\Mongo;

/**
 * Class MongoImpl
 *
 * Class to provide an implementation of Mongo cache driver
 *
 * @package Zeedhi\Framework\Cache\Type
 */
class MongoImpl implements Cache, CacheInterface {

	/**
	 * The data field will store the serialized PHP value.
	 */
	const DATA_FIELD = 'd';

	/**
	 * The expiration field will store a MongoDate value indicating when the
	 * cache entry should expire.
	 *
	 * With MongoDB 2.2+, entries can be automatically deleted by MongoDB by
	 * indexing this field wit the "expireAfterSeconds" option equal to zero.
	 * This will direct MongoDB to regularly query for and delete any entries
	 * whose date is older than the current time. Entries without a date value
	 * in this field will be ignored.
	 *
	 * The cache provider will also check dates on its own, in case expired
	 * entries are fetched before MongoDB's TTLMonitor pass can expire them.
	 *
	 * @see http://docs.mongodb.org/manual/tutorial/expire-data/
	 */
	const EXPIRATION_FIELD = 'e';

	/** @var Mongo */
	protected $mongo;
	/*
	 * The name of the collection to save the data
	 * @var string
	 */
	protected $collectionName = 'ZhCache';

	/**
	 * Constructor.
	 *
	 * @param Mongo $mongo The connection provider with mongo
	 */
	public function __construct(Mongo $mongo) {
		$this->mongo = $mongo;
	}

	/**
	 * Returns the name of the collection that is being saved to the cache
	 *
	 * @return string
	 */
	public function getCollectionName() {
		return $this->collectionName;
	}

	/**
	 * Sets the collection that will save the data cache
	 *
	 * @param $collectionName
	 */
	public function setCollectionName($collectionName) {
		$this->collectionName = $collectionName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($key, $value, $lifeTime = 0) {
		$this->mongo->update($this->getCollectionName(),
			array('_id' => $key),
			array('$set' => array(
				self::EXPIRATION_FIELD => ($lifeTime == 0 ? $lifeTime : new \MongoDB\BSON\UTCDateTime(time() + $lifeTime)),
				self::DATA_FIELD => new \MongoDB\BSON\Binary(serialize($value), \MongoDB\BSON\Binary::TYPE_GENERIC),
			)),
			true, false);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($key) {
        return $this->doFetch($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($key){
        $documents = $this->mongo->find($this->getCollectionName(), array('_id' => $key));
        $document = array_shift($documents);

        if(!$this->contains($key)){
            throw Exception::valueNotFound($key);
        }

        return unserialize($document[self::DATA_FIELD]->getData());
    }


	/**
     * {@inheritdoc}
	 */
    public function delete($key) {
        $this->mongo->remove($this->getCollectionName(), array('_id' => $key));
		return true;
	}

    /**
     * {@inheritdoc}
     */
    protected function doContains($key){
        $documents = $this->mongo->find($this->getCollectionName(), array('_id' => $key));
        $document = array_shift($documents);

        if ($document === null)
            return false;

		if ($this->isExpired($document)) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($key){
        return $this->doContains($key);
    }

	/**
	 * Check if the document is expired.
	 *
	 * @param array $document
	 *
	 * @return boolean
	 */
	private function isExpired(array $document) {
		return isset($document[self::EXPIRATION_FIELD]) &&
		$document[self::EXPIRATION_FIELD] instanceof \MongoDB\BSON\UTCDateTime &&
		intval($document[self::EXPIRATION_FIELD]->toDateTime()->format('U')) < time();
	}

	public function getStats() {}
}