<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\PhoneLoginRequest;
use App\Transformers\UserTransformer;

class UserController extends Controller
{
	public function test(User $user){
		$query = $user->query();
		$query->where('');
		$users  = $query->paginate(3);
		return $this->response->paginator($users,new UserTransformer());
	}

	public function test2(){
		return $this->response->collection(User::all(), new UserTransformer());	
	}

	public function test3(){
		return $this->response->item(User::first(), new UserTransformer());
	}
	//返回用户列表
	public function index()
	{
		//3个用户为一页
		$users = User::paginate(3);

	}
	//返回单一用户信息
	public function show(User $user)
	{
		return $this->success(new UserResource($user));
	}
	//返回当前登录用户信息
	public function info()
	{
		$user = Auth::user();
		return $this->success(new UserResource($user));
	}
	//用户注册
	public function register(UserRequest $request)
	{
		User::create($request->all());
		return $this->setStatusCode(201)->success('用户注册成功');
	}

	//用户登录
	public function login(PhoneLoginRequest $request)
	{
		$verifyData = \Cache::get($request->verification_key);

		if (!$verifyData) {
			return $this->response->error('验证码已失效', 422);
		}

		if (!hash_equals((string) $verifyData['code'], $request->verification_code)) {
			return $this->response->errorUnauthorized('验证码错误');
		}

		$user = User::create([
			'phone' => $verifyData['phone'],
		]);

		// 清除验证码缓存
		\Cache::forget($request->verification_key);
		return response()->json([
			'data'=>$user,
		])->setMeta([ 'access_token' => \Auth::guard('api')->fromUser($user),
			'token_type' => 'Bearer',
			'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60])->setStatusCode(201);
	}
	
	//用户退出
	public function logout()
	{
		Auth::logout();
		return $this->success('退出成功...');
	}

	public function miniProgramLogin(){
		$code = $request->code;

		$miniProgram = \EasyWeChat::miniProgram();
		$data = $miniProgram->auth->session($code);

		if (isset($data['errcode'])) {
			return $this->error('小程序code 不正确');
		}

		$user = User::where('weapp_openid', $data['openid'])->first();

		$attributes['weixin_session_key'] = $data['session_key'];

		if (!$user) {
			// 找不到 openid 对应的用户要求用户提交
			if (!$request->phone) {
				return $this->error('用户不存在',401);
			}

			$username = $request->username;

			filter_var($username, FILTER_VALIDATE_EMAIL) ?
				$credentials['email'] = $username : $credentials['phone'] = $username;

			$credentials['password'] = $request->password;

			if (!Auth::guard('api')->once($credentials)) {
				return $this->response->errorUnauthorized('用户名或密码错误');
			}

			$user = Auth::guard('api')->getUser();
			$attributes['weapp_openid'] = $data['openid'];
		}

		$user->update($attributes);

		$token = Auth::guard('api')->fromUser($user);

	}	

	public function wxLogin(){
		if (!in_array($type, ['weixin'])) {
			return $this->response->errorBadRequest();
		}
		$driver = \Socialite::driver($type);
		try {
			if ($code = $request->code) {
				$response = $driver->getAccessTokenResponse($code);
				$token = array_get($response, 'access_token');
			} else {
				$token = $request->access_token;

				if ($type == 'weixin') {
					$driver->setOpenId($request->openid);
				}
			}
			$oauthUser = $driver->userFromToken($token);
		} catch (\Exception $e) {
			return $this->response->errorUnauthorized('参数错误，未获取用户信息');
		}

		switch ($type) {
			case 'weixin':
				$unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

				if ($unionid) {
					$user = User::where('weixin_unionid', $unionid)->first();
				} else {
					$user = User::where('weixin_openid', $oauthUser->getId())->first();
				}

				// 没有用户，默认创建一个用户
				if (!$user) {
					$user = User::create([
						'name' => $oauthUser->getNickname(),
						'avatar' => $oauthUser->getAvatar(),
						'weixin_openid' => $oauthUser->getId(),
						'weixin_unionid' => $unionid,
					]);
				}

				break;
		}

		$token = Auth::guard('api')->fromUser($user);
		return $this->respondWithToken($token)->setStatusCode(201);
	}


}
