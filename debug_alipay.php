<?php
// 支付宝配置调试脚本

require_once __DIR__ . '/vendor/autoload.php';

// 加载Laravel环境
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 支付宝配置检查 ===\n";

// 1. 检查环境变量
echo "1. 环境变量检查:\n";
echo "ALIPAY_APP_ID: " . env('ALIPAY_APP_ID', '未设置') . "\n";
echo "ALIPAY_SECRET_CERT: " . (env('ALIPAY_SECRET_CERT') ? '已设置' : '未设置') . "\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "NGROK_URL: " . env('NGROK_URL', '未设置') . "\n";

// 2. 检查配置文件
echo "\n2. 配置文件检查:\n";
$config = config('pay');
echo "支付模式: " . ($config['alipay']['default']['mode'] ?? '未设置') . "\n";
echo "APP_ID: " . ($config['alipay']['default']['app_id'] ?? '未设置') . "\n";
echo "回调地址: " . ($config['alipay']['default']['notify_url'] ?? '未设置') . "\n";
echo "返回地址: " . ($config['alipay']['default']['return_url'] ?? '未设置') . "\n";

// 3. 检查证书文件
echo "\n3. 证书文件检查:\n";
$certPaths = [
    'app_public_cert_path' => $config['alipay']['default']['app_public_cert_path'] ?? '',
    'alipay_public_cert_path' => $config['alipay']['default']['alipay_public_cert_path'] ?? '',
    'alipay_root_cert_path' => $config['alipay']['default']['alipay_root_cert_path'] ?? ''
];

foreach ($certPaths as $name => $path) {
    $exists = file_exists($path);
    echo "$name: " . ($exists ? '存在' : '不存在') . " ($path)\n";
}

// 4. 尝试创建支付宝实例
echo "\n4. 支付宝实例创建测试:\n";
try {
    $alipay = app('alipay');
    echo "支付宝实例创建成功\n";
    
    // 5. 测试支付请求
    echo "\n5. 测试支付请求:\n";
    $orderData = [
        'out_trade_no' => 'TEST_' . time(),
        'total_amount' => '0.01',
        'subject' => '测试订单',
    ];
    
    $response = $alipay->web($orderData);
    echo "支付请求创建成功\n";
    echo "响应类型: " . get_class($response) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "详细信息: " . $e->getTraceAsString() . "\n";
}

echo "\n=== 检查完成 ===\n";