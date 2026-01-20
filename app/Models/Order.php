<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_REVIEWING = 'reviewing';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_PART_SUCCESS = 'part_success';
    const REFUND_STATUS_FAILED = 'failed';
    const REFUND_STATUS_REJECTED = 'rejected';
    const REFUND_STATUS_CANCELLED = 'canceled';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已提交退款申请',
        self::REFUND_STATUS_REVIEWING  => '退款审核中',
        self::REFUND_STATUS_PROCESSING => '退款处理中',
        self::REFUND_STATUS_SUCCESS    => '全额退款成功',
        self::REFUND_STATUS_PART_SUCCESS    => '部分退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
        self::REFUND_STATUS_REJECTED     => '退款申请已驳回失败',
        self::REFUND_STATUS_CANCELLED     => '退款申请已取消',
    ];


    // 正向物流 - 未发货：订单未安排发货（原状态保留）
    const SHIP_STATUS_PENDING = 'pending';
    // 正向物流 - 待揽件：商家已下单，快递员未上门取件（新增：用户感知“已发货”前的状态）
    const SHIP_STATUS_WAITING_PICKUP = 'waiting_pickup';
    // 正向物流 - 运输中：快递已揽件，正在运往目的地（新增：核心进度，原版本缺失）
    const SHIP_STATUS_IN_TRANSIT = 'in_transit';
    // 正向物流 - 派件中：快递到达目的地，正在派件（新增：用户等待收货的关键状态）
    const SHIP_STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    // 正向物流 - 已收货：用户确认收货（原状态保留，优化命名更直观）
    const SHIP_STATUS_RECEIVED = 'received';

     // 逆向物流（退货）- 待发货：用户申请退货后，未寄出商品（新增：售后必备
    const SHIP_STATUS_RETURN_PENDING = 'return_pending';
    // 逆向物流（退货）- 待揽件：用户已下单退货，快递未上门（新增）
    const SHIP_STATUS_RETURN_WAITING_PICKUP = 'return_waiting_pickup';
    // 逆向物流（退货）- 运输中：用户已寄出，商品退回商家（新增）
    const SHIP_STATUS_RETURN_IN_TRANSIT = 'return_in_transit';
    // 逆向物流（退货）- 已签收：商家已收到退回商品（新增）
    const SHIP_STATUS_RETURN_RECEIVED = 'return_received';

    // 特殊状态 - 物流异常：如丢件、滞留、安检失败（新增：主流平台必备）
    const SHIP_STATUS_EXCEPTION = 'exception';

    // 物流状态映射表（前端展示用）
    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING => '未发货',
        self::SHIP_STATUS_WAITING_PICKUP => '待揽件',
        self::SHIP_STATUS_IN_TRANSIT => '运输中',
        self::SHIP_STATUS_OUT_FOR_DELIVERY => '派件中',
        self::SHIP_STATUS_RECEIVED => '已收货',
        self::SHIP_STATUS_RETURN_PENDING => '退货待发货',
        self::SHIP_STATUS_RETURN_WAITING_PICKUP => '退货待揽件',
        self::SHIP_STATUS_RETURN_IN_TRANSIT => '退货运输中',
        self::SHIP_STATUS_RETURN_RECEIVED => '退货已签收',
        self::SHIP_STATUS_EXCEPTION => '物流异常',
    ];

    // 在 Order 模型中新增
    const ORDER_STATUS_UNPAID = 'pending';
    const ORDER_STATUS_PAID = 'paid';
    const ORDER_STATUS_CANCELLED = 'cancelled';

    public static $orderStatusMap = [
        self::ORDER_STATUS_UNPAID => '待支付',
        self::ORDER_STATUS_PAID => '已支付',
        self::ORDER_STATUS_CANCELLED => '已取消',
    ];

    protected $fillable = [
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    protected $casts = [
        'closed' => 'boolean',
        'reviewed' => 'boolean',
        'address' => 'json',
        'ship_data' => 'json',
        'extra' => 'json',
    ];

    protected $dates = [
        'paid_at'
    ];

    // 状态合法性校验（避免非法装填写入数据库）
    public static function isValidRefundStatus(string $status): bool
    {
        return in_array($status, [
            self::REFUND_STATUS_PENDING,
            self::REFUND_STATUS_APPLIED,
            self::REFUND_STATUS_REVIEWING,
            self::REFUND_STATUS_PROCESSING,
            self::REFUND_STATUS_SUCCESS,
            self::REFUND_STATUS_PART_SUCCESS,
            self::REFUND_STATUS_FAILED,
            self::REFUND_STATUS_REJECTED,
            self::REFUND_STATUS_CANCELLED,
        ]);
    }

    // 校验物流状态是否合法
    public static function isValidShipStatus(string $status):bool {
        return in_array($status, [
            self::SHIP_STATUS_PENDING,
            self::SHIP_STATUS_WAITING_PICKUP,
            self::SHIP_STATUS_IN_TRANSIT,
            self::SHIP_STATUS_OUT_FOR_DELIVERY,
            self::SHIP_STATUS_RECEIVED,
            self::SHIP_STATUS_RETURN_PENDING,
            self::SHIP_STATUS_RETURN_WAITING_PICKUP,
            self::SHIP_STATUS_RETURN_IN_TRANSIT,
            self::SHIP_STATUS_RETURN_RECEIVED,
            self::SHIP_STATUS_EXCEPTION,
        ]);
    }

    // 自动执行
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建时间 在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的no字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成流水单号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if(!$model->no) {
                    \Log::warning('生成订单号失败（Order no generation failed）');
                    return false;
                }
            }

            // 初始化状态：新订单默认”未退款“"未退货"
            if(empty($model->refund_status)) {
                $model->refund_status = self::REFUND_STATUS_PENDING;
            }
            if (empty($model->ship_status)) {
                $model->ship_status = self::SHIP_STATUS_PENDING;
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    // 生成流水号
    public static function findAvailableNo() {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6位 的数字
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('订单号生成失败：10次尝试均已存在（Order no collision after 10 attempts）');

        return false;
    }

    // 订单关联优惠券
    public function couponCode() {
        return $this->belongsTo(CouponCode::class);
    }

}
