<?php
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'dbname' => getenv('DB_NAME') ?: 'fastfood',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'port' => getenv('DB_PORT') ?: '3306'
    ],
    'app' => [
        'base_url' => getenv('BASE_URL') ?: 'http://localhost:8080',
        'jwt_secret' => getenv('JWT_SECRET') ?: 'cambiame_por_un_secreto_largo'
    ]
];
