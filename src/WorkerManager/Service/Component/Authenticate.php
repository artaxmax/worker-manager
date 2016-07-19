<?php

namespace WorkerManager\Service\Component;

use WorkerManager\Application;
use WorkerManager\Interfaces\ComponentInterface;

/**
 * WorkerManager\Service\Component\Authenticate
 */
class Authenticate implements ComponentInterface
{
    /**
     * authenticate method
     */
    protected function authenticate() {
        header('WWW-Authenticate: Basic realm="Test Authentication System"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must enter a valid login ID and password to access this resource\n";
        exit;
    }

    /**
     * @param Application $application
     */
    public function registry(Application $application)
    {
        $config = $application->getConfig();
        if ($config->get('worker_manager.control.auth') === true) {
            if (!isset($_SERVER['PHP_AUTH_USER']) ||
                $_SERVER['PHP_AUTH_USER'] !== $config->get('worker_manager.control.username') ||
                $_SERVER['PHP_AUTH_PW'] !== $config->get('worker_manager.control.password')
            ) {
                $this->authenticate();
            }
        }
    }
}
