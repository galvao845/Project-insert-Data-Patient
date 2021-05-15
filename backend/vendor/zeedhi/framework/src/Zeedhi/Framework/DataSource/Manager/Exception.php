<?php
namespace Zeedhi\Framework\DataSource\Manager;

class Exception extends \Zeedhi\Framework\DataSource\Exception {

    public static function columnNotPresentInResultSet($columnName, $dataSourceName, \Exception $previous = null) {
        return new static("Column {$columnName} was not found in result set of data source {$dataSourceName}.", 0, $previous);
    }

    public static function errorExecutingQuery(\Exception $previous) {
        return new static($previous->getMessage(), $previous->getCode(), $previous);
    }

    public static function errorLoadingMetadataForClass($oldMessage, $className) {
        return new static("An error occur while loading metadata for class {$className}: ".$oldMessage);
    }
}