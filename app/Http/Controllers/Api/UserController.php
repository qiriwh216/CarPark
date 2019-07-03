<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller {
	//返回用户列表
	public function index() {
		//3个用户为一页
		
		$users = User::paginate(3);
		return $users;
	}
	//返回单一用户信息
	public function show() {
	}

	public function store(){

	}
}
