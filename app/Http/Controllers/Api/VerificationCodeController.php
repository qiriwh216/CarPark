<?php

namespace App\Http\Controllers\Api;

use App\Models\SmsCode;
use Illuminate\Http\Request;

class VerificationCodeController extends Controller
{

    protected $attributes =
        [
            'mobile' => '手机号码',
            'code' => '短信验证码'
        ];

    /**
     * 获取验证码接口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getSMSCode(Request $request)
    {
        $this->validate(
            $request,
            [
                'mobile' => 'bail|required|digits:11',
            ],
            $this->validateMessages,
            $this->attributes
        );

        $mobile = $request->input('mobile');

        //同一手机号，一天内验证码发送超过5次,toast提示：验证码发送太多频繁，请明日再试
        $todaySendCodeNum = SmsCode::getTodaySendCodeNumByMobile($mobile);
        if ($todaySendCodeNum >= SMS_CAN_NUM_EVERYDAY) {
            $this->response->error('验证码发送太多频繁，请明日再试', 422);
        }

        $nearRecord = SmsCode::getNearByMobile($mobile);
        if ($nearRecord->create_time > time() - SMS_INTERVAL_TIME) {
            $this->response->error('验证码发送太多频繁，请稍后再试', 422);
        }

        //发送短信验证码
        $result = app('newMicroService')->getSMSCode($mobile);

        if ($result['ok'] == 1) {
            //发送成功 记录到数据库

            //TODO 拿到发送验证码的小程序用户信息
            $userInfo = [
                'user_id' => 1001, //模拟数据
                'openid' => 'openidcxjkqo5df1ghlnvssa',
                'unionid' => 'unionidkdfsdkl55mvnape4d',
            ];

            SmsCode::create([
                'user_id' => $userInfo['user_id'],
                'openid' => $userInfo['openid'],
                'unionid' => $userInfo['unionid'],
                'mobile' => $mobile,
                'status' => 0,
                'year_month_date' => date("Ymd"),
                'create_time' => time(),
            ]);

            return response()->json(['message' => '发送成功'])->setStatusCode(201);
        } else {
            //发送失败
            $this->response->error($result['message'], 500);
        }
    }

}