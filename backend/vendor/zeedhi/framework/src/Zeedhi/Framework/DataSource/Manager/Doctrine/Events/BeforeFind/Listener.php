<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\BeforeFind;

use Zeedhi\Framework\DataSource\FilterCriteria;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function beforeFind(FilterCriteria $filterCriteria);

    public function notify(array $args) {
        $this->beforeFind($args[0]);
    }
}