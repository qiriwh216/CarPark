<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

	//返回用户列表
	public function index()
	{
		//3个用户为一页
		$users = User::paginate(3);
		return UserResource::collection($users);
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
	public function login(Request $request)
	{
		$user = User::first();	
		$token =  auth('api')->fromUser($user);
		return $this->success($token);
	}
	
	//用户退出
	public function logout()
	{
		Auth::logout();
		return $this->success('退出成功...');
	}
}
