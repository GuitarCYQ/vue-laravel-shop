<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Yansongda\Pay\Exception\PayException;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Events\OrderPaid;

class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    { 
        // 检查订单状态
        if ($order->paid_at || $order->closed) {
            return response()->json([
                'code' => 400,
                'message' => '订单状态不正确，无法支付',
                'data' => null
            ]);
        }

        try {
            // 生成支付宝支付
            $result = app('alipay')->web([
                'out_trade_no' => $order->no,          // 使用订单号
                'total_amount' => $order->total_amount, // 订单总金额
                'subject' => '支付 Larabbs 订单：' . $order->no,
                'notify_url' => ngrok_url('api.v1.payment.alipay.notify'), // 异步通知地址
                'return_url' => 'http://localhost:5173/payment/alipay/return',           // 同步跳转地址
            ]);

            // 调试：检查返回对象类型
            // \Log::info('Result type: ' . get_class($result));
            
            // 处理Guzzle PSR7 Response对象
            if ($result instanceof \Psr\Http\Message\ResponseInterface) {
                // 对于PSR7 Response，使用 getBody() 方法
                $htmlContent = $result->getBody()->getContents();
            }
            // 处理Laravel Response对象
            elseif (method_exists($result, 'getContent')) {
                $htmlContent = $result->getContent();
            }
            // 处理其他情况
            else {
                // 如果直接返回了字符串或HTML内容
                $htmlContent = (string) $result;
            }
            
            // \Log::info('HTML content length: ' . strlen($htmlContent));
            
            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => [
                    'form' => $htmlContent, // 返回HTML表单内容
                    'pay_url' => null,
                    'order_id' => $order->id,
                    'order_no' => $order->no,
                    'amount' => $order->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('PayByAlipay error: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => '生成支付链接失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    // 前端回调页面
    public function alipayReturn() 
    {
        $data = app('alipay')->callback();
    }

    // 服务器端回调
    public function alipayNotify()
    {
        $data = app('alipay')->callback();
        // \Log::debug('Alipay notify 支付回调', $data->all());

        // 如果订单状态不算成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // $data->out_trade_no 拿到订单流水号，不在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        //正常来说不太可能出现一笔不存在的订单，这步是为了健壮代码
        if(!$order) {
            return 'fail';
        }

        // 如果这笔订单已经支付
        if($order->paid_at) {
            // 返回数据给支付宝，支付宝得到这个返回后就知道我们已经处理好这笔订单了，不会再发生这笔订单的回调了。
            return app('alipay')->success();
        }

        // 修改订单的状态
        $order->update([
            'status' => 'paid', // 订单状态
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no' => $data->trade_no, // 支付宝订单号
            'refund_status' => Order::ORDER_STATUS_PAID, // 状态修改成已支付
        ]);

        // 支付完成后调用事件
        $this->afterPaid($order);

        // 告诉支付宝订单完成了
        return app('alipay')->success();
    }

    // 
    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
    

    // 验证订单状态
    public function verifyPayStatus(Request $request) 
    {
        // 验证参数
        $outTradeNo = $request->input('out_trade_no');
        if (!$outTradeNo) {
            return response()->json([
                'success' => false,
                'message' => '商户订单号不能为空',
            ]);
        }

        try {
            // 获取支付宝实例
            $alipay = app('alipay');
            // 调用支付宝官方接口：查询交易状态
            $result = $alipay->query([
                'out_trade_no' => $outTradeNo,
            ]);

            // 解析支付宝返回的状态
            $tradeStatus = $result->trade_status ?? '';
            if($tradeStatus === 'TRADE_SUCCESS') {
                // 支付成功：更新本地订单状态
                $orderId = $this->updateOrderStatus($outTradeNo, $result);
                return response()->json([
                    'success' => true,
                    'msg' => '支付成功',
                    'data' => [
                        'data' => $result,
                        'id' => $orderId,
                    ] // 可选：返回交易详情
                ]);
            } else {
                // 支付未成功（待支付/交易关闭）
                return response()->json([
                    'success' => false,
                    'msg' => '支付状态：' . $tradeStatus,
                    'data' => $result
                ]);
            }

        }catch (\Exception $e){ 
            \Log::error('支付宝支付状态查询失败：', [
                'out_trade_no' => $outTradeNo,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'msg' => '查询失败：' . $e->getMessage()
            ]);
        }
    }

    // 辅助方法：更新本地订单状态
    private function updateOrderStatus(string $outTradeNo, $alipayResult) {
        $order = Order::where('no', $outTradeNo)->first();
        if($order && !$order->paid_at) {
            // 先记录日志，方便调试
            \Log::info('更新订单支付状态', [
                'order_no' => $outTradeNo,
                'alipay_trade_no' => $alipayResult->trade_no ?? '',
            ]);
            
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'alipay',
                'payment_no' => $alipayResult->trade_no ?? '',
                'refund_status' => Order::ORDER_STATUS_PAID, // 状态修改成已支付
            ]);
            
            // 触发支付完成事件
            $this->afterPaid($order);
        }
        return $order->id;
    }

}
