<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    const PAY_STATUS_PENDING = 'pending';
    const PAY_STATUS_APPLIED = 'applied';
    const PAY_STATUS_PROCESSING = 'processing';
    const PAY_STATUS_SUCCESS = 'success';
    const PAY_STATUS_FAILED = 'failed';

    public static $payStatusMap = [
        self::PAY_STATUS_PENDING => '未支付',
        self::PAY_STATUS_APPLIED => '已申请支付',
        self::PAY_STATUS_PROCESSING => '支付中',
        self::PAY_STATUS_SUCCESS => '支付成功', 
        self::PAY_STATUS_FAILED => '支付失败'

    ];
    
    protected $guarded = [];
    protected $casts = [
        'closed'=>'boolean'
    ];
    protected $dates = [
        'paid_at',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find order no failed');

        return false;
    }

    public function user(){
       return $this->belongsTo(User::class); 
    }
    //饭票劵
    public function couponCode()
    {
        return $this->belongsTo(CouponCode::class);
    }
}
