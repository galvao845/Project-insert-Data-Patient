<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo;

/**
 * Class Configuration
 *
 * @package Zeedhi\Framework\DataSource
 */
class Configuration extends \Zeedhi\Framework\DataSource\Configuration {

    /** @var static[]  */
    protected $internalCollections = array();
    /** @var array  */
    protected $internalCollectionsOptions = array();
    /** @var static  */
    protected $wrapperCollection;
    /** @var array */
    protected $typesMapping = array();
    /** @var static[] */
    protected static $collectionsMap = array();
    /** @var string */
    protected static $dirLocation;

    protected function setWrapperCollection($wrapperCollection) {
        $this->wrapperCollection = self::factoryFromFileLocation(self::$dirLocation, $wrapperCollection);
    }

    protected function setInternalCollections(array $internalCollections) {
        foreach ($internalCollections as $name => $collection) {
            $this->internalCollections[$name] = self::factoryFromFileLocation(self::$dirLocation, $name);
            $this->internalCollectionsOptions[$name] = $collection;
        }
    }

    protected static function factoryFromJsonData($dataSourceConfig, $dataSourceName) {
        /** @var Configuration $instance */
        $instance = parent::factoryFromJsonData($dataSourceConfig, $dataSourceName);
        self::$collectionsMap[$dataSourceName] = $instance;

        if (isset($dataSourceConfig['typesMapping'])) {
            $instance->typesMapping = $dataSourceConfig['typesMapping'];
        }

        if (isset($dataSourceConfig['internalCollections'])) {
            $instance->setInternalCollections($dataSourceConfig['internalCollections']);
        }

        if (isset($dataSourceConfig['wrapperCollection'])) {
            $instance->setWrapperCollection($dataSourceConfig['wrapperCollection']);
        }

        return $instance;
    }

    /**
     * @param string $dirLocation
     * @param string $dataSourceName
     * @return static
     */
    public static function factoryFromFileLocation($dirLocation, $dataSourceName) {
        self::$dirLocation = $dirLocation;
        if (!array_key_exists($dataSourceName, self::$collectionsMap)) {
            parent::factoryFromFileLocation($dirLocation, $dataSourceName);
        }

        return self::$collectionsMap[$dataSourceName];
    }

    public function getInternalCollectionOptions($name) {
        return $this->internalCollectionsOptions[$name];
    }

    public function getInternalCollections() {
        return $this->internalCollections;
    }

    /**
     * @param $field
     * @return mixed
     * @throws Exception
     */
    public function getInternalCollectionForField($field) {
        foreach ($this->internalCollectionsOptions as $key => $value) {
            if ($value['fieldName'] === $field) {
                return $this->internalCollections[$key];
            }
        }

        throw Exception::missingInternalCollectionField($field);
    }

    public function getWrapperCollection() {
        return $this->wrapperCollection;
    }

    public function getTypesMapping() {
        return $this->typesMapping;
    }

}