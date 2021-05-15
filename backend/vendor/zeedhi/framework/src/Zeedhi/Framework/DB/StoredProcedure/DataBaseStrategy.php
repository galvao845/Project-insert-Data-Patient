<?php
namespace Zeedhi\Framework\DB\StoredProcedure;

interface DataBaseStrategy {

    /**
     * @param StoredProcedure $procedure   The procedure to be executed.
     * @param array           $paramValues Param values to override values present in Param instance.
     *
     * @return array The output parameters indexed by name.
     */
    public function executeProcedure(StoredProcedure $procedure, array $paramValues = array());
}