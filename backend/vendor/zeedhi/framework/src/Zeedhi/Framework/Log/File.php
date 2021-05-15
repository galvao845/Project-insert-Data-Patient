<?php
namespace Zeedhi\Framework\Log;

class File extends AbstractLogger {

    /** @var resource */
    protected $fileHandle;
    /** @var string */
    protected $fileName;

    /**
     * Construct...
     *
     * @param string $fileName Name of the file that will be store the log messages.
     */
    public function __construct($fileName) {
        $this->fileName = $fileName;
    }

    protected function openFile() {
        $this->fileHandle = fopen($this->fileName, 'a');
        if ($this->fileHandle === false) throw new \Exception("Can not open file {$this->fileName}.");
    }

    protected function isFileOpen() {
        return $this->fileHandle != false;
    }

    protected function ensureFileIsOpen() {
        if (!$this->isFileOpen()) $this->openFile();
    }

    protected function writeLine($message) {
        $this->ensureFileIsOpen();
        fprintf($this->fileHandle, str_replace("%", "%%", $message."\n"));
    }
}