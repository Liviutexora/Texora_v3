<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$requiredPhpVersion = '8.2.0';
$currentPhpVersion = PHP_VERSION;
if (version_compare($currentPhpVersion, $requiredPhpVersion, '<')) {
    http_response_code(503);
    require __DIR__ . '/installer-requirements.php';
    exit;
}

$envPath = __DIR__ . '/../.env';
$envExamplePath = __DIR__.'/../.env.example';
$installedFile = __DIR__.'/../.installed';

if (!file_exists($envPath)) {
    if (file_exists($envExamplePath)) {
        copy($envExamplePath, $envPath);
        if (file_exists($installedFile)) {
            unlink($installedFile);
        }
    }
}


$basePath = __DIR__ . '/..';
$autoloadPath = $basePath . '/vendor/autoload.php';

// Check if Composer dependencies are installed
if (!file_exists($autoloadPath)) {
    if (file_exists($basePath . '/composer.lock')) {
        unlink($basePath . '/composer.lock');
    }
    
    // Change to project root before running composer
    $originalDir = getcwd();
    chdir($basePath);
    
    // Run composer install - let Composer use its default HOME/COMPOSER_HOME
    // Only set COMPOSER_HOME if system HOME is not available (rare edge case)
    $command = 'composer install --no-interaction --prefer-dist 2>&1';
    
    // Only override COMPOSER_HOME if HOME is not set (unusual but possible in some environments)
    if (empty(getenv('HOME')) && empty(getenv('COMPOSER_HOME'))) {
        // Use system temp directory instead of project storage
        $composerHome = sys_get_temp_dir() . '/.composer-' . md5($basePath);
        $command = 'COMPOSER_HOME="' . escapeshellarg($composerHome) . '" ' . $command;
    }
    
    exec($command, $composerOutput, $composerReturnCode);
    
    // Restore original directory
    chdir($originalDir);
}


$manifestPath = __DIR__.'/build/manifest.json';
if (!file_exists($manifestPath)) {
    if (file_exists(__DIR__ . '/../package-lock.json')) {
        unlink(__DIR__ . '/../package-lock.json');
    }
    // Change to project root and run npm install and build
    $currentDir = getcwd();
    chdir($basePath);
    exec('npm install && npm run build 2>&1', $npmOutput, $npmReturnCode);
    chdir($currentDir);
}


// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Capture the request
$request = Request::capture();

$app->handleRequest($request);
