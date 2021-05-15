<?php
namespace Zeedhi\Framework\Log;

class Memory extends AbstractLogger{

    protected $logs = "";

    protected function writeLine($message) {
        $this->logs .= $message."\n";
    }

    public function getLog() {
        return $this->logs;
    }
} 