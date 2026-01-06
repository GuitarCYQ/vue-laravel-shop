<?php

use Yansongda\Pay\Pay;

return [
    'alipay' => [
        'default' => [
            // 「必填」支付宝分配的 app_id
            'app_id' => '9021000157664153',
            // 「必填」应用私钥 字符串或路径
            // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
            'app_secret_cert' => 'MIIEowIBAAKCAQEAn5TKQMWRGzCOaHmLt/i24pUJyVrOjubTaM/YSJyQ6g/7DKyJDaQBR5FuBojLk4jG6FWRcwR7D1spWZCq0g7wh8bKAV8FNG+n2gxvonM4ou+UDc6SHVlK00dbRuJyc2fC7clfyhz7ULtbcUBz04DZH5G0lOGzXptZ+rjfKGbGkd+OpBP2tbMBf9ObmSjMYT1FyN70Ye0MHyV/pGJs9Xea0TsV4lwX+6o8LqUYocq+wohdeOi60DWZ7L3KtJQ8ZMY+4MMopdWa9O7mfALavd8mqqkrV5pihqF0yqUIhaAifBvUDQPHBtxrms9NotjWd2r9YcKGiQE2WZ/7QgcGML3NZwIDAQABAoIBAHTvW5FA9EZ/OysVPauOhbzcxzIT515RiEK6Yts6VvocwbKUz0z+i618xKnnHX27cT2YTh+A/gfPnioyVunb5qcnpKiCj1bQJqgiCr94PN0wP0Yqw2jcIHdXR2xWzNUCCJWjxRxeRk3vLF99giJOvUtjwnaCOKSvo6yUQXnibck/Y/6E6oZNsj3H0iE6O3BtkTvpyE1Pw3ocS137X8Fyg2hlCLP5fLRGBN5FNlYTzS7Gtf/VQHfdds0wWnrjOtAR7XjUAm6HIFKyVNRAcM9PCrgJfwRGCVPBsi1kEb/tSVJAulH5tl0bAYieM1zzQLMj+/sMomZD7G8c0aaoM729d4kCgYEA5rsRQmwE/pwCbWEvfmWk6wFNJQgxOpZwsZUi2jta0DYdLoxUYmREscxM1zHL32HqTcUZkyOzdrJInaeaJiKfXiPnW372A6VwZcmEkZVhrcX334vUBfEjWw9hgevTDr8TTJN28TWizuXUibPalGdjDeXugmZmx2xowuDuEJ7FXPMCgYEAsQ7sbTuRZ0bPMVDJZLL1OVIvi+vGfIvLRCq1Y911nRecBic67d6k29pYcbz17RcmMdO+m0w+2VW+wiwwrllpVBd9bIM7Q+f2K6sPjfQFIpUPa94AIMuH0v82wgBt1gl/bICtXf41oyYGhRPaLQZ4IbnixlHq4xQqlmlkmdPqmr0CgYEApEvyx1R53f+jbtW0ZxHj9H/RuhvySiDUEG/9CF8ZlIfkEcBVWlbB38V1tgq8fM2RdV1zj6XtZgkf0juFdcBTsUpptWpbrk8BEUoE/1Ha403D6sf761tBbNcv/L67ZJXFHVMocCxcSW25AXEzpVdVXpErXGmkqD7W/mYCv4z/RucCgYBeINnuRGHLruhNhm5au40E2T1vvHu8ELGSXsYp/j0ds8R9vPNmLBwmyVCFvvhbABiNGs3VT9aFwkD0h3ul5ODLFOXX/3hNZI8JrhpJ1DW6qdYV+WLGDI37zTQ2NPYveMMmUs5YDuJDBt8mdmRh1XOeMm1uE3S1Hi554jPaV7/8XQKBgFtGNS9xG3zDDeGHTfTUQjpTm6zqMjbj7Hzc2qKh36xgAv/DTNZZFwucao8NqeV2DMpAxWeRpQuj/DAQQ8Ik7GQISydHBIAIN25J8qbiFTSP0pQuaxVh92wly6NHwqFQrlLzjiqt9K+bYQop9K3wkDw8ze+Id6nTq7wFd1VXI65E',
            // 「必填」应用公钥证书 路径
            // 设置应用私钥后，即可下载得到以下3个证书
            'app_public_cert_path' => storage_path('cert/appPublicCert.crt'),
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => storage_path('cert/alipayPublicCert.crt'),
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path' => storage_path('cert/alipayRootCert.crt'),
            'return_url' => '',
            'notify_url' => '',
            // 「选填」第三方应用授权token
            'app_auth_token' => '',
            // 「选填」服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id' => '',
            // 「选填」默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    // 由于还没做到教程中对接微信的章节，所以先留空了
    'wechat' => [
        'default' => [
            // 必填-商户号，服务商模式下为服务商商户号
            // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
            'mch_id' => 'xxx',
            // 选填-v2商户私钥
            'mch_secret_key_v2' => '',
            // 必填-v3 商户秘钥
            // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
            'mch_secret_key' => '',
            // 必填-商户私钥 字符串或路径
            // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_key.pem
            'mch_secret_cert' => '',
            // 必填-商户公钥证书路径
            // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_cert.pem
            'mch_public_cert_path' => '',
            // 必填-微信回调url
            // 不能有参数，如?号，空格等，否则会无法正确回调
            'notify_url' => 'https://yansongda.cn/wechat/notify',
            // 选填-公众号 的 app_id
            // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
            'mp_app_id' => 'xxx',
            // 选填-小程序 的 app_id
            'mini_app_id' => '',
            // 选填-app 的 app_id
            'app_id' => '',
            // 选填-合单 app_id
            'combine_app_id' => '',
            // 选填-合单商户号
            'combine_mch_id' => '',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id' => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id' => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id' => '',
            // 选填-服务商模式下，子商户id
            'sub_mch_id' => '',
            // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
            ],
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ]
    ],
    // 银联支付
    // 'unipay' => [
    //     'default' => [
    //         // 必填-商户号
    //         'mch_id' => '',
    //         // 必填-商户公私钥
    //         'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
    //         // 必填-商户公私钥密码
    //         'mch_cert_password' => '000000',
    //         // 必填-银联公钥证书路径
    //         'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
    //         // 必填
    //         'return_url' => 'https://yansongda.cn/unipay/return',
    //         // 必填
    //         'notify_url' => 'https://yansongda.cn/unipay/notify',
    //     ],
    // ],
    'logger' => [
        'enable' => true,
        'file' => storage_path('logs/alipay.log'),
        'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'daily', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
];
