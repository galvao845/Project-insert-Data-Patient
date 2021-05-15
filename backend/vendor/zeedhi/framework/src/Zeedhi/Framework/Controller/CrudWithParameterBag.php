<?php
namespace Zeedhi\Framework\Controller;

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO;

abstract class CrudWithParameterBag extends Crud {

    /** @var ParameterBag */
    protected $parameterBag;
    /** @var array */
    protected $parameterBagColumns = array();

    public function __construct(Manager $dataSourceManager, ParameterBag $parameterBag) {
        parent::__construct($dataSourceManager);
        $this->parameterBag = $parameterBag;
    }

    public function find(DTO\Request\Filter $request, DTO\Response $response) {
        $conditions = array();
        $filterCriteria = $request->getFilterCriteria();
        foreach($filterCriteria->getConditions() as $condition) {
            $columnName = $condition['columnName'];
            if(in_array($columnName, $this->parameterBagColumns)) {
                $this->parameterBag->set($columnName, $condition['value']);
            } else {
                $conditions[] = $condition;
            }
        }

        $newRequest = new DTO\Request\Filter(
            new FilterCriteria(
                $filterCriteria->getDataSourceName(),
                $conditions,
                $filterCriteria->getPage(),
                $filterCriteria->getPageSize()
            ),
            $request->getMethod(),
            $request->getRoutePath(),
            $request->getUserId()
        );

        parent::find($newRequest, $response);
    }


}