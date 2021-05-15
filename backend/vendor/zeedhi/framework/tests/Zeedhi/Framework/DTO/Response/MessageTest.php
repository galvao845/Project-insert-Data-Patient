<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response\Message;

class MessageTest extends \PHPUnit\Framework\TestCase
{

    public function testCreateMessage()
    {
        $message = new Message('Fork me on GitHub');
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Message', $message, 'It is expected an instance of Message.');
        $this->assertEquals('Fork me on GitHub', $message->getMessage(), 'Message expected is "Fork me on GitHub".');
    }

    public function testCreateMessageByType()
    {
        $message = new Message('Fork me on GitHub', Message::TYPE_WARNING);
        $this->assertEquals(Message::TYPE_WARNING, $message->getType(), 'It is expected type of WARNING.');
    }

    public function testFadeTimeByMessageType()
    {
        $this->assertEquals(5000, Message::fadeTimeByType(Message::TYPE_MESSAGE), 'It is expected 5000 ms in fadeTime.');
        $this->assertEquals(5000, Message::fadeTimeByType(Message::TYPE_WARNING), 'It is expected 5000 ms in fadeTime.');
        $this->assertEquals(30000, Message::fadeTimeByType(Message::TYPE_ERROR), 'It is expected 30000 ms in fadeTime.');
        $this->assertEquals(5000, Message::fadeTimeByType(), 'It is expected 5000 ms in fadeTime.');
    }

    public function testCreateMessageWithFadeTime()
    {
        $message = new Message('Fork me on GitHub', Message::TYPE_MESSAGE, 1000);
        $this->assertEquals(1000, $message->getFadeTime(), 'It is expected 1000 ms in fadeTime.');
    }

    public function testCreateMessageWithVariables()
    {
        $message = new Message('Fork me on GitHub! __myVar__', Message::TYPE_MESSAGE, null, array("myVar" => "Awesome!"));
        $variables = $message->getVariables();
        $this->assertArrayHasKey("myVar", $variables, 'It is expected to there is a myVar key on the message variables.');
        $this->assertEquals("Awesome!", $variables["myVar"], 'It is expected to the myVar key value to be equal "Awesome!".');
    }

}