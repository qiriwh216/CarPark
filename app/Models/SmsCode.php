<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 短信发送记录模型
 *
 * Class SmsCode
 * @package App\Models
 */
class SmsCode extends Model
{

    protected $table = 'sms_code';

    /**
     * 获取今日单个手机号发送的短信数量
     * @param $mobile
     * @return mixed
     *
     */
    public static function getTodaySendCodeNumByMobile($mobile)
    {
        return self::where('mobile', $mobile)->where('year_month_date', date('Ymd'))->count();
    }

    /**
     * 获取最近一条发送短信的记录
     *
     * @param $mobile
     * @return mixed
     */
    public static function getNearByMobile($mobile)
    {
        return self::where('mobile', $mobile)->orderBy('create_time', 'desc')->first();
    }

}
