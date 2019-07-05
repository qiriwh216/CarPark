<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{
    use Helpers;

    protected $validateMessages = [
        'required' => ':attribute 不能为空',
        'max' => ':attribute 超出允许最大值',
        'min' => ':attribute 超出允许最小值',
        'in' => ':attribute 无效',
        'numeric' => ':attribute 须要为数值',
        'array' => ':attribute 期望值为数组',
        'date_format' => ':attribute 时间格式为 2017-02-07',
    ];

    public function errorResponse($statusCode, $message = null, $code = 0)
    {
        throw new HttpException($statusCode, $message, null, [], $code);
    }
}
