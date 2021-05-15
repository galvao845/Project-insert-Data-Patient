<?php
namespace Zeedhi\Framework\DataSource;

class Exception extends \Exception{

    public static function invalidFileName($fileName) {
        return new static("Config file for data source {$fileName} not found.");
    }

    public static function invalidDataSourceName($dataSourceName) {
        return new static("Data source {$dataSourceName} not found in config file.");
    }

    public static function pkColumnNotFoundInColumnList($pkColumnName, $dataSourceName) {
        return new static("Primary key column {$pkColumnName} not present in data source {$dataSourceName} column list.");
    }

    public static function sequentialColumnMustBeAPkColumn($dataSource, $sequentialColumn) {
        return new static("Sequential column {$sequentialColumn} was not found in pk columns of data source {$dataSource}.");
    }

    public static function dataColumnDoesNotExist($dataColumn, $dataSource){
        return new static("Data column {$dataColumn} does not exist in data source {$dataSource}");
    }
}