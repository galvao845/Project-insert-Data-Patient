<?php

class OraclePlatformTest extends PHPUnit\Framework\TestCase {

	const DATE_FORMAT = 'd/m/Y H:i:s';

	public function createPlatform() {
		return new \Zeedhi\Framework\ORM\Platform\OraclePlatform();
	}

	public function testOracleDateFormatString() {
		$oraclePlatform = $this->createPlatform();
		$this->assertEquals(self::DATE_FORMAT, $oraclePlatform->getDateFormatString());
	}

	public function testOracleDateTimeFormatString() {
		$oraclePlatform = $this->createPlatform();
		$this->assertEquals(self::DATE_FORMAT, $oraclePlatform->getDateTimeFormatString());
	}

}
