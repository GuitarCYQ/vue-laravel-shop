<?php

return [
    'paths' => ['api/*', 'v1/*', 'login/*'], // ✅ 匹配你的接口路径
    'allowed_methods' => ['*'], // 允许所有请求方法
    'allowed_origins' => [ #允许的域名
        'http://localhost:5173', // 本地前端
        'http://222.186.21.30:8688', // 线上前端
        'http://103.242.14.249', // 线上前端
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // 允许所有请求头
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true, // ✅ 允许携带cookie/token
];
