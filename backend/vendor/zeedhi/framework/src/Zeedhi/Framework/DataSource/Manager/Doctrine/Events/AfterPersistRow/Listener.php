<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\AfterPersistRow;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function afterPersistRow(array $row, $entity, $rowKey);

    public function notify(array $args) {
        $this->afterPersistRow($args[0], $args[1], $args[2]);
    }
} 