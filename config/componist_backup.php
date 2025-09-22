<?php

return [
    'path' => storage_path('backups'),
    'max_backups' => 7,

    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),

    // Empfänger-Adresse für Benachrichtigungen
    'notify_email' => env('BACKUP_NOTIFY_EMAIL', 'admin@deine-domain.de'),
];
