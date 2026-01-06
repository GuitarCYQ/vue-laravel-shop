<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request$request, Closure$next): Response
    {
       $origin =$request->header('Origin');
       $allowedOrigins = [
            'http://222.186.21.30:8688', // 前端地址
            // 其他允许的域名...
        ];

        // 只设置一个Origin头
        if (in_array($origin,$allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' .$origin);
        } else {
            header('Access-Control-Allow-Origin: http://222.186.21.30:8688'); // 兜底
        }

        // 允许ngrok自定义头
        header('Access-Control-Allow-Headers: Content-Type, Authorization, ngrok-skip-browser-warning');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        if ($request->isMethod('OPTIONS')) {
            return response()->json(['status' => 'success'], 204);
        }

        return$next($request);
    }
}
