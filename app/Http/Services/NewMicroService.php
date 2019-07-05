<?php

namespace App\Http\Services;
use App\Helpers\NewMicroAuthPrivilege;
use App\Helpers\ICEService;


class NewMicroService extends BaseService
{

    /**
     * @var ICEService
     */
    protected $iceService;

    /**
     * @var NewnewMicroAuthPrivilege
     */
    protected $newMicroAuthPrivilege;

    /**
     * NewMicroService constructor.
     * @param $iceService
     */
    public function __construct(ICEService $iceService, NewMicroAuthPrivilege $newMicroAuthPrivilege)
    {
        $this->iceService = $iceService;
        $this->newMicroAuthPrivilege = $newMicroAuthPrivilege;
    }


    /**
     * 根据手机号获取用户信息
     * @param $mobile
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserByMobile($mobile)
    {
        if (!$mobile) {
            return '';
        }

        $result = $this->iceService->dispatch(
            'czyuser/czy/userInfoByMobile',
            [
                'mobile' => $mobile,
                'access_token' => $this->newMicroAuthPrivilege->getAccessToken(),
            ],
            [],
            'GET'
        );

        return $result;
    }

    /**
     * 查询用户uuid
     * @param $mobile
     * @return mixed
     * @throws \Exception
     */
    public function queryUserUuid($mobile)
    {
        $result = $this->iceService->dispatch(
            'czyuser/czy/unionUserQuery',
            [
                'mobile' => $mobile,
                'access_token' => $this->newMicroAuthPrivilege->getAccessToken(),
            ],
            [],
            'GET'
        );

        return array_get($result, 'uuid', '');
    }

    /**
     * 根据用户uuid获取用户信息
     * @param $uuid
     * @return mixed
     * @throws \Exception
     */
    public function getUserByUuid($uuid)
    {
        $result = $this->iceService->dispatch(
            'czyuser/czy/userInfoByUuid',
            [
                'uuid' => $uuid,
                'access_token' => $this->newMicroAuthPrivilege->getAccessToken(),
            ],
            [],
            'GET'
        );

        return $result;
    }

    /**
     * 调用ICE微服务短信接口
     * @param $mobile
     * @return mixed
     * @throws \Exception
     */
    public function getSMSCode($mobile)
    {
        $result = $this->iceService->dispatch(
            'czyprovide/verification/getCode',
            [
                'mobile' => $mobile,
                'template' => '彩车位提醒您，您的验证码是{code},请妥善保管！',
            ],
            [],
            'POST'
        );

        return $result;
    }

    /**
     * 验证ICE短信验证码是否正确
     * @param $mobile
     * @param $code
     * @return mixed
     * @throws \Exception
     */
    public function checkSMSCode($mobile, $code)
    {
        $result = $this->iceService->dispatch(
            'czyprovide/verification/checkCode',
            [
                'mobile' => $mobile,
                'code' => $code,
            ],
            [],
            'PUT'
        );

        return $result;
    }



}