<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\BeforePersistRow;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function beforePersistRow(array $row, $rowKey);

    public function notify(array $args) {
        $this->beforePersistRow($args[0], $args[1]);
    }


}