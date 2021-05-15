<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\AfterDeleteRow;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function afterDeleteRow(array $row, $entity, $rowKey);

    public function notify(array $args) {
        $this->afterDeleteRow($args[0], $args[1], $args[2]);
    }
} 