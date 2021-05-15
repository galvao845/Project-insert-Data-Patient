<?php
namespace Zeedhi\Framework\Routing\Parsers;

use Exception;

class JSONFormatException extends Exception{

    /**
     * Alert that a configuration routes file contains a invalid JSON
     *
     * @param string $filePath Configuration routes file path
     * @param string $error    Error that ocurred when decoding the JSON file contents
     * @return JSONFormatException
     */
    public static function invalidJSON($filePath, $error) {
        return new static('Invalid JSON on router file "'. $filePath . '": ' . $error);
    }

}