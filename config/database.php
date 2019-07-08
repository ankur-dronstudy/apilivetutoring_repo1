<?php

$env = env('ENVIRONMENT', 'development');
if ($env === 'staging') {
    return [
        'fetch' => PDO::FETCH_CLASS,
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [

            'sqlite' => [
                'driver' => 'sqlite',
                'database' => storage_path('database.sqlite'),
                'prefix' => '',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => '127.0.0.1',
                ],
                'write' => [
                    'host' => '127.0.0.1'
                ],
                'database' => '',
                'username' => '',
                'password' => '',
                // 'username' => 'root',
                // 'password' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ],
            'sqlsrv' => [
                'driver' => 'sqlsrv',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
            ],
            'elastic' => [
                'protocol' => 'http://',
                'host' => '52.66.128.56',
                'port' => ':9200',
                'questionsData' => '/questions/data/',
                'quizData' => '/quiz/data/',
                'newsData' => '/news/data/',
                'cityData' => '/cities/data/',
                'collegeData' => '/college/data/',
                'learnData' => '/content/data/'
            ]
        ],
        'migrations' => 'migrations',
        'redis' => [

            'cluster' => false,
            'default' => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
        ]
    ];
} else if ($env === 'live') {
    return [
        'fetch' => PDO::FETCH_CLASS,
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [

            'sqlite' => [
                'driver' => 'sqlite',
                'database' => storage_path('database.sqlite'),
                'prefix' => '',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => '116.203.142.168',
                ],
                'write' => [
                    'host' => '116.203.142.168'
                ],
                'database' => 'apilivetutoring',
                'username' => 'myuser',
                'password' => 'mypass',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'prefix' => '',
                'strict' => false,
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ],
            'sqlsrv' => [
                'driver' => 'sqlsrv',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
            ],
            'elastic' => [
                'protocol' => 'http://',
                'host' => '52.66.128.56',
                'port' => ':9200',
                'questionsData' => '/questions/data/',
                'quizData' => '/quiz/data/',
                'newsData' => '/news/data/',
                'cityData' => '/cities/data/',
                'collegeData' => '/college/data/',
                'learnData' => '/content/data/'
            ]
        ],
        'migrations' => 'migrations',
        'redis' => [

            'cluster' => false,
            'default' => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
        ]
    ];
} else if ($env === 'development') {
    return [
        'fetch' => PDO::FETCH_CLASS,
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [

            'sqlite' => [
                'driver' => 'sqlite',
                'database' => storage_path('database.sqlite'),
                'prefix' => '',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => 'localhost',
                ],
                'write' => [
                    'host' => 'localhost'
                ],
                'database' => 'apilivetutoring',
                'username' => 'root',
                'password' => 'root',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ],
            'sqlsrv' => [
                'driver' => 'sqlsrv',
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'api_scholarspace'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
            ],
            'elastic' => [
                'protocol' => 'http://',
                'host' => '52.66.128.56',
                'port' => ':9200',
                'questionsData' => '/questions/data/',
                'quizData' => '/quiz/data/',
                'newsData' => '/news/data/',
                'cityData' => '/cities/data/',
                'collegeData' => '/college/data/',
                'learnData' => '/content/data/'
            ],
        ],
        'migrations' => 'migrations',
        'redis' => [

            'cluster' => false,
            'default' => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
                'driver'=>'redis'
            ],
        ]
    ];
}
