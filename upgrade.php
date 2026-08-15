<?php

declare(strict_types=1);

const USE_MAINTENANCE_MODE = true;
const INSTALL_DEV_DEPENDENCIES = false;
const RUN_NPM_INSTALL = false;
const RUN_NPM_BUILD = false;
const RUN_MIGRATIONS = true;
const RUN_SEEDER = true;
const RUN_STORAGE_LINK = true;

function printInfo(string $message): void
{
    echo PHP_EOL . '[OK] ' . $message . PHP_EOL;
}

function printWarning(string $message): void
{
    echo PHP_EOL . '[WARNING] ' . $message . PHP_EOL;
}

function printError(string $message): void
{
    echo PHP_EOL . '[ERROR] ' . $message . PHP_EOL;
}

function runCommand(string $command, bool $allowFailure = false): void
{
    echo PHP_EOL . '> ' . $command . PHP_EOL;

    passthru($command . ' 2>&1', $exitCode);

    if ($exitCode !== 0) {
        if ($allowFailure) {
            printWarning('Command failed, but will be ignored. Exit Code: ' . $exitCode);

            return;
        }

        throw new RuntimeException('Command failed: ' . $command . ' | Exit Code: ' . $exitCode);
    }
}

function runArtisan(string $command, bool $allowFailure = false): void
{
    runCommand('php artisan ' . $command, $allowFailure);
}

function disableMaintenanceMode(): void
{
    try {
        runArtisan('up', true);
    } catch (Throwable $exception) {
        printWarning('Maintenance mode could not be automatically disabled.');
        printWarning($exception->getMessage());
    }
}

$startedAt = microtime(true);
$projectRoot = __DIR__;

chdir($projectRoot);

try {
    if (! file_exists($projectRoot . '/artisan')) {
        throw new RuntimeException('No artisan file found. The upgrade.php file must be located in the Laravel project root directory..');
    }

    if (! file_exists($projectRoot . '/composer.json')) {
        throw new RuntimeException('No composer.json file found.');
    }

    if (! file_exists($projectRoot . '/package.json')) {
        throw new RuntimeException('No package.json found.');
    }

    printInfo('Upgrade started');
    printInfo('Project Path: ' . $projectRoot);

    if (USE_MAINTENANCE_MODE) {
        runArtisan('down');
    }

    if (INSTALL_DEV_DEPENDENCIES) {
        runCommand('composer install --no-interaction --prefer-dist --optimize-autoloader');
    } else {
        runCommand('composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader');
    }

    if (RUN_NPM_INSTALL) {
        if (file_exists($projectRoot . '/package-lock.json')) {
            runCommand('npm ci');
        } else {
            runCommand('npm install');
        }
    }

    if (RUN_NPM_BUILD) {
        runCommand('npm run build');
    }

    runArtisan('cache:clear');
    runArtisan('config:clear');
    runArtisan('route:clear');
    runArtisan('view:clear');
    runArtisan('event:clear', true);

    if (RUN_MIGRATIONS) {
        runArtisan('migrate --force');
    }

    if (RUN_SEEDER) {
        runArtisan('db:seed --force');
    }

    if (RUN_STORAGE_LINK) {
        runArtisan('storage:link', true);
    }

    runArtisan('optimize');
    runArtisan('view:cache');

    if (USE_MAINTENANCE_MODE) {
        runArtisan('up');
    }

    $duration = round(microtime(true) - $startedAt, 2);

    printInfo('Upgrade successfully completed in ' . $duration . ' seconds.');

    exit(0);
} catch (Throwable $exception) {
    printError('Upgrade Failed!');
    printError($exception->getMessage());

    if (USE_MAINTENANCE_MODE) {
        disableMaintenanceMode();
    }

    exit(1);
}
