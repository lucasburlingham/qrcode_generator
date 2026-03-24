<?php

return [
    // Postgre or MySQL would be safer for production; SQLite is acceptable for simple self-hosted demos.
    'db_path' => getenv('SHORTENER_DB_PATH') ?: '/var/qrdata/links.sqlite',

    // If SHORTENER_CANONICAL_HOST is set, this is used to build short URLs to avoid Host header spoofing.
    'canonical_host' => getenv('SHORTENER_CANONICAL_HOST') ?: 'localhost',

    // Strong policy: only secure web schemes by default.
    'allowed_schemes' => ['https', 'http', 'ws', 'wss'],

    // API key for privileged operations (GET /api/links listing or DELETE).
    'api_key' => getenv('SHORTENER_API_KEY') ?: '',

    // Maximum URL length.
    'max_url_length' => 2048,

    // link code length can be increased to reduce enumeration.
    'code_length' => 4,
    'code_charset' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
];
