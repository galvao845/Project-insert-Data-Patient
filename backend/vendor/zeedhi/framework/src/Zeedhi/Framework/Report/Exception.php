<?php
namespace Zeedhi\Framework\Report;

class Exception extends \Exception {

	public static function strategyNotFound($strategyName) {
		return new static("No strategy found named '{$strategyName}'.");
	}

	public static function emptyReport() {
		return new static("No data to be listed");
	}

	public static function reportNotFound($reportName) {
		return new static("No report found named '{$reportName}'.");
	}
}