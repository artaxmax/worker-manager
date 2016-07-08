# worker-manager

Application check rabbitMQ queue message rate and supervisor worker status.
If queue is empty - app will stop workers. Whet get new message - will start worker(s).
In supervisor configuration need to be configured max count of workers.

Installation

    composer require artaxmax/worker-manager
    
## Usage

Create console file (app/console.php):

    <?php
    
    set_time_limit(0);
    
    require_once __DIR__.'/../vendor/autoload.php'; // include composer autoload.php file
    
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Input\ArgvInput;
    use WorkerManager\Command\WorkerCommand;
    
    $application = new Application();
    $application->add(new WorkerCommand()); // add worker command
    $application->run(new ArgvInput()); // run application
    
Create config [YML](http://yaml.org/) file (config.yml)

* Configure RabbitMQ api:

        worker_manager:
            rabbitmq:
                rabbit1:
                    name: rabbit1
                    host: localhost
                    username: username
                    password: password

* Configure supervisor:

        worker_manager:
            supervisor1:
                name: supervisor1
                server: http://localhost:8081
                username: username
                password: password
                config: /.../supervisor.conf
                max_worker_count: 10
                
* Configure each one worker:

        worker_manager:
            worker1:
                name: worker1
                queue: worker1-queue
                vhost: /
                min_count: 1
        
Register config file

    <?php
    
    use WorkerManager\Service\ConfigManager;
    
    ...
    
    ConfigManager::register(__DIR__.'/config/config.yml');

Run application:

    php app/console.php worker-manager:start
    

You can add your own logging class. It should implement `WorkerManager\Interfaces\LoggerInterface` interface:

    <?php
        ...
        $logger = new YourLogger();
        $command = new WorkerCommand();
        $command->setLogger($logger);
        ...
        $application->add($command);
        ...


