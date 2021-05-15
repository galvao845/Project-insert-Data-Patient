<?php
namespace Zeedhi\Framework\DataSource;
/**
 * Class FilterCriteria
 *
 * Contains all information that a Manager will need to retrieve the proper DataSet in findBy method.
 *
 * @package Zeedhi\Framework\DataSource
 */
class FilterCriteria implements AssociatedWithDataSource {

    const ORDER_ASC  = 'ASC';
    const ORDER_DESC = 'DESC';
    const ORDER_BY   = 'ORDER_BY';
    const GROUP_BY   = 'GROUP_BY';

    /** The list of operators accepted */
    const EQ          = '=';
    const NEQ         = '<>';
    const LT          = '<';
    const LTE         = '<=';
    const GT          = '>';
    const GTE         = '>=';
    const IS          = '='; // no difference with EQ
    const IN          = 'IN';
    const NOT_IN      = 'NOT_IN';
    const LIKE        = 'LIKE';
    const LIKE_I      = 'LIKE_I';
    const NOT_LIKE    = 'NOT_LIKE';
    const IS_NULL     = 'IS_NULL';
    const IS_NOT_NULL = 'IS_NOT_NULL';
    const BETWEEN     = 'BETWEEN';
    const NOT_BETWEEN = 'NOT_BETWEEN';
    /**
     * Search for given text in all columns. Used with a collection of columnNames.
     * The columns must be a * to consider all column or column name delimited by | (pipe).
     */
    const LIKE_ALL      = 'LIKE_ALL';
    /* Add description */
    const MAPPED_LIKE_ALL      = 'MAPPED_LIKE_ALL';

    /** @var string The name of the dataSourceName that will be filtered. */
    protected $dataSourceName;

    /**
     * @var array of arrays(
     *  "columnName" => ,
     *  "operator"   => ,
     *  "value"      =>
     * )
     *
     * The list of conditions to be matched by the Manager.
     */
    protected $conditions;

    /** @var int The page number, if this value is null the manager must not paginate. */
    protected $page;
    /** @var int The page size. Default value is 300. THIS IS SPARTAAA4A@A!!1! */
    protected $pageSize;
    /** @var string Where clause to be applied in DataSourceManager. */
    protected $whereClause;
    /** @var array */
    protected $whereClauseParams = array();
    /** @var array */
    protected $orderBy = array();
    /** @var array */
    protected $groupBy = array();

    /**
     * Constructor ..
     *
     * @param string $dataSourceName The name of the dataSource that will be filtered.
     * @param array $conditions The list of conditions to be matched by the Manager. See $conditions doc for details.
     * @param int $page The page number, if this value is null the manager must not paginate. OPTIONAL.
     * @param int $pageSize The page size. Default value is 300.
     */
    function __construct($dataSourceName, array $conditions = array(), $page = null, $pageSize = 300) {
        $this->dataSourceName = $dataSourceName;
        $this->conditions = $conditions;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    /**
     * Set the page number. If null the Manager must not paginate.
     *
     * @param int $page The page number.
     *
     * @return void
     */
    public function setPage($page) {
        $this->page = $page;
    }

    /**
     * The page size.
     *
     * @param int $pageSize The page size.
     *
     * @return void
     */
    public function setPageSize($pageSize) {
        $this->pageSize = $pageSize;
    }

    /**
     * Add a condition. It can be used in two forms.
     * The simple one, less verbosity.
     *      $filterCriteria->addCondition($columnName, $value);
     * The other one, for more reader friendly:
     *      $filterCriteria->addCondition($columnName, $operator, $value);
     *
     * @param string $columnName          The column name to be used in condition.
     * @param mixed  $value               The value of given column, operator in second form.
     * @param mixed  $valueIfOperatorUsed The value of column, only used in second form. If this value to null,
     *                                    means that will use the first form.
     *
     * @return void
     */
    public function addCondition($columnName, $value, $valueIfOperatorUsed = 'ZeedhiIsNullValue') {
        if ($valueIfOperatorUsed === 'ZeedhiIsNullValue') {
            $operator = "=";
        } else {
            $operator = $value;
            $value = $valueIfOperatorUsed;
        }

        $this->conditions[] = array(
            "columnName" => $columnName,
            "value"      => $value,
            "operator"   => $operator
        );
    }

    /**
     * Return the existent condition.
     *
     * @return array of arrays(
     *  "columnName" => ,
     *  "operator"   => ,
     *  "value"      =>
     * )
     */
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * Return the page number.
     *
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Return the table name.
     *
     * @return string
     */
    public function getDataSourceName() {
        return $this->dataSourceName;
    }

    /**
     * Set the dataSource name of this filter.
     *
     * @param string $dataSourceName The name of dataSource
     */
    public function setDataSourceName($dataSourceName) {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * Return the page size.
     *
     * @return int
     */
    public function getPageSize() {
        return $this->pageSize;
    }

    /**
     * Return true if this need to be paginated. Verify if page (pageNumber) value to null.
     *
     * @return bool
     */
    public function isPaginated() {
        return $this->page !== null;
    }

    /**
     * Find the position of the first result, according to pageNumber and pageSize.
     *
     * @return int
     */
    public function getFirstResult() {
        return ($this->page - 1) * $this->pageSize;
    }

    /**
     * Return true if it has a where clause.
     *
     * @return bool
     */
    public function hasWhereClause() {
        return $this->whereClause != null;
    }

    /**
     * Set a where clause to be applied to data source manager.
     *
     * @param string $whereClause The string used for where clause.
     * @param array  $params      Optional. Parameters used by where clause. Indexed by param name.
     *
     * @return void
     */
    public function setWhereClause($whereClause, $params = array()) {
        $this->whereClause = $whereClause;
        $this->whereClauseParams = $params;
    }

    /**
     * @return array
     */
    public function getWhereClauseParams() {
        return $this->whereClauseParams;
    }

    /**
     * Get a where clause to be applied to data source manager.
     *
     * @return string
     */
    public function getWhereClause() {
        return $this->whereClause;
    }

    /**
     * Add an order to be applied to data source manager.
     *
     * @param string $columnName The column to be ordered.
     * @param string  $direction Optional. Direction to be ordered.
     *
     * @return void
     */
    public function addOrderBy($columnName, $direction = FilterCriteria::ORDER_ASC) {
        $this->orderBy[$columnName] = $direction;
    }

    /**
     * Get an order to be applied to data source manager.
     *
     * @return array
     */
    public function getOrderBy() {
        return $this->orderBy;
    }

    /**
     * Add a group to be applied to data source manager.
     *
     * @param mixed $columnName The column to be ordered.
     *
     * @return void
     */
    public function addGroupBy($columnName) {
        $this->groupBy[] = $columnName;
    }

    /**
     * Get a group to be applied to data source manager.
     *
     * @return array
     */
    public function getGroupBy() {
        return $this->groupBy;
    }
}