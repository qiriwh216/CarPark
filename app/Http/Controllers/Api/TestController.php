<?php

namespace App\Http\Controllers\Api;


use App\Helpers\NewMicroAuthPrivilege;
use App\Http\Services\UserService;

class TestController extends Controller
{
    public function test(){
//        var_dump((new NewMicroAuthPrivilege())->getAccessToken());
        var_dump(app('userService')->getUserByMobile('13212629412'));
    }

}
