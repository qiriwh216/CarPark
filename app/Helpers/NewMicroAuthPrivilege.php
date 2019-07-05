<?php


namespace App\Helpers;



class NewMicroAuthPrivilege
{
    private static $_instance;
    private $appAccessToken;
    private $interface;


    public function __construct()
    {
        $this->corpUuid = 'a8c58297436f433787725a94f780a3c9';//租户UUID
        $this->appUuid = env('ICE_APP_ID');
        $this->appSecret = env('ICE_CLIENT_SECRET');
        $this->serviceUuid = env('AUTH_SERVICE_UUID');
        $this->serviceSecret = env('AUTH_SERVICE_SECRET');
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 获取应用授权
     * @return mixed
     */
    public function getAppAuth()
    {
        return app('cache')
            ->remember(
                'Micro:Privilege:app:auth:businness',
                cache_minute_random(),
                function () {
                    $ts = time()*1000;
                    return app('ICEService')->dispatch(
                        '/authms/auth/app',
                        [],
                        [
                            'corp_uuid' => $this->corpUuid,
                            'app_uuid' => $this->appUuid,
                            'signature' => md5($this->appUuid . $ts . $this->appSecret),
                            'timestamp' => $ts
                        ],
                        'POST'
                    );
                }
            );
    }

    /**
     * 获取本微服务的权限服务access_token
     * @return $this|bool
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $cacheKey = 'Micro:Privilege:app:auth:businness';
        $token = app('cache')->get($cacheKey);
        if ($token['accessToken']) {
            return $token['accessToken'];
        }

        $accessToken = $this->flush()->getAppAuth();
        if($accessToken['accessToken']){
            return $accessToken['accessToken'];
        }
        return false;
    }



    /**
     * 获取权限列表
     * @return bool
     * @throws \Exception
     */
    public function getAccessPriliveges()
    {
        return app('cache')
            ->tags('newMicro:newPrivilege')
            ->remember(
                'Micro:newPrivilege:app:access_token:privileges:business' . $this->appAccessToken,
                cache_minute_random(),
                function () {
                    $privilege = app('ICEService')->dispatch(
                        '/authms/app/privilege',
                        [
                            'service_token' => $this->getServiceAccessToken(),
                            'app_token' => $this->appAccessToken
                        ],
                        [],
                        'GET'
                    );
                    return $privilege;
                }
            );
    }

    public function authMicroRequest($accessToken, $interface = '' , $request_method = 'GET')
    {
        if (!$accessToken) {
            throw new \Exception('无法获取第三方调用方access_token', 9004);
        };
        $this->appAccessToken = $accessToken;
        $this->interface = $interface;

        $privileges_ = $this->getAccessPriliveges();
        $privileges =  isset($privileges_['privileges']) && $privileges_['privileges'] ? $privileges_['privileges'] : array();
        $flag = false;
        if(!empty($privileges))
        {
            foreach($privileges as $key=>$value)
            {
                if($value['url'] == $interface && $value['method'] == $request_method)
                {
                    $flag = true;
                }
            }
        }else{
            throw new \Exception('缺少接口访问权限: ' . $interface, 9005);
        }
        return $flag;
    }

    /**
     * 获取服务的access_token
     * @return $this
     */
    public function getServiceAccessToken()
    {
        $cacheKey = 'new:micro:newPrivilege:app:Service:privileges:business';
        $token = app('cache')
            ->remember(
                $cacheKey,
                cache_minute_random(),
                function () {
                    $ts = time();
                    return app('ICEService')->dispatch(
                        '/authms/auth/service',
                        [],
                        [
                            'service_uuid' => $this->serviceUuid,
                            'signature' => md5($this->serviceUuid . $ts . $this->serviceSecret),
                            'timestamp' => $ts
                        ],
                        'POST'
                    );
                }
            );

        if ($token['accessToken']) {
            return $token['accessToken'];
        }
        return false;
    }


    protected function flush()
    {
        app('cache')->tags('newMicro:newPrivilege')->flush();
        return $this;
    }

}