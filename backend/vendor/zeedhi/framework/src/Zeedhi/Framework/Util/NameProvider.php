<?php
namespace Zeedhi\Framework\Util;

use Zeedhi\Framework\DataSource\Configuration;

class NameProvider implements \Zeedhi\Framework\DataSource\Manager\Doctrine\NameProvider {

    protected $namespacePrefix;
    protected $subModelsByTables;
    protected $dataSourceDirectory;
    protected $useEntitiesDirectory;
    /** @var  Inflector */
    protected $customInflector;

    public function __construct($dataSourceDirectory, $namespacePrefix, array $subModelsByTables = array(), $useEntitiesDirectory = true, $customInflector = null) {
        $this->dataSourceDirectory = $dataSourceDirectory;
        $this->namespacePrefix = $namespacePrefix;
        $this->subModelsByTables = $subModelsByTables;
        $this->useEntitiesDirectory = $useEntitiesDirectory;
        $this->customInflector = $customInflector ?: new DefaultInflector();
    }

    /**
     * @param $tableName
     *
     * @return string
     */
    protected function classify($tableName) {
        return $this->customInflector->classify($tableName);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName($tableName) {
        $className = $this->namespacePrefix ? "\\" . $this->namespacePrefix : "";
        if (isset($this->subModelsByTables[$tableName])) {
            $className .= "\\" . $this->classify($this->subModelsByTables[$tableName]);
        }

        if ($this->useEntitiesDirectory) {
            $className .= "\\Entities";
        }

        $className .= "\\" . $this->classify($tableName);
        return $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceByName($dataSourceName) {
        return Configuration::factoryFromFileLocation($this->dataSourceDirectory, $dataSourceName);
    }

    public static function factoryDefault() {
        $ds = DIRECTORY_SEPARATOR;
        $dataSourceDirectory = '..' . $ds . 'gen' . $ds . 'datasources';
        return new static($dataSourceDirectory, "Model", array(), false);
    }

    public static function factoryFromEntitiesJSON($fileLocation, $dataSourceDir) {
        $entitiesJson = json_decode(file_get_contents($fileLocation), true);
        $inflector = isset($entitiesJson['inflectorClassName']) ? new $entitiesJson['inflectorClassName'] :  null;
        return new static($dataSourceDir, $entitiesJson['namespace'], $entitiesJson['subModelsByTables'], true, $inflector);
    }
}
