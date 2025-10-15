# AutoBackup Readme

## Publish config file

```bash
php artisan vendor:publish --tag="componist_backup"
```

## Add your .env file
```
BACKUP_NOTIFY_EMAIL="your@mail.com"
```

## Run command backup now
php artisan queue:work

```bash
php artisan componist:db-backup
```

## Config file
```php
return [
    'path' => storage_path('backups'),
    'max_backups' => 7,

    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),

    // mail adress for Notification
    'notify_email' => env('BACKUP_NOTIFY_EMAIL', 'admin@deine-domain.de'),

    'settings' => [
        'send_mail_by_success' => true,
    ],
    'backup_daily_at' => '01:00'
];
```