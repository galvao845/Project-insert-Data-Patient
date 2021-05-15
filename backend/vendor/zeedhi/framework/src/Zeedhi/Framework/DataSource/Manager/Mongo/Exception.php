<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo;

class Exception extends \Zeedhi\Framework\DataSource\Manager\Exception {

    public static function missingInternalCollectionField($fieldName) {
        return new static("Missing internal collection field '{$fieldName}'.");
    }

}