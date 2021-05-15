<?php
namespace Zeedhi\Framework\Session;

use Zeedhi\Framework\Session\Attribute\AttributeInterface;
use Zeedhi\Framework\Session\Attribute\SimpleAttribute;
use Zeedhi\Framework\Session\Storage\NativeSession;
use Zeedhi\Framework\Session\Storage\SessionStorageInterface;

/**
 * Class Session
 *
 * This class implements the manager of session
 *
 * @package Zeedhi\Framework\Session
 */
class Session implements SessionInterface {
	/**
	 * Storage driver.
	 *
	 * @var SessionStorageInterface
	 */
	protected $storage;
	/**
	 * @var string
	 */
	private $attributeName;

	/**
	 * Constructor.
	 *
	 * @param SessionStorageInterface $storage    A SessionStorageInterface instance.
	 * @param AttributeInterface      $attributes An AttributeBagInterface instance, (defaults null for default
	 *                                            AttributeBag)
	 */
	public function __construct(SessionStorageInterface $storage = null, AttributeInterface $attributes = null) {
		$this->storage = $storage ?: new NativeSession();
		$attributes = $attributes ?: new SimpleAttribute();
		$this->attributeName = $attributes->getName();
		$this->registerBag($attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function start() {
		return $this->storage->start();
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy() {
		if (!$this->isStarted()) {
			$this->storage->start();
		}

		return $this->storage->destroy();
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($name) {
		return $this->getStorageBag()->has($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($name, $default = null) {
		return $this->getStorageBag()->get($name, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($name, $value) {
		$this->getStorageBag()->set($name, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function all() {
		return $this->getStorageBag()->all();
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace(array $attributes) {
		$this->getStorageBag()->replace($attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($name) {
		return $this->getStorageBag()->remove($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear() {
		$this->getStorageBag()->clear();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted() {
		return $this->storage->isStarted();
	}

	/**
	 * {@inheritdoc}
	 */
	public function invalidate($lifetime = null) {
		$this->storage->clear();
		return $this->migrate(true, $lifetime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function migrate($destroy = false, $lifetime = null) {
		return $this->storage->regenerate($destroy, $lifetime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$this->storage->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		return $this->storage->getId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setId($id) {
		$this->storage->setId($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return $this->storage->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name) {
		$this->storage->setName($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadataBag() {
		return $this->storage->getMetadataBag();
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerBag(\Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag) {
		$this->storage->registerBag($bag);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBag($name) {
		return $this->storage->getBag($name);
	}

	/**
	 * @return AttributeInterface
	 */
	protected function getStorageBag() {
		return $this->getBag($this->attributeName);
	}
}