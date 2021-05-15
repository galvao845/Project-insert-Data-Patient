<?php
namespace Zeedhi\Framework\DataSource\Manager\DataColumnToColumnConversion;

class Exception extends \Zeedhi\Framework\DataSource\Manager\Exception {

    public static function dataColumnDoesNotExistForGivenColumn($column, $dataSource){
        return new static("Data column for column {$column} does not exist in data source {$dataSource}");
    }

    public static function dataColumnMissingInRow($dataColumnName, $dataSourceName) {
        return new static("Data column {$dataColumnName} was not found in given row for data source{$dataSourceName}.");
    }
}