<?php
namespace Zeedhi\Framework\DataSource\Manager\Remote;

class Exception extends \Exception {

    public static function dataSetNotFound($dataSet) {
        return new static('DataSet "'.$dataSet.'" was not found on remote response.');
    }

    public static function errorOnRemoteServer($error) {
        return new static('Error on remote server: "'.$error.'".');
    }

}