<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\BeforeDeleteRow;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function beforeDeleteRow(array $row, $rowKey);

    public function notify(array $args) {
        $this->beforeDeleteRow($args[0], $args[1]);
    }
} 