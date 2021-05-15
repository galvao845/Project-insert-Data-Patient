<?php
namespace Zeedhi\Framework\Serializer\Strategy;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Context;

class Exclusion implements ExclusionStrategyInterface
{
    protected $properties = array();
    protected $className = array();

    /**
     * {@inheritDoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $navigatorContext)
    {
        return in_array($metadata->name, $this->className) ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $navigatorContext)
    {
        if (in_array($property->name, $this->properties) ||
            in_array($property->class . "::" . $property->name, $this->properties)
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function addExclusionClass($className)
    {
        $this->className[] = $className;
    }

    public function addExclusionProperty($propertyName)
    {
        $this->properties[] = $propertyName;
    }
} 