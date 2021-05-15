<?php
namespace Zeedhi\Framework\Events;

interface Listener {

    public function notify(array $args);
}