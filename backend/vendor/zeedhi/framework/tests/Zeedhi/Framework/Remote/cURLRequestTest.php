<?php
namespace tests\Zeedhi\Framework\Remote;

use Zeedhi\Framework\Remote\cURLRequest;

class cURLRequestTest extends \PHPUnit\Framework\TestCase {

    const URL = 'http://localhost/';

    public function testCreateInstance() {
        $cURLRequest = new cURLRequest(self::URL);

        $this->assertInstanceOf('Zeedhi\Framework\Remote\HttpInterface', $cURLRequest);
    }

}