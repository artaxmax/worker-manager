<?php

namespace WorkerManager;

use Opis\Routing\Collections\RouteCollection;
use Opis\Routing\Path;
use Opis\Routing\Route;
use Opis\Routing\Router;
use WorkerManager\Interfaces\ComponentInterface;
use WorkerManager\Service\ConfigManager;

/**
 * WorkerManager\Application
 */
class Application
{
    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @var array
     */
    private $services = [];

    /**
     * @param RouteCollection $routeCollection
     */
    public function __construct(
        RouteCollection $routeCollection = null
    ) {
        $this->routeCollection = $routeCollection ?: new RouteCollection();
        ConfigManager::register(__DIR__.'/Resources/config');
    }

    /**
     * @param ComponentInterface $component
     */
    public function addComponent(ComponentInterface $component)
    {
        $component->registry($this);
    }

    /**
     * run application
     */
    public function run()
    {
        $path = new Path($_SERVER['SCRIPT_NAME']);
        try {
            print $this->buildRouter()->route($path);
        } catch (\Exception $exception) {
            print $exception->getMessage();
        }
    }

    /**
     * @return \Noodlehaus\Config
     */
    public function getConfig()
    {
        return ConfigManager::getConfig();
    }

    /**
     * @param Route $route
     *
     * @return static
     */
    public function addRoute(Route $route)
    {
        $this->routeCollection[] = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return __DIR__;
    }

    /**
     * @param string $serviceName
     * @param string $class
     * @param array  $construct
     *
     * @return $this
     */
    public function addService($serviceName, $class, array $construct = [])
    {
        $this->services[(string) $serviceName] = [
            'class' => (string) $class,
            'construct' => $construct,
        ];

        return $this;
    }

    /**
     * @param string $serviceName
     *
     * @return object
     * @throws \Exception
     */
    public function getService($serviceName)
    {
        $serviceName = (string) $serviceName;
        if (isset($this->services[$serviceName])) {
            $className = $this->services[$serviceName]['class'];
            $ref = new \ReflectionClass($className);

            return $ref->newInstanceArgs($this->services[$serviceName]['construct']);
        }

        throw new \Exception(sprintf('Service not found %s!', $serviceName));
    }

    /**
     * @return Router
     */
    protected function buildRouter()
    {
        return new Router($this->routeCollection);
    }
}
