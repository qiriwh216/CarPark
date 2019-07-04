<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable implements JWTSubject{

	use Notifiable;
	// use Traits\LastActivedAtHelper;
	protected $table = 'user';
	
	protected $guarded=[];
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];
	/**	
	 * getJWTIdentifier 返回了 User 的 id，getJWTCustomClaims 是我们需要额外再 JWT 载荷中增加的自定义内容，这里返回空数组
	 */
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		return [];
	}
	

	public function carPark(){
		return $this->hasOne(CarPark::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function favoriteCarParks(){
		return $this->belongsToMany(CarPark::class,'favorite')
		->withTimestamps()
		->orderBy('favorite.created_at','desc');
	}


}
