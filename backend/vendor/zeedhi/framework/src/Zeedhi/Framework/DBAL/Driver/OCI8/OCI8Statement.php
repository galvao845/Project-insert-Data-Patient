<?php
namespace Zeedhi\Framework\DBAL\Driver\OCI8;

class OCI8Statement extends \Doctrine\DBAL\Driver\OCI8\OCI8Statement {

    protected $fieldTypes = null;
    protected $fieldNames = null;
    protected $fieldIdByName = null;

    protected function loadResultSetMetaData() {
        if ($this->fieldTypes === null) {
            $this->fieldNames = $this->fieldIdByName = $this->fieldTypes = array();
            $ociFieldNum = oci_num_fields($this->_sth);
            for ($i = 1; $i <= $ociFieldNum; $i++) {
                $fieldName = oci_field_name($this->_sth, $i);
                $this->fieldTypes[$i] = oci_field_type($this->_sth, $i);
                $this->fieldNames[$i] = $fieldName;
                $this->fieldIdByName[$fieldName] = $i;
            }
        }
    }

    protected function normalizeFloatString($value) {
        // This is know due to OracleSessionInit.
        list($thousandsSeparator, $decimalSeparator) = array(',', '.');
        // Remove thousands delimiter char and replace decimal dots.
        return str_replace($decimalSeparator, ".", str_replace($thousandsSeparator, "", $value));
    }

    protected function normalizeValueByType($value, $type) {
        if ($type === 'NUMBER' && $value !== null) {
            $value = floatval($this->normalizeFloatString($value));
        }

        return $value;
    }

    protected function inferTypes($result, $fetchMode) {
        $this->loadResultSetMetaData();
        switch ($fetchMode ?: $this->_defaultFetchMode) {
            case \PDO::FETCH_BOTH:
                foreach($result as $key => $value) {
                    $key = !is_numeric($key) ? $this->fieldIdByName[$key] : $key+1;
                    $value = $this->normalizeValueByType($value, $this->fieldTypes[$key]);
                }
                break;
            case \PDO::FETCH_ASSOC:
                foreach($result as $key => $value) {
                    $key = $this->fieldIdByName[$key];
                    $value = $this->normalizeValueByType($value, $this->fieldTypes[$key]);
                }
                break;
            case \PDO::FETCH_NUM:
            case \PDO::FETCH_COLUMN:
                
                if(!is_array($result)){
                    return [$result];
                }
                
                foreach($result as $key => $value) {
                    $value = $this->normalizeValueByType($value, $this->fieldTypes[$key+1]);
                }
                break;
        }

        return $result;
    }

    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0) {
        $result = parent::fetch($fetchMode, $cursorOrientation, $cursorOffset);
        return $result ? $this->inferTypes($result, $fetchMode) : $result;
    }

    protected function normalizeFetchAllResultSet($fetchMode, $rows) {
        $fetchMode = $fetchMode ?: $this->_defaultFetchMode;
        //The OCI_BOTH mode is normalized by fetch method, even if called by fetch all.
        if (self::$fetchModeMap[$fetchMode] !== OCI_BOTH) {
            foreach ($rows as &$row) {
                $row = $this->inferTypes($row, $fetchMode);
            }
        }

        return $rows;
    }

    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null) {
        $rows = parent::fetchAll($fetchMode, $fetchArgument, $ctorArgs);
        return $this->normalizeFetchAllResultSet($fetchMode, $rows);
    }

    public function bindParam($column, &$variable, $type = null, $length = null) {
        $column = isset($this->_paramMap[$column]) ? $this->_paramMap[$column] : $column;

        if ($type == \PDO::PARAM_LOB) {
            $lob = oci_new_descriptor($this->_dbh, OCI_D_LOB);
            $lob->writeTemporary($variable, OCI_TEMP_BLOB);

            return oci_bind_by_name($this->_sth, $column, $lob, -1, OCI_B_BLOB);
        } elseif ($type === SQLT_LBI){
            return oci_bind_by_name($this->_sth, $column, $variable, strlen($variable), $type);
        } elseif ($length !== null) {
            return oci_bind_by_name($this->_sth, $column, $variable, $length);
        }

        return oci_bind_by_name($this->_sth, $column, $variable);
    }
}