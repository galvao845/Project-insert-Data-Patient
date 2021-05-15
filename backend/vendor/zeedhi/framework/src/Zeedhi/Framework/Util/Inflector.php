<?php
namespace Zeedhi\Framework\Util;

interface Inflector {

    /**
     * Convert 'TABLE_NAME' into 'TableName'.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function classify($tableName);

}