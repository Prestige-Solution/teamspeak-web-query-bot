# Installation & Setup Guide

This guide provides step-by-step instructions for installing and configuring the **TeamSpeak Web Query Bot** on a Linux server (Ubuntu/Debian).

---

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Install PHP & Extensions](#1-install-php--extensions)
3. [Install Git & Composer](#2-install-git--composer)
4. [Clone Repository & File Permissions](#3-clone-repository--file-permissions)
5. [Install Dependencies](#4-install-dependencies)
6. [Environment Configuration & Initialization](#5-environment-configuration--initialization)
7. [Web Server Configuration](#6-web-server-configuration)
8. [Configure Supervisor (Process Management)](#7-configure-supervisor-process-management)
9. [Configure Cronjob (Task Scheduler)](#8-configure-cronjob-task-scheduler)
10. [TeamSpeak Server Configuration](#9-teamspeak-server-configuration)
11. [Updating & Upgrades](#10-updating--upgrades)

---

## System Requirements

- **Operating System:** Linux (Ubuntu 22.04 / 24.04 LTS or Debian recommended)
- **Web Server:** Nginx or Apache
- **PHP:** 8.3 or newer
- **Database:** MySQL 8.0+, MariaDB 10.3+, or PostgreSQL 13+
- **Composer:** v2.x
- **Node.js & npm (Optional):** v18.x or newer (pre-built assets are included in the repository; only needed if modifying frontend assets)
- **Supervisor:** For process supervision and queue handling
- **Git**

---

## 1. Install PHP & Extensions

Add the Ondřej Surý PHP PPA repository (Ubuntu/Debian):

```shell
sudo apt update
sudo apt install -y software-properties-common ca-certificates lsb-release apt-transport-https
sudo LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
sudo apt update
```

### For MySQL / MariaDB:
```shell
sudo apt install -y php8.3-{cli,fpm,common,curl,intl,mbstring,xml,bz2,zip,gd,ssh2,mysql}
```

### For PostgreSQL:
```shell
sudo apt install -y php8.3-{cli,fpm,common,curl,intl,mbstring,xml,bz2,zip,gd,ssh2,pgsql}
```

---

## 2. Install Git & Composer

### Install Git:
```shell
sudo apt install -y git
```

### Install Composer:
Follow the official [Composer Installation Guide](https://getcomposer.org/download/) or run:
```shell
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Install Node.js & npm (Optional):
> **Note:** Frontend assets are already pre-compiled and included in the repository. Installing Node.js and npm is only required if you plan to modify or rebuild the frontend assets.

```shell
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 3. Clone Repository & File Permissions

Clone the repository to `/var/www/psbot` and set the appropriate owner permissions for the web server user (`www-data`):

```shell
sudo git clone https://github.com/Prestige-Solution/teamspeak-web-query-bot.git /var/www/psbot
sudo chown -R www-data:www-data /var/www/psbot
sudo chmod -R 775 /var/www/psbot/storage /var/www/psbot/bootstrap/cache
cd /var/www/psbot
```

---

## 4. Install Dependencies

Install the PHP dependencies using Composer:

```shell
cd /var/www/psbot

# Install PHP dependencies (production mode)
sudo -u www-data composer install --no-dev --optimize-autoloader
```

> **Optional Frontend Asset Build:**  
> Pre-compiled assets are already tracked in the repository. If you make custom changes to stylesheet or JavaScript files, compile them using:
> ```shell
> sudo -u www-data npm ci
> sudo -u www-data npm run build
> ```

---

## 5. Environment Configuration & Initialization

Create the environment file and configure database credentials:

```shell
cd /var/www/psbot
sudo -u www-data cp .env.example .env
```

Edit `.env` using your preferred editor (e.g., `sudo nano .env`) and update the following settings:
- `APP_URL`: The full URL of your application (e.g., `https://bot.yourdomain.com`)
- `DB_CONNECTION`: `mysql` or `pgsql`
- `DB_HOST`: Database host (e.g., `127.0.0.1`)
- `DB_PORT`: `3306` (MySQL) or `5432` (PostgreSQL)
- `DB_DATABASE`: Name of your database
- `DB_USERNAME`: Database user
- `DB_PASSWORD`: Database password

Run the setup and account creation commands:

```shell
# Generate application encryption key
sudo -u www-data php artisan key:generate

# Run initial migrations, seeders, storage link, and optimizations
sudo -u www-data php artisan app:setup

# Create the initial administrator user account
sudo -u www-data php artisan app:setup-account
```

---

## 6. Web Server Configuration

Ensure your web server points its **Document Root** to `/var/www/psbot/public` (not the project root).

### Example Apache2 Virtual Host (`/etc/apache2/sites-available/psbot.conf`):

```apache
<VirtualHost *:80>
    ServerName bot.yourdomain.com
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/psbot/public

    <Directory /var/www/psbot/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/psbot_error.log
    CustomLog ${APACHE_LOG_DIR}/psbot_access.log combined
</VirtualHost>
```

Enable required Apache modules, enable the site configuration, and restart Apache:

```shell
# Enable mod_rewrite and PHP-FPM support
sudo a2enmod rewrite proxy_fcgi setenvif
sudo a2enconf php8.3-fpm

# Enable the psbot site
sudo a2ensite psbot.conf

# Test configuration and restart Apache
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## 7. Configure Supervisor (Process Management)

The application utilizes background workers and bot instances managed via [Supervisor](https://supervisord.org/).

### Install Supervisor:
```shell
sudo apt install -y supervisor
```

### Copy Default Configuration Files:
If you installed the application at `/var/www/psbot`, you can copy the example configuration files directly:

```shell
sudo cp /var/www/psbot/docs/supervisor/example-psbot.conf /etc/supervisor/conf.d/psbot.conf
sudo cp /var/www/psbot/docs/supervisor/example-psbot-clearing.conf /etc/supervisor/conf.d/psbot-clearing.conf
sudo cp /var/www/psbot/docs/supervisor/example-psbot-worker.conf /etc/supervisor/conf.d/psbot-worker.conf
sudo cp /var/www/psbot/docs/supervisor/example-psbot-migration.conf /etc/supervisor/conf.d/psbot-migration.conf
```

### Custom Configuration Notes:
- If your installation path differs from `/var/www/psbot`, update the `directory` and `command` paths in each file under `/etc/supervisor/conf.d/`.
- In `/etc/supervisor/conf.d/psbot.conf`, adjust `numprocs=5` to match the number of TeamSpeak server instances you manage.

### Reload and Start Supervisor:
```shell
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl reload

# Verify all processes are running
sudo supervisorctl status
```

Expected output:
```text
psbot-bot:psbot-bot_00                RUNNING   pid 1234, uptime 0:01:00
psbot-clearing:psbot-clearing_00      RUNNING   pid 1235, uptime 0:01:00
psbot-worker:psbot-worker_00          RUNNING   pid 1236, uptime 0:01:00
psbot-migration:psbot-migration_00    RUNNING   pid 1237, uptime 0:01:00
```

To start stopped processes:
```shell
sudo supervisorctl start all
```

---

## 8. Configure Cronjob (Task Scheduler)

The Laravel task scheduler runs scheduled jobs (such as statistics resets and background periodic tasks).

Add the scheduler cron entry:
```shell
echo "* * * * * www-data php /var/www/psbot/artisan schedule:run >> /dev/null 2>&1" | sudo tee /etc/cron.d/psbot
```

---

## 9. TeamSpeak Server Configuration

1. **SSH Host Key Configuration:**  
   If you are connecting via SSH to a TeamSpeak 3 Server, you must generate and configure a compatible `ssh_rsa_host_key`. See the [TeamSpeak SSH Compatibility Guide](https://github.com/Prestige-Solution/ts-x-php-framework/blob/main/doc/docker/make-ts-ssh-compatible.md#setup-a-ssh_rsa_host_key) for detailed instructions.

2. **Query IP Allowlist:**  
   Add the public/outgoing IP address of your web server to the `query_ip_allowlist.txt` file on your TeamSpeak server to avoid query flooding blocks.

---

## 10. Updating & Upgrades

To upgrade an existing installation to a newer version, you can execute the automated upgrade script (note that NPM steps are disabled by default as compiled assets are included in the repository):

```shell
cd /var/www/psbot
sudo -u www-data php upgrade.php
```

Or execute the steps manually:
```shell
cd /var/www/psbot
sudo -u www-data php artisan down
sudo -u www-data git pull
sudo -u www-data composer install --no-dev --optimize-autoloader

# Optional: Rebuild assets only if you modified frontend sources
# sudo -u www-data npm ci
# sudo -u www-data npm run build

sudo -u www-data php artisan app:setup
sudo -u www-data php artisan up
sudo supervisorctl restart all
```
