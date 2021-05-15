<?php
namespace Zeedhi\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class InstanceManager
 *
 * Dependency injector that can generate instances using class definitions and configured instance parameters
 *
 * @package Zeedhi\Framework\DependencyInjection
 */
class InstanceManager
{
    const YAML = 'YAML';
    const XML = 'XML';
    const PHP = 'PHP';

    /**
     * @var InstanceManager
     */
    protected static $instanceManager;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * @var \Symfony\Component\DependencyInjection\Loader\FileLoader
     */
    protected $loader;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->container = new ContainerBuilder();
    }

    /**
     * Get an container the dependency
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     *
     * @throws \Exception See \Symfony\Component\DependencyInjection\ContainerBuilder::get documentation
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     *
     * @return InstanceManager
     */
    public function registerService($id, $service)
    {
        $this->container->set($id, $service);
        return $this;
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws \InvalidArgumentException if the parameter is not defined
     *
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }


    /**
     * Sets a service definition.
     *
     * @param string     $id         The service identifier
     * @param Definition $definition A Definition instance
     *
     * @return Definition the service definition
     *
     * @throws BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    public function setDefinition($id, Definition $definition) {
        return $this->container->setDefinition($id, $definition);
    }


    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @return InstanceManager
     */
    public function setParameter($name, $value)
    {
        $this->container->setParameter($name, $value);
        return $this;
    }

    /**
     * Compiles the container.
     *
     * This method passes the container to compiler
     * passes whose job is to manipulate and optimize
     * the container.
     *
     * @return void
     */
    public function compile() {
        $this->container->compile();
    }

    /**
     * Loads a dependency file.
     *
     * @param string $file
     * @param string $type
     *
     * @return void
     */
    public function loadFromFile($file, $type = InstanceManager::XML)
    {
        $dirName = dirname($file);
        $fileName = basename($file);
        switch ($type) {
            case self::YAML:
                $this->loader = new YamlFileLoader($this->container, new FileLocator($dirName));
                $this->loader->load($fileName);
                break;
            case self::XML:
                $this->loader = new XmlFileLoader($this->container, new FileLocator($dirName));
                $this->loader->load($fileName);
                break;
            case self::PHP:
                $this->loader = new PhpFileLoader($this->container, new FileLocator($dirName));
                $this->loader->load($fileName);
                break;
        }
    }

    /**
     *  Create an instance of InstanceManager
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!self::$instanceManager) {
            self::$instanceManager = new static();
        }
        return self::$instanceManager;
    }

} 