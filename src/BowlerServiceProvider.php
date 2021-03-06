<?php

namespace UniversalTechnology\Bowler;

use Illuminate\Support\ServiceProvider;
use UniversalTechnology\Bowler\Console\Commands\QueueCommand;
use UniversalTechnology\Bowler\Console\Commands\ConsumeCommand;
use UniversalTechnology\Bowler\Console\Commands\SubscriberCommand;

/**
 * @author Ali Issa <ali@vinelab.com>
 * @author Kinane Domloje <kinane@vinelab.com>
 */
class BowlerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton('vinelab.bowler.registrator', function ($app) {
            return new RegisterQueues();
        });

        // Bind connection to env configuration
        $rbmqHost = config('queue.connections.rabbitmq.host');
        $rbmqPort = config('queue.connections.rabbitmq.port');
        $rbmqUsername = config('queue.connections.rabbitmq.username');
        $rbmqPassword = config('queue.connections.rabbitmq.password');
        $this->app->bind(Connection::class, function () use ($rbmqHost, $rbmqPort, $rbmqUsername, $rbmqPassword) {
            return new Connection($rbmqHost, $rbmqPort, $rbmqUsername, $rbmqPassword);
        });

        $this->app->bind(
            \UniversalTechnology\Bowler\Contracts\BowlerExceptionHandler::class,
            $this->app->getNamespace() . \Exceptions\Handler::class
        );

        //register command
        $commands = [
            QueueCommand::class,
            ConsumeCommand::class,
            SubscriberCommand::class,
        ];
        $this->commands($commands);
    }
}
