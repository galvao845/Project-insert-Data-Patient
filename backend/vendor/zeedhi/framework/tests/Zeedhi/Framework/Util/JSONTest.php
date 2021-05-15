<?php
namespace Zeedhi\Framework\Util;

use Zeedhi\Framework\Util\JSON;

class JSONTest extends \PHPUnit\Framework\TestCase {

    public function testFactoryObjectWithNull() {
        $this->assertEquals(
            'null',
            JSON::factoryObjectFromArray(null)
        );
    }

    public function testFactoryObjectWithString() {
        $this->assertEquals(
            '"Test"',
            JSON::factoryObjectFromArray('Test')
        );
    }

    public function testFactoryObjectWithNumber() {
        $this->assertEquals(
            1,
            JSON::factoryObjectFromArray(1)
        );
    }

    public function testFactoryObjectWithObject() {
        $this->assertEquals(
            'stdClass',
            JSON::factoryObjectFromArray(new \stdClass)
        );
    }

    public function testFactoryObjectWithArray() {
        $this->assertEquals(
            '[]',
            JSON::factoryObjectFromArray([])
        );
    }

    public function testFactoryObjectWithResource() {
        $file = fopen(__DIR__.'/entities.json', 'r');
        $this->assertEquals(
            '"resource"',
            JSON::factoryObjectFromArray($file)
        );
        fclose($file);
    }

    public function testFactoryObjectWithMixed() {
        $file = fopen(__DIR__.'/entities.json', 'r');
        $obj = [
            'string' => 'test',
            'number' => 1,
            'object' => new \stdClass,
            'null' => null,
            'resource' => $file,
            'stringList' => ['a', 'b'],
            'numberList' => [1, 1.1],
            'objectList' => [new \stdClass, new \stdClass],
            'mixedList' => [1, 'a', new \stdClass, [], [1]],
            'resourceList' => [$file, $file],
            'arrayList' => [[], []],
            'nullList' => [null, null],
            'emptyList' => [],
        ];
        $json = '{"string": "test","number": 1,"object": stdClass,"null": null,"resource": "resource","stringList": ["a" ,"b"],"numberList": [1 ,1.1],"objectList": [stdClass ,stdClass],"mixedList": [1 ,"a" ,stdClass ,[] ,[1]],"resourceList": ["resource" ,"resource"],"arrayList": [[] ,[]],"nullList": [null ,null],"emptyList": []}';
        $this->assertEquals(
            $json,
            JSON::factoryObjectFromArray($obj)
        );
        fclose($file);
    }
}