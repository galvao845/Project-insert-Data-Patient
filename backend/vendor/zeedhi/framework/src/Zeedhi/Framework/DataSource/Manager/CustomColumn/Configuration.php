<?php
namespace Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DataSource;

class Configuration extends DataSource\Configuration {

    /** @var string[] List of customColumns */
    protected $customColumns = array();

    /**
     * factoryFromJsonData
     * Create Configuration from json data
     *
     * @param  $dataSourceConfig JSON containing the configuration data
     * @param  $dataSourceName   Name of the dataSource
     *
     * @return static
     *
     * @throws Exception
     */
    public static function factoryFromJsonData($dataSourceConfig, $dataSourceName) {
        $instance = parent::factoryFromJsonData($dataSourceConfig, $dataSourceName);

        if (isset($dataSourceConfig['customColumns'])) {
            $instance->setCustomColumns($dataSourceConfig['customColumns']);
        }

        return $instance;
    }

    /**
     * getCustomColumns
     *
     * @return string[] Array of strings containing the customColumns
     */
    public function getCustomColumns() {
        return $this->customColumns;
    }

    /**
     * setCustomColumns
     *
     * @param string[] $customColumns Array of strings containing the customColumns
     */
    public function setCustomColumns(array $customColumns) {
        $this->customColumns = $customColumns;
    }

}