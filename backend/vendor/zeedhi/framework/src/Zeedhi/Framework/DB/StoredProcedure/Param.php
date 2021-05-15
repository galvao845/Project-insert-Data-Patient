<?php
namespace Zeedhi\Framework\DB\StoredProcedure;

/**
 * Class Param
 *
 * @package Zeedhi\Framework\DB\Procedure
 */
class Param {

	const PARAM_TYPE_NULL = 0;
	const PARAM_TYPE_INT = 1;
	const PARAM_TYPE_STR = 2;
	const PARAM_TYPE_LOB = 3;
	const PARAM_TYPE_STMT = 4;
	const PARAM_TYPE_BOOL = 5;

	const PARAM_INPUT = 'I';
	const PARAM_OUTPUT = 'O';
	const PARAM_INPUT_OUTPUT = 'IO';

	/** @var string */
	protected $inOut;
	/** @var null|int */
	protected $length;
	/** @var string */
	protected $name;
	/** @var null|int */
	protected $type;
	/** @var null|mixed */
	protected $value;

	/**
	 * Constructor
	 *
	 * @param string $name   The name of the parameter
	 * @param string $inOut  The parameter is in, inout or out.
	 * @param null   $value  The value of the parameter
	 * @param null   $type   The type of the parameter
	 * @param null   $length The length the parameter
	 */
	public function __construct($name, $inOut, $value = null, $type = null, $length = null) {
		$this->name = $name;
		$this->inOut = $inOut;
		$this->type = $type;
		$this->length = $length;
		$this->value = $value;
	}

	/**
	 * Returns the value of the parameter
	 *
	 * @return null|mixed
	 */
	public function getValue() {
		switch($this->type) {
            case self::PARAM_TYPE_INT:
                return (int) $this->value;
                break;
            case self::PARAM_TYPE_BOOL:
                return (bool) $this->value;
                break;
            default:
                return $this->value;
                break;
        }
	}

	/**
	 * Returns size the parameter
	 *
	 * @return mixed
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * Returns the name the parameter
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the parameter formatted placeholder
	 *
	 * @return string
	 */
	public function getParamAlias() {
		return ":" . $this->name;
	}

	/**
	 * Returns the type the parameter
	 *
	 * @return null|int
	 */
	public function getType() {
		return $this->type ?: 0;
	}

	/**
	 * Returns true if the parameter is output
	 *
	 * @return bool
	 */
	public function isOutput() {
		return $this->inOut === self::PARAM_OUTPUT || $this->inOut === self::PARAM_INPUT_OUTPUT;
	}

}