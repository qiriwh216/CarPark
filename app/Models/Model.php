<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent {
	//根据model class获取表名 表名中包含_，用驼峰自动转换
	public function getTable() {
		return $this->table ? $this->table : strtolower(snake_case(class_basename($this)));
	}
}
