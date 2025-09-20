<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Embedding Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default embedding provider that will be used
    | by the framework when generating embeddings.
    |
    */

    'default' => env('EMBEDDING_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Embedding Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the embedding providers for your application.
    | These providers are used to generate vector embeddings for text.
    |
    */

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'dimensions' => 1536,
            'rate_limit' => 3000, // requests per minute
        ],

        'cloudflare' => [
            'driver' => 'cloudflare',
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
            'model' => env('CLOUDFLARE_EMBEDDING_MODEL', '@cf/baai/bge-base-en-v1.5'),
            'dimensions' => 768,
            'rate_limit' => 1000,
        ],

        'cohere' => [
            'driver' => 'cohere',
            'api_key' => env('COHERE_API_KEY'),
            'model' => env('COHERE_EMBEDDING_MODEL', 'embed-english-v3.0'),
            'dimensions' => 1024,
            'rate_limit' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers
    |--------------------------------------------------------------------------
    |
    | These providers will be used as fallbacks if the primary provider fails.
    |
    */

    'fallback_providers' => ['cloudflare', 'cohere'],

    /*
    |--------------------------------------------------------------------------
    | Health Check Interval
    |--------------------------------------------------------------------------
    |
    | The interval in seconds between health checks for embedding providers.
    |
    */

    'health_check_interval' => 300, // seconds

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | The time in seconds to cache embeddings and health check results.
    |
    */

    'cache_ttl' => 3600, // seconds

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | The maximum number of retries for embedding generation.
    |
    */

    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | The delay in seconds between retries, indexed by attempt number.
    |
    */

    'retry_delay' => [30, 120, 300], // seconds
];