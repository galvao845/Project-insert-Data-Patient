<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response\Notification;

class NotificationTest extends \PHPUnit\Framework\TestCase {

    public function testSuccess() {
        $expectedMessage = "expected message";
        $notification = new Notification($expectedMessage);
        $this->assertEquals($expectedMessage, $notification->getMessage());
        $this->assertEquals(Notification::TYPE_SUCCESS, $notification->getType());
    }

    public function testAlert() {
        $expectedMessage = "expected message";
        $notification = new Notification($expectedMessage, Notification::TYPE_ALERT);
        $this->assertEquals($expectedMessage, $notification->getMessage());
        $this->assertEquals(Notification::TYPE_ALERT, $notification->getType());
    }

    public function testError() {
        $expectedMessage = "expected message";
        $notification = new Notification($expectedMessage, Notification::TYPE_ERROR);
        $this->assertEquals($expectedMessage, $notification->getMessage());
        $this->assertEquals(Notification::TYPE_ERROR, $notification->getType());
    }

    public function testVariables() {
        $expectedMessage = "expected message __myVar__";
        $notification = new Notification($expectedMessage, Notification::TYPE_SUCCESS, array("myVar" => "Awesome!"));
        $this->assertEquals($expectedMessage, $notification->getMessage());
        $this->assertEquals(Notification::TYPE_SUCCESS, $notification->getType());

        $variables = $notification->getVariables();
        $this->assertArrayHasKey("myVar", $variables, 'It is expected to there is a myVar key on the notification variables.');
        $this->assertEquals("Awesome!", $variables["myVar"], 'It is expected to the myVar key value to be equal "Awesome!".');
    }
}