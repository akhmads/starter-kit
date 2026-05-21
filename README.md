## About

Laravel and livewire starter-kit

## Tech Stack

-   Laravel
-   Livewire
-   Tailwind CSS
-   Daisy UI
-   Mary UI

## Requirements

-   PHP 8.3+
-   Composer 2+
-   Node JS 22+
-   PostgreSQL 15+
-   Supervisor

## Installation

Clone the repository then go into the application folder

```
git clone https://github.com/akhmads/livewire4

cd livewire4
```

Install composer and node dependencies

```
composer install

npm install
```

Setup your environment

```
cp .env.example .env
```

Generate Key

```
php artisan key:generate
```

Build assets

```
npm run build
```

Run migration

```
php artisan migrate
```

Link storage to public

```
php artisan storage:link
```

Change directory permission (for linux server)

```
sudo usermod -aG www-data $USER
sudo chown -R $USER:www-data /var/www/your-app
sudo find /var/www/your-app -not -path '*/node_modules/*' -not -path '*/.git/*' -type d -exec chmod 755 {} +
sudo find /var/www/your-app -not -path '*/node_modules/*' -not -path '*/.git/*' -type f -exec chmod 644 {} +

sudo chmod -R 775 /var/www/your-app/storage
sudo chmod -R 775 /var/www/your-app/bootstrap/cache
```

Run server

```
php artisan serve
```

## Running Queue and Scheduler

On local environment

```
php artisan schedule:work

php artisan queue:listen --timeout=1800
```

Scheduler on cron jobs

```
* * * * * cd /var/www/your-app && php artisan schedule:run >> /dev/null 2>&1
```

Queue on supervisor

```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-app/artisan queue:work --queue=default --timeout=1800
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/your-app/laravel-worker.log
stopwaitsecs=1800
```