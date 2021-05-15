<?php
namespace Zeedhi\Framework\Util;

/**
 * Zeedhi JSON services.
 */
class JSON {
    /**
     * Convert any value to a JSON string.
     *
     * @param mixed $obj
     *
     * @return mixed
     */
    public static function factoryObjectFromArray($obj) {
        switch(gettype($obj)) {
            case 'array':
                if (!$obj) {
                    return '[]';
                } elseif (gettype(key($obj)) !== "integer") {
                    $count = count($obj);
                    $json = '{';
                    foreach ($obj as $key => $value) {
                        $key = $key === '?' ? 'PARAM' : str_replace(':', '', $key);
                        $json .= "\"$key\": " . self::factoryObjectFromArray($value);
                        $count--;
                        if ($count > 0) $json .= ',';
                    }
                    $obj = $json . '}';
                } else {
                    $count = count($obj);
                    $json = '[';
                    foreach ($obj as $value) {
                        $json .= self::factoryObjectFromArray($value);
                        $count--;
                        if ($count > 0) $json .= ' ,';
                    }
                    $obj = $json . ']';
                }
                break;
            case 'boolean':
                $obj = $obj ? 'true' : 'false';
                break;
            case 'object':
                $obj = get_class($obj);
                break;
            case 'resource':
                $obj = '"resource"';
                break;
            case 'NULL':
                $obj = 'null';
                break;
            case 'string':
                $obj = "\"$obj\"";
        }
        return $obj;
    }
}