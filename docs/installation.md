# Overview

# Install PHP
## With ppa:ondrej/php
```shell
sudo apt install software-properties-common
sudo LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
sudo apt update
```
## Use with MySQL database
```shell
sudo apt install php8.2-{cli,common,curl,intl,mbstring,xml,bz2,zip,gd,ssh2,mysql}
```
## Use with postgres
```shell
sudo apt install php8.2-{cli,common,curl,intl,mbstring,xml,bz2,gd,zip,ssh2,pgsql}
```
# Git & Composer
## Install composer
To install composer follow the [Documentation](https://getcomposer.org/download/)

## Install application
```
git clone https://github.com/Prestige-Solution/teamspeak-web-query-bot.git /var/www/psbot/
sudo chown -R user:www-data /var/www/psbot
cd /var/www/psbot/
composer install
```

# Initial setup
## Create .env
Copy the .env.example to .env
```shell
cd /var/www/psbot/
cp .env.example .env 
```
Change the ``DB_*`` and ``APP_URL`` configs and create a new APP_Key with
```shell
php artisan key:generate
php artisan app:setup
php artisan app:setup-account
```

# Supervisor
This application is developed and tested with [supervisor process control system](https://supervisord.org/introduction.html)
```shell
sudo apt install supervisor
```
## Default config
If you use our default directory at ``/var/www/psbot`` you can use directly our supervisor configs
```shell
sudo cp /var/www/psbot/docs/supervisor/example-psbot.conf /etc/supervisor/conf.d/psbot.conf
sudo cp /var/www/psbot/docs/supervisor/example-psbot-clearing.conf /etc/supervisor/conf.d/psbot-clearing.conf
sudo cp /var/www/psbot/docs/supervisor/example-psbot-worker.conf /etc/supervisor/conf.d/psbot-worker.conf
```

## Custom config
Change the config files at ``/etc/supervisor/conf.d/`` the command to ``command=php <YOUR PATH>/artisan ...``<br>
If you will handle more than 1 teamspeak server then increase at ``/etc/supervisor/conf.d/psbot.conf`` the value ``numprocs=1`` to your manged server count

## Start processes
```shell
#reload configs
sudo supervisorctl reload
#check processes are running
sudo supervisorctl status
psbot-bot:psbot-bot_00             RUNNING
psbot-clearing:psbot-clearing_00   RUNNING
psbot-worker:psbot-worker_00       RUNNING
#if the processes marked as stopped
sudo supervisorctl start all
```

# Setup Cronjob
```shell
sudo touch /etc/cron.d/psbot
sudo echo '* * * * * www-data php /var/www/psbot/artisan schedule:run' > /etc/cron.d/psbot
```
