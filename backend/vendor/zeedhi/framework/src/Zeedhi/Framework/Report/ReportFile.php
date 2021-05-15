<?php
namespace Zeedhi\Framework\Report;

/**
 * Class ReportFile
 */
class ReportFile extends \Zeedhi\Framework\File\File {

	protected $content;

	public function getContent() {
		return isset($this->content) ? $this->content : $this->openFile()->fgets();
	}

	public function setContent($content) {
		$this->content = $content;
	}
}