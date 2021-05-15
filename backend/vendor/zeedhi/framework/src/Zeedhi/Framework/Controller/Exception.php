<?php
namespace Zeedhi\Framework\Controller;

/**
 * Class Exception
 *
 * Should group all variety of expected messages used in this Exception Class.
 *
 * @package Zeedhi\Framework\Controller
 */
class Exception extends \Exception{

    /**
     * Return a exception with the proper message when a method of a given controller doesn't exist.
     *
     * @param string $className  The Controller class name.
     * @param string $methodName The nonexistent action/method name.
     *
     * @return Exception
     */
    public static function methodDoestNotExist($className, $methodName) {
        return new self("Controller {$className} doesn't has method {$methodName}.");
    }

    /**
     * @param string $reportName
     * @return Exception
     */
    public static function reportNotMapped($reportName) {
        return new self("Mapping not found for report '{$reportName}'.");
    }

    /**
     * @return Exception
     */
    public static function missingReportNameField() {
        return new self("Missing report name in row.");
    }

    /**
     * @param string $fieldName
     * @param string $paramName
     * @return Exception
     */
    public static function missingFieldForParameter($fieldName, $paramName) {
        return new self("Missing field '{$fieldName}', used for parameter '{$paramName}', at given row.");
    }
} 