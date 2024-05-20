<?php
namespace Deployer;

require 'recipe/laravel.php';

/**
 * Config
 */

desc('Configuring the deployment.');

set('repository', 'git@git.ps.prestige-solutions.de:onitzsche/ts3-php-bot.git');
set('ssh_multiplexing', true);  // Speed up deployment
set('keep_releases', 3);        // Keep only the last n releases

add('shared_files', []);
add('shared_dirs', ['storage']);
add('writable_dirs', []);

/**
 * Hosts
 */

desc('Configuring the host where to deploy.');

host('devpsbot.ps.prestige-solutions.de')
    ->set('hostname', 'devpsbot.ps.prestige-solutions.de')
    ->set('remote_user', 'git-deploy')
    ->set('branch', 'development')
    ->set('deploy_path', '/var/www/devpsbot-gamerboerse-de')
    ->setLabels([
        'env' => 'prod',
    ]);

/**
 * Hooks
 */

after('deploy:vendors', 'artisan:down');             // Enable maintenance mode
//after('deploy:update_code', 'deploy:update_images'); // Install current images from Git
after('deploy:publish', 'artisan:up');               // Disable maintenance mode

after('deploy:failed', 'deploy:unlock');

/**
 * Tasks
 */

desc('Starting the actual deployment of the application.');

// Set up a deployer task to copy secrets to the server.
// Grabs the DotEnv file from the Gitlab Variable
task('deploy:secrets', function () {
    upload(getenv('DOTENV_DEPLOYMENT_STAGING'), get('deploy_path') . '/shared/.env');
});

// Set up a deployer task to ensure, that the Git versioned files are properly uploaded to the storage.
// Known issue: https://github.com/deployphp/deployer/issues/2069
//task('deploy:update_images', function () {
//    $shared_path = get('deploy_path') . '/shared/storage/app/public/img';
//    $release_path = "{{release_path}}/storage/app/public/img";
//
//    $img_subfolders = ['design', 'partner'];
//
//    foreach ($img_subfolders as $subfolder) {
//        // delete directory, if it exists in order to delete potential old files
//        run("rm -rf $shared_path/$subfolder || true");
//
//        // copy image folders and files to shared storage
//        run("cp -ru $release_path/$subfolder $shared_path/");
//    }
//});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',         // Deploy the vendors files
    'deploy:secrets',         // Deploy secrets
    'deploy:shared',          // Deploy shared files (e.g. `.env` file)
    'artisan:storage:link',   // Ensure, that the storage is properly linked
    'artisan:cache:clear',    // Clear old cache
    'artisan:optimize',       // Cache the current DotEnv and routes
    'artisan:view:cache',     // Cache the current views
    'artisan:migrate',        // Run database migrations
    'artisan:migrate:status', // Show database migration status
    'artisan:db:seed',        // Run seed
    'deploy:publish',         // Publishes the release
]);

desc('Deployment completed.');

