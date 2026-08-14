<?php

declare(strict_types=1);

const USE_MAINTENANCE_MODE = true;
const INSTALL_DEV_DEPENDENCIES = false;
const RUN_NPM_INSTALL = true;
const RUN_NPM_BUILD = true;
const RUN_MIGRATIONS = true;
const RUN_SEEDER = false;
const RUN_STORAGE_LINK = true;

function printInfo(string $message): void
{
    echo PHP_EOL . '[OK] ' . $message . PHP_EOL;
}

function printWarning(string $message): void
{
    echo PHP_EOL . '[WARNUNG] ' . $message . PHP_EOL;
}

function printError(string $message): void
{
    echo PHP_EOL . '[FEHLER] ' . $message . PHP_EOL;
}

function runCommand(string $command, bool $allowFailure = false): void
{
    echo PHP_EOL . '> ' . $command . PHP_EOL;

    passthru($command . ' 2>&1', $exitCode);

    if ($exitCode !== 0) {
        if ($allowFailure) {
            printWarning('Befehl fehlgeschlagen, wird aber ignoriert. Exit Code: ' . $exitCode);

            return;
        }

        throw new RuntimeException('Befehl fehlgeschlagen: ' . $command . ' | Exit Code: ' . $exitCode);
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
        printWarning('Wartungsmodus konnte nicht automatisch deaktiviert werden.');
        printWarning($exception->getMessage());
    }
}

$startedAt = microtime(true);
$projectRoot = __DIR__;

chdir($projectRoot);

try {
    if (! file_exists($projectRoot . '/artisan')) {
        throw new RuntimeException('Keine artisan-Datei gefunden. Die upgrade.php muss im Laravel-Projekt-Root liegen.');
    }

    if (! file_exists($projectRoot . '/composer.json')) {
        throw new RuntimeException('Keine composer.json gefunden.');
    }

    if (! file_exists($projectRoot . '/package.json')) {
        throw new RuntimeException('Keine package.json gefunden.');
    }

    printInfo('Upgrade gestartet');
    printInfo('Projektpfad: ' . $projectRoot);

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

    printInfo('Upgrade erfolgreich abgeschlossen in ' . $duration . ' Sekunden.');

    exit(0);
} catch (Throwable $exception) {
    printError('Upgrade fehlgeschlagen!');
    printError($exception->getMessage());

    if (USE_MAINTENANCE_MODE) {
        disableMaintenanceMode();
    }

    exit(1);
}
