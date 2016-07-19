<?php

namespace WorkerManager\Service\Component;

use Opis\Routing\Pattern;
use Opis\Routing\Route;
use WorkerManager\Application;
use WorkerManager\Interfaces\ComponentInterface;
use WorkerManager\Interfaces\RoutingHelperInterface;

/**
 * WorkerManager\Service\Component\Routing
 */
class Routing implements ComponentInterface
{
    /**
     * @var \Twig_Environment
     */
    static protected $twig;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @return \Twig_Environment
     */
    static public function getTwig()
    {
        return static::$twig;
    }

    /**
     * @param Application $application
     */
    public function registry(Application $application)
    {
        $loader = new \Twig_Loader_Filesystem(
            sprintf('%s/Resources/views', $application->getAppDir())
        );
        static::$twig = new \Twig_Environment($loader);
        $defaultData = [
            'appDir' => $application->getAppDir(),
        ];
        /** @var Route $route */
        foreach ($this->routes as $route) {
            $route->bind(
                'defaultData',
                function () use ($defaultData) {
                    return $defaultData;
                }
            );
            $application->addRoute($route);
        }
    }

    /**
     * @param string                 $route
     * @param string                 $template
     * @param RoutingHelperInterface $helper
     *
     * @return Route
     */
    public function add($route, $template, RoutingHelperInterface $helper)
    {
        $route = new Route(
            new Pattern($route),
            function (array $data, array $defaultData) use ($template) {
                return Routing::getTwig()->render($template, array_merge($defaultData, $data));
            }
        );
        $route->bind('data', function () use ($helper) {
            return $helper->getData();
        });
        $this->routes[] = $route;

        return $route;
    }
}
