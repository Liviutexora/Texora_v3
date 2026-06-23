<?php

namespace App\Providers;

use Exception;
use Closure;
use Illuminate\Support\ServiceProvider;

abstract class DatabaseAwareServiceProvider extends ServiceProvider
{
    /**
     * Check if we're running a composer command
     */
    protected function isRunningComposerCommand(): bool
    {
        if (!$this->app->runningInConsole()) {
            return false;
        }

        $argv = $_SERVER['argv'] ?? [];
        
        $composerCommands = [
            'composer',
            'dump-autoload',
            'package:discover',
            'clear-compiled',
            'optimize',
            'post-autoload-dump',
            'post-update-cmd',
            'post-install-cmd',
            'pre-autoload-dump',
        ];
        
        foreach ($argv as $arg) {
            foreach ($composerCommands as $command) {
                if (str_contains($arg, $command)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if database connection is ready
     */
    protected function isDatabaseReady(): bool
    {
        try {
            $this->app['db']->connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if we can safely run database operations
     */
    protected function canRunDatabaseOperations(): bool
    {
        return !$this->isRunningComposerCommand() && $this->isDatabaseReady();
    }

    /**
     * Boot only if database is available
     */
    protected function bootWithDatabase(Closure $callback): void
    {
        if ($this->isRunningComposerCommand()) {
            return;
        }

        $this->app->booted(function () use ($callback) {
            if ($this->canRunDatabaseOperations()) {
                $callback();
            }
        });
    }
}