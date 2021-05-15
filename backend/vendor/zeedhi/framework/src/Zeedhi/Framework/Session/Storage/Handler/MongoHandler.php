<?php
namespace Zeedhi\Framework\Session\Storage\Handler;

use Zeedhi\Framework\DB\Mongo\Mongo;

/**
 * Class MongoHandler
 *
 * @package Zeedhi\Framework\Session\Storage\Handler
 */
class MongoHandler implements \SessionHandlerInterface {

	/**
	 * @var Mongo
	 */
	protected $mongo;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * List of available options:
	 *  * collection: The name of the collection [required]
	 *  * id_field: The field name for storing the session id [default: _id]
	 *  * data_field: The field name for storing the session data [default: data]
	 *  * time_field: The field name for storing the timestamp [default: time]
	 *
	 * @param Mongo $mongo   A MongoClient or Mongo instance
	 * @param array $options An associative array of field options
	 *
	 */
	public function __construct(Mongo $mongo, array $options) {
		$this->mongo = $mongo;

		if (!isset($options['collection'])) {
			throw new \InvalidArgumentException('You must provide the "collection" option for MongoHandler');
		}

		$this->options = array_merge(array(
			'id_field' => '_id',
			'data_field' => 'data',
			'time_field' => 'time',
			'expiry_field' => false,
		), $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function open($savePath, $sessionName) {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy($sessionId) {
		$this->getMongo()->remove($this->options['collection'], array(
			$this->options['id_field'] => $sessionId,
		));

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc($maxlifetime) {
		/* Note: MongoDB 2.2+ supports TTL collections, which may be used in
		 * place of this method by indexing the "time_field" field with an
		 * "expireAfterSeconds" option. Regardless of whether TTL collections
		 * are used, consider indexing this field to make the remove query more
		 * efficient.
		 *
		 * See: http://docs.mongodb.org/manual/tutorial/expire-data/
		 */
		if (false !== $this->options['expiry_field']) {
			return true;
		}
		$time = new \MongoDB\BSON\UTCDateTime(time() - $maxlifetime);

		$this->getMongo()->remove($this->options['collection'], array(
			$this->options['time_field'] => array('$lt' => $time),
		));

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($sessionId, $data) {
		$fields = array(
			$this->options['data_field'] => new \MongoDB\BSON\Binary($data, \MongoDB\BSON\Binary::TYPE_GENERIC),
			$this->options['time_field'] => new \MongoDB\BSON\UTCDateTime(),
		);

		/* Note: As discussed in the gc method of this class. You can utilise
		 * TTL collections in MongoDB 2.2+
		 * We are setting the "expiry_field" as part of the write operation here
		 * You will need to create the index on your collection that expires documents
		 * at that time
		 * e.g.
		 * db.MySessionCollection.ensureIndex( { "expireAt": 1 }, { expireAfterSeconds: 0 } )
		 */
		if (false !== $this->options['expiry_field']) {
			$expiry = new \MongoDB\BSON\UTCDateTime(time() + (int)ini_get('session.gc_maxlifetime'));
			$fields[$this->options['expiry_field']] = $expiry;
		}

		$this->getMongo()->update($this->options['collection'],
			array($this->options['id_field'] => $sessionId),
			array('$set' => $fields),
			true, false
		);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($sessionId) {
		$documents = $this->getMongo()->find($this->options['collection'], array(
			$this->options['id_field'] => $sessionId,
		));
		$document = array_shift($documents);
		return null === $document ? '' : $document[$this->options['data_field']]->getData();
	}

	/**
	 * Return a Mongo instance
	 *
	 * @return Mongo
	 */
	protected function getMongo() {
		return $this->mongo;
	}

}