<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\AfterFind;

use Zeedhi\Framework\DataSource\FilterCriteria;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function afterFind(FilterCriteria $filterCriteria, array $rows);

    public function notify(array $args) {
        $this->afterFind($args[0], $args[1]);
    }
}