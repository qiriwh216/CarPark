<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' =>['serializer:array', 'bindings', 'force-json']
], function ($api) {
	$api->group([
		'middleware' => 'api.throttle',
		'limit' => config('api.rate_limits.sign.limit'),
		'expires' => config('api.rate_limits.sign.expires'),
	], function ($api) {

		$api->get('test', 'UserController@test');

		// 小程序登录
		$api->post('weapp/authorizations', 'AuthorizationsController@weappStore');
		// 小程序注册
		$api->post('weapp/register', 'UsersController@weappStore');

		// 刷新token
		$api->put('authorizations/current', 'AuthorizationsController@update');
		// 删除token
		$api->delete('authorizations/current', 'AuthorizationsController@destroy');

		//小区信息
		$api->get('community', 'CommunitiesController@index');
		//车位信息
		$api->get('carPark', 'CarParksController@index');



		//需要token
		//获取我的信息
		$api->group(['middleware' => 'api.auth'], function ($api) {
			$api->get('user', 'UsersController@me');


            // =================================================================================================================
            // ICE
            // =================================================================================================================
            //获取短信验证码
            $api->post('getSMSCode', 'VerificationCodeController@getSMSCode');
		});
	});
});
