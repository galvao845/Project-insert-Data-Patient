<?php
namespace Zeedhi\Framework\Serializer;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

/**
 * Class Serializer
 *
 * Class allows you to (de-)serialize data of any complexity
 *
 * @package Zeedhi\Framework\Serializer
 */
class Serializer {
	const JSON = 'json';
	const XML = 'xml';

	/** @var Serializer $serializer */
	protected $serializer;
	/** @var SerializerBuilder $serializerBuilder */
	protected $serializerBuilder;
	/** @var Strategy\Exclusion $exclusionStrategy */
	protected $exclusionStrategy;
	/** @var SerializationContext $serializationContext */
	protected $serializationContext;
	/** @var PropertyNamingStrategyInterface $namingStrategy */
	protected $namingStrategy;
	/** @var SubscribingHandlerInterface $handlers */
	protected $handlers = array();

	/**
	 * Constructor
	 *
	 * @param SerializationContext       $serializationContext
	 * @param SerializerBuilder          $serializerBuilder
	 * @param ExclusionStrategyInterface $exclusionStrategy
	 */
	public function __construct(SerializationContext $serializationContext, SerializerBuilder $serializerBuilder, ExclusionStrategyInterface $exclusionStrategy) {
		$this->serializationContext = $serializationContext;
		$this->serializerBuilder = $serializerBuilder;
		$this->exclusionStrategy = $exclusionStrategy;
		$this->serializationContext->addExclusionStrategy($exclusionStrategy);
	}

	/**
	 * Add handlers to allow you to change the serialization
	 * or deserialization process for a single type/format combination.
	 *
	 * @param SubscribingHandlerInterface $handler
	 */
	public function addHandle(SubscribingHandlerInterface $handler) {
		$this->handlers[] = $handler;
	}

	/**
	 * Configure an strategy for translate the property name to a serialized name that is displayed.
	 *
	 * @param PropertyNamingStrategyInterface $namingStrategy An instance of implementation of the strategy
	 */
	public function setNamingStrategy(PropertyNamingStrategyInterface $namingStrategy) {
		$this->namingStrategy = $namingStrategy;
	}

	/**
	 * Ensure is building the serializer with the appropriate settings
	 *
	 * @return \JMS\Serializer\Serializer
	 */
	protected function ensureIsBuilding() {
		if (!$this->serializer) {
			if ($this->namingStrategy) {
				$this->serializerBuilder->setPropertyNamingStrategy($this->namingStrategy);
			}
			if($this->handlers) {
				$handlers = $this->handlers;
				$this->serializerBuilder->configureHandlers(
					function (HandlerRegistry $registry) use ($handlers) {
						foreach ($handlers as $subscribingHandler) {
							$registry->registerSubscribingHandler($subscribingHandler);
						}
					}
				);
			}
			$this->serializer = $this->serializerBuilder->build();
		}
	}

	/**
	 * Serialize data to format passed by parameter
	 *
	 * @param mixed  $data The object or data to be serialized
	 * @param string $type The format type to be serialized
	 *
	 * @return mixed
	 */
	public function serialize($data, $type = self::JSON) {
		$this->ensureIsBuilding();
		return $this->serializer->serialize($data, $type, $this->serializationContext);
	}

	/**
	 * De-serialize data into object the type of className
	 *
	 * @param mixed  $data      The data to be serialized in the object
	 * @param string $className The class name that reference the object to be serialized
	 * @param string $type      The type of data
	 *
	 * @return mixed
	 *
	 * @todo review method
	 */
	public function deSerialize($data, $className, $type = self::JSON) {
		$this->ensureIsBuilding();
		return $this->serializer->deserialize($data, $className, $type);
	}

	/**
	 * Add property's whether should be skipped.
	 *
	 * @param string $propertyName The name of property should be skipped.
	 *
	 * @return $this
	 */
	public function addExclusionProperty($propertyName) {
		$this->exclusionStrategy->addExclusionProperty($propertyName);
		return $this;
	}

	/**
	 * Add class's whether should be skipped.
	 *
	 * @param string $className The name of class should be skipped.
	 *
	 * @return $this
	 */
	public function addExclusionClass($className) {
		$this->exclusionStrategy->addExclusionClass($className);
		return $this;
	}
}