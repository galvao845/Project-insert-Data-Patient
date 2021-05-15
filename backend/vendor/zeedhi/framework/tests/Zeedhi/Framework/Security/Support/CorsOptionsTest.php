<?php
namespace tests\Zeedhi\Framework\Security\Support;


use Zeedhi\Framework\Security\Support\CorsOptions;

class CorsOptionsTest extends \PHPUnit\Framework\TestCase {

	public function testCorsOptionsWithoutAllowed() {
		$options = new CorsOptions();
		$this->assertEquals(array(), $options->getAllowedOrigins(), 'No origin are allowed');
		$this->assertEquals(array(), $options->getAllowedMethods(), 'No methods are allowed');
		$this->assertEquals(array(), $options->getAllowedHeaders(), 'No headers are allowed');
		$this->assertEquals(0, $options->getMaxAge(), 'Preflight request cannot be cached');
		$this->assertEquals(false, $options->getExposedHeaders(), 'No headers are exposed to the browser');
		$this->assertFalse($options->isSupportCredentials(), 'Cookies are not allowed');
	}

	public function testCorsOptionsAllAllowed() {
		$options = new CorsOptions('*', '*', '*', 1000, false, false);
		$this->assertEquals('*', $options->getAllowedOrigins(), 'All origins allowed');
		$this->assertEquals('*', $options->getAllowedMethods(), 'All methods allowed');
		$this->assertEquals('*', $options->getAllowedHeaders(), 'All headers allowed');
		$this->assertEquals(1000, $options->getMaxAge(), 'Preflight request cached by 1000s');
		$this->assertEquals(false, $options->getExposedHeaders(), 'No headers are exposed to the browser');
		$this->assertFalse($options->isSupportCredentials(), 'Cookies are not allowed');
	}

}
