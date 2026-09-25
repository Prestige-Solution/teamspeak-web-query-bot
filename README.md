# TeamSpeak Web Query Bot

Your TeamSpeak server, your rules – without the administrative stress.<br>
Tired of clicking around in the server query? Our web app handles the heavy lifting for you: create channels, set permissions, manage users – everything runs automatically.<br>
Log in, configure, and sit back.<br>
Less admin, more gaming.

---

## Features

- **Channel Creator:** Create dynamic channels and define client actions in just a few clicks.
- **Channel Remover:** Automatically clean up temporary or unused sub-channels.
- **Dynamic Banners:** Create customized banners and display real-time server information dynamically in TeamSpeak.
- **Name Police:** Define what is allowed and what is not. Block forbidden nicknames and channel names effortlessly.
- **Multi-Server Management:** Scale made simple: manage multiple TeamSpeak servers from a single dashboard.
- **SSH Query Support:** Full support for secure SSH ServerQuery connections.

---

## Installation & Setup

### Requirements

- **Web Server:** Nginx or Apache
- **PHP:** 8.3 or newer with required extensions:
  - `curl`, `gd`, `ssh2`, `intl`, `mbstring`, `xml`, `bz2`, `zip`
  - Database driver: `pdo_mysql` (MySQL/MariaDB) or `pdo_pgsql` (PostgreSQL)
  - See [Laravel Deployment Documentation](https://laravel.com/docs/12.x/deployment)
- **Database:** MySQL 8.0+, MariaDB 10.3+, or PostgreSQL 13+
- **Composer:** v2.x
- **Node.js & npm:** v18.x or newer (*optional*, only needed if developing or rebuilding frontend assets)
- **Supervisor:** For running persistent bot instances and queue workers
- **Git**

### Installation Guide

For detailed step-by-step instructions, see the [Installation & Setup Guide](docs/installation.md).

---

## TeamSpeak Permissions

- **ServerAdmin Query Account:** Recommended for full administrative control and seamless bot operations.
- **Custom Bot Identity:** You can create a dedicated ServerQuery identity, but ensure it has sufficient permissions granted for the configured bot features (channel creation, client kicks/moves, group assignments, etc.).

All bot operations and ServerQuery interactions are tracked via the built-in logging system.
