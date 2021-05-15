<?php
namespace Zeedhi\Framework\Log;

class Console extends AbstractLogger{

    protected function writeLine($message) {
        echo $message, "\n";
    }
}