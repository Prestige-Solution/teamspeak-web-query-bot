<!-- TOC -->
* [Install PHP with extensions](#install-php-with-extensions)
* [Git & Composer](#git--composer)
* [Initial Setup](#initial-setup)
* [Supervisor](#supervisor)
  * [Default Config](#default-config)
  * [Custom Config](#custom-config)
* [Setup Cronjob](#setup-cronjob)
<!-- TOC -->

# Install PHP with extensions
If you use mysql:
```shell
sudo apt install php8.2-{cli,common,curl,intl,mbstring,xml,bz2,gd,ssh2,mysql}
```
If you use postgres:
```shell
sudo apt install php8.2-{cli,common,curl,intl,mbstring,xml,bz2,gd,ssh2,pgsql}
```
# Git & Composer
```
git clone https://github.com/Prestige-Solution/teamspeak-web-query-bot.git /var/www/psbot/
cd /var/www/psbot/
composer install
npm install
```

# Initial Setup
To start the initial setup run ``/var/www/psbot/php artisan app:setup``

# Supervisor
This application is developed and tested with [supervisor process control system](https://supervisord.org/introduction.html)
```shell
sudo apt install supervisor
```
## Default Config
If you use our default directory at ``/var/www/psbot`` you can use directly our supervisor configs
```shell
cp /var/www/psbot/docs/supervisor/example-psbot.conf /etc/supervisor/conf.d/psbot.conf
cp /var/www/psbot/docs/supervisor/example-psbot-clearing.conf /etc/supervisor/conf.d/psbot-clearing.conf
cp /var/www/psbot/docs/supervisor/example-psbot-worker.conf /etc/supervisor/conf.d/psbot-worker.conf
```

```shell
sudo supervisorctl reload
sudo supervisorctl start all
```
## Custom Config
Change the config files at ``/etc/supervisor/conf.d/`` the command to ``command=php <YOUR PATH>/artisan ...``<br>
If you will handle more than 1 teamspeak server then increase at ``/etc/supervisor/conf.d/psbot.conf`` the value ``numprocs=1`` to your manged server count
```shell
sudo supervisorctl reload
sudo supervisorctl start all
```

# Setup Cronjob
```shell
sudo touch /etc/cron.d/psbot
sudo echo '* * * * * www-data php /var/www/psbot/artisan schedule:run' > /etc/cron.d/etc/cron.d/psbot
```
