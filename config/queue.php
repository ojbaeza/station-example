<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis",
    |          "deferred", "background", "failover", "null"
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        // Station RabbitMQ Driver
        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'hosts' => [
                [
                    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                    'port' => (int) env('RABBITMQ_PORT', 5672),
                    'username' => env('RABBITMQ_USER', 'station'),
                    'password' => env('RABBITMQ_PASSWORD', 'station'),
                    'vhost' => env('RABBITMQ_VHOST', 'station'),
                ],
            ],
            'options' => [
                'ssl_options' => [
                    'cafile' => env('RABBITMQ_SSL_CAFILE'),
                    'local_cert' => env('RABBITMQ_SSL_LOCALCERT'),
                    'local_key' => env('RABBITMQ_SSL_LOCALKEY'),
                    'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                    'passphrase' => env('RABBITMQ_SSL_PASSPHRASE'),
                ],
                'heartbeat' => 60,
                'connection_timeout' => 10.0,
                'read_write_timeout' => 30.0,
            ],
            'exchange' => [
                'name' => env('RABBITMQ_EXCHANGE', 'station.direct'),
                'type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
                'durable' => true,
                'auto_delete' => false,
            ],
            'management_url' => env('RABBITMQ_MANAGEMENT_URL'),
            'management_user' => env('RABBITMQ_MANAGEMENT_USER'),
            'management_password' => env('RABBITMQ_MANAGEMENT_PASSWORD'),
        ],

        // Station Redis Driver
        'redis' => [
            'driver' => 'station-redis',
            'connection' => env('STATION_REDIS_CONNECTION', 'default'),
            'queue' => env('STATION_REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('STATION_REDIS_RETRY_AFTER', 90),
            'block_for' => (int) env('STATION_REDIS_BLOCK_FOR', 5),
            'after_commit' => false,
        ],

        // Station SQS Driver (for LocalStack)
        'sqs' => [
            'driver' => 'station-sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'token' => env('AWS_SESSION_TOKEN'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'prefix' => env('SQS_PREFIX', 'http://127.0.0.1:4566/000000000000'),
            'suffix' => env('SQS_SUFFIX'),
            'queue' => env('SQS_QUEUE', 'default'),
            'endpoint' => env('SQS_ENDPOINT', 'http://127.0.0.1:4566'),
            'wait_time' => (int) env('SQS_WAIT_TIME', 20),
            'visibility_timeout' => (int) env('SQS_VISIBILITY_TIMEOUT', 30),
            'fifo' => (bool) env('SQS_FIFO', false),
            'message_group_id' => env('SQS_MESSAGE_GROUP_ID', 'default'),
            'content_based_deduplication' => (bool) env('SQS_CONTENT_DEDUP', false),
            'after_commit' => false,
        ],

        // Station Beanstalkd Driver
        'beanstalkd' => [
            'driver' => 'station-beanstalkd',
            'host' => env('BEANSTALKD_HOST', '127.0.0.1'),
            'port' => (int) env('BEANSTALKD_PORT', 11300),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'timeout' => (int) env('BEANSTALKD_TIMEOUT', 10),
            'ttr' => (int) env('BEANSTALKD_TTR', 60),
            'reserve_timeout' => (int) env('BEANSTALKD_RESERVE_TIMEOUT', 5),
            'priority' => (int) env('BEANSTALKD_PRIORITY', 1024),
            'retry_delay' => (int) env('BEANSTALKD_RETRY_DELAY', 60),
            'after_commit' => false,
        ],

        // Station Kafka Driver
        'kafka' => [
            'driver' => 'station-kafka',
            'brokers' => env('KAFKA_BROKERS', '127.0.0.1:9092'),
            'queue' => env('KAFKA_TOPIC', 'default'),
            'group_id' => env('KAFKA_GROUP_ID', 'station'),
            'auto_offset_reset' => env('KAFKA_AUTO_OFFSET_RESET', 'earliest'),
            'auto_commit' => (bool) env('KAFKA_AUTO_COMMIT', false),
            'consume_timeout' => (int) env('KAFKA_CONSUME_TIMEOUT', 5000),
            'flush_timeout' => (int) env('KAFKA_FLUSH_TIMEOUT', 10000),
            'session_timeout' => (int) env('KAFKA_SESSION_TIMEOUT', 30000),
            'heartbeat_interval' => (int) env('KAFKA_HEARTBEAT_INTERVAL', 3000),
            'message_timeout' => (int) env('KAFKA_MESSAGE_TIMEOUT', 30000),
            'acks' => (int) env('KAFKA_ACKS', -1),
            'idempotent' => (bool) env('KAFKA_IDEMPOTENT', false),
            'security_protocol' => env('KAFKA_SECURITY_PROTOCOL'),
            'sasl_mechanisms' => env('KAFKA_SASL_MECHANISMS'),
            'sasl_username' => env('KAFKA_SASL_USERNAME'),
            'sasl_password' => env('KAFKA_SASL_PASSWORD'),
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
