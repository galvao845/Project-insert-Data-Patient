<?php
namespace tests\Zeedhi\Framework\Util;

use Zeedhi\Framework\Util\Crypt;

class CryptTest extends \PHPUnit\Framework\TestCase {

    public function testCryptData() {
        $salt = "t4h2yH42knGn32";
        $crypt = new Crypt();
        $text = 'Zeedhi Framework';
        $encryptedText = $crypt->encrypt($text, $salt);
        $decryptedText = $crypt->decrypt($encryptedText, $salt);

        $this->assertEquals($text, $decryptedText);
    }

}