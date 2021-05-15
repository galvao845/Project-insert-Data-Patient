<?php
namespace Zeedhi\Framework\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;

class QueryBuilder extends DoctrineQueryBuilder {


    /* @var array The query comments. */
    private $_comment = [];

    public function __construct(Connection $connection){
        parent::__construct($connection);
    }

    /**
     * Builds the stackTrace as an array of strings.
     *
     * @return array $stackTrace the humanreadble stack trace.
     */
    private function getStackTrace(){
        $stackTrace = '';
        foreach (debug_backtrace() as $key => $traceElement) {
            if(isset($traceElement['class']))
                $stackTrace .= $traceElement['class'];
            if(isset($traceElement['type']))
                $stackTrace .= $traceElement['type'];
            if(isset($traceElement['function']))
                $stackTrace .= $traceElement['function'].' ';
            if(isset($traceElement['file']))
                $stackTrace .= 'called by '.$traceElement['file'].' ';
            if(isset($traceElement['line']))
                $stackTrace .= 'at line '.$traceElement['line'].' ';
        }
        return $stackTrace;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(){
        $this->comment('Parameters', $this->getParameters());
        $this->comment('StackTrace', $this->getStackTrace());
        return parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getSQL(){
        $sql = '';
        if (count($this->_comment)) {
            $sql.= ' /* ';
            $sql.= \Zeedhi\Framework\Util\JSON::factoryObjectFromArray($this->_comment);
            $sql.= ' */ ';
        }
        return $sql.parent::getSQL();
    }

    /**
     * {@inheritdoc}
     */
    public function add($sqlPartName, $sqlPart, $append = false){
        if($sqlPartName == 'comment'){
            foreach ($sqlPart as $key => $value) {
                $this->_comment[$key] = $value;
            }
            return $this;
        } else {
            return parent::add($sqlPartName, $sqlPart, $append);
        }
    }

    /**
     * Adds a comment to the query.
     *
     * @param array The array of comments to add.
     *
     * @return QueryBuilder Return the QueryBuilder object.
     */
    public function comment($key, $value){
        $comment[$key] = $value;
        return $this->add('comment', $comment);
    }

}