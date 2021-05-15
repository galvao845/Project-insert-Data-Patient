<?php
namespace tests\Zeedhi\Framework\HTTP;

use Zeedhi\Framework\HTTP\Parameter;

class ParameterTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Based ParameterBag on Symfony2
     */
    public function testAll()
    {
        $parameter = new Parameter(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $parameter->getAll(), '->getAll() gets all the input');
    }

    public function testKeys()
    {
        $parameter = new Parameter(array('foo' => 'bar'));
        $this->assertEquals(array('foo'), $parameter->getKeys());
    }

    public function testAdd()
    {
        $parameter = new Parameter(array('foo' => 'bar'));
        $parameter->add(array('bar' => 'bas'));
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bas'), $parameter->getAll());
    }

    public function testRemove()
    {
        $parameter = new Parameter(array('foo' => 'bar'));
        $parameter->add(array('bar' => 'bas'));
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bas'), $parameter->getAll());
        $parameter->remove('bar');
        $this->assertEquals(array('foo' => 'bar'), $parameter->getAll());
    }

    public function testHas()
    {
        $parameter = new Parameter(array('foo' => 'bar'));

        $this->assertTrue($parameter->has('foo'), '->has() returns true if a parameter is defined');
        $this->assertFalse($parameter->has('unknown'), '->has() return false if a parameter is not defined');
    }

    public function testGet()
    {
        $parameter = new Parameter(array('foo' => 'bar', 'null' => null));

        $this->assertEquals('bar', $parameter->get('foo'), '->get() gets the value of a parameter');
        $this->assertEquals('default', $parameter->get('unknown', 'default'), '->get() returns second argument as default if a parameter is not defined');
        $this->assertNull($parameter->get('null', 'default'), '->get() returns null if null is set');
    }

    public function testGetDeep()
    {
        $parameter = new Parameter(array('foo' => array('bar' => array('moo' => 'boo'))));

        $this->assertEquals(array('moo' => 'boo'), $parameter->get('foo[bar]', null, true));
        $this->assertEquals('boo', $parameter->get('foo[bar][moo]', null, true));
        $this->assertEquals('default', $parameter->get('foo[bar][foo]', 'default', true));
        $this->assertEquals('default', $parameter->get('bar[moo][foo]', 'default', true));
    }

    public function testSet()
    {
        $parameter = new Parameter(array());

        $parameter->set('foo', 'bar');
        $this->assertEquals('bar', $parameter->get('foo'), '->set() sets the value of parameter');

        $parameter->set('foo', 'baz');
        $this->assertEquals('baz', $parameter->get('foo'), '->set() overrides previously set parameter');
    }

    public function testReplace()
    {
        $parameter = new Parameter(array('foo' => 'bar'));

        $parameter->replace(array('FOO' => 'BAR'));
        $this->assertEquals(array('FOO' => 'BAR'), $parameter->getAll(), '->replace() replaces the input with the argument');
        $this->assertFalse($parameter->has('foo'), '->replace() overrides previously set the input');
    }

	/**
	 * @dataProvider getInvalidPaths
	 * @expectedException \Exception
	 */
	public function testGetDeepWithInvalidPaths($path)
	{
		$parameter = new Parameter(array('foo' => array('bar' => 'moo')));
		$parameter->get($path, null, true);
	}

	public function getInvalidPaths()
	{
		return array(
			array('foo[['),
			array('foo[d'),
			array('foo[bar]]'),
			array('foo[bar]d'),
		);
	}

}
