<?php
namespace Zeedhi\Framework\Serializer\Strategy;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

class UpperCaseNaming implements PropertyNamingStrategyInterface
{
    private $separator;

    public function __construct($separator = '_')
    {
        $this->separator = $separator;
    }

    public function translateName(PropertyMetadata $property)
    {
        $name = preg_replace('/[A-Z]/', $this->separator . '\\0', $property->name);
        return strtoupper($name);
    }
}