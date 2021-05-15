<?php
namespace Zeedhi\Framework\Session\Attribute;

use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;

/**
 * Class NamespacedAttribute
 *
 * This class provides structured storage of session attributes using
 * a name spacing character in the key.
 *
 * @package Zeedhi\Framework\Session\Attribute
 */
class NamespacedAttribute extends NamespacedAttributeBag implements AttributeInterface {

}