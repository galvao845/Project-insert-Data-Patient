<?php
namespace Zeedhi\Framework\DataSource\Manager\IdProvider;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\DataSet;


class ManagerImpl implements Manager {

    /** @var Manager */
    protected $manager;
    /** @var \Zeedhi\Framework\DataSource\Manager\IdProvider\IdProvider */
    protected $idProvider;
    /** @var Manager\Doctrine\NameProvider */
    protected $nameProvider;
    /** @var string */
    protected $dataSourceName;
    /** @var Configuration */
    protected $dataSourceConfig;

    const ALL_DATA = "__ALL";

    public function __construct(Manager\Doctrine\NameProvider $nameProvider, Manager $manager, IdProvider $idProvider){
        $this->manager = $manager;
        $this->idProvider = $idProvider;
        $this->nameProvider = $nameProvider;
    }

    public function persist(DataSet $dataSet){
        $this->dataSourceName = $dataSet->getDataSourceName();
        $this->dataSourceConfig = $this->nameProvider->getDataSourceByName($this->dataSourceName);
        $sequenceName = $this->dataSourceConfig->getSequentialColumn();

        foreach($dataSet->getRows() as $row){
            if ($row['__is_new']) {
                $row[$sequenceName] = $this->idProvider->getNextId();
            }
        }

        return $this->manager->persist($dataSet);
    }


    public function delete(DataSet $dataSet){
        return $this->manager->delete($dataSet);
    }


    public function findBy(FilterCriteria $filterCriteria){
        return $this->manager->findBy($filterCriteria);
    }

        /**
	 * Return a populated dataSet.
	 *
	 * Verify if the dataSet contains a ALL_DATA's flag and populate it.
	 *
	 * @param DataSet $dataSet
	 *
	 * @return DataSet $dataSet
	 */
	public function populateDataSet(DataSet $dataSet){
        $rows = $dataSet->getRows();
        $dataSourceName = $dataSet->getDataSourceName();
		foreach($rows[0] as $column => $value){
            if(is_array($value) && isset($value[self::ALL_DATA])){
                if(isset($data)){
                    $rows[0][$column] = $data;
                } else {
                    $filterCriteria = $this->buildAllDataFilter($rows, $value, $column, $dataSourceName);
                    $data = $rows[0][$column] = $this->findBy($filterCriteria)->getRows();
                }
            }
		}
		return new DataSet($dataSourceName, $rows);
    }

    /**
     * Return a filterCriteria.
     *
     * Build a filterCriteria based on dataSourceFilter.
     *
     * @param $rows
     * @param $value
     * @param $column
     *
     * @return FilterCriteria $filterCriteria
     */
    public function buildAllDataFilter($rows, $value, $column, $dataSourceName){
        $filterCriteria = new FilterCriteria($dataSourceName);
        if(isset($rows[0][$column . '_EXCEPT']) && !empty($rows[0][$column . '_EXCEPT'])){
            $exceptionFilter = array();
            foreach($rows[0][$column . '_EXCEPT'] as $exceptRow){
                $exceptionFilter[] = $exceptRow;
            }
            $filterCriteria->addCondition($column, "NOT_IN", $exceptionFilter);
        }
        if(!empty($value[self::ALL_DATA])){
            foreach($value[self::ALL_DATA] as $filter){
                $filterCriteria->addCondition($filter["name"], $filter["operator"], $filter["value"]);
            }
        }
        return $filterCriteria;
    }


}