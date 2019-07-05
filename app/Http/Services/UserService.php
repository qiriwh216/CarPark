<?php

namespace App\Http\Services;
use App\Helpers\NewMicroAuthPrivilege;
use App\Helpers\ICEService;


class UserService extends BaseService
{
    protected $userInfo;

    protected $customer = [];

    protected $yunUserInfo = [];

    /**
     * @var ICEService
     */
    protected $iceService;

    /**
     * @var NewnewMicroAuthPrivilege
     */
    protected $newMicroAuthPrivilege;

    const USER_INFO_CACHE_KEY = 'user:info:access_token:';
    const YUN_INFO_CACHE_KEY = 'yun:info:access_token:';

    /**
     * UserService constructor.
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



}