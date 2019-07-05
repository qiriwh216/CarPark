<?php

namespace App\Http\Services;

class BaseService
{

	protected $model;

	public function __construct()
	{
	}


	public function getUpdateData($to = array(), $from = array())
	{
		$updateDatas = array_uintersect_assoc(
			$to,
			$from,
			function ($left, $right) {
				return $left == $right;
			}
		);

		foreach ($updateDatas as $key => $value) {
			if (in_array($key, ['status']) && (string)$value == '') {
				unset($updateDatas[$key]);
			}
		}

		if (env('APP_DEBUG') == true) {
			app('log')->debug('updateDatas: ' . json_encode(
					$updateDatas,
					JSON_UNESCAPED_SLASHES
					| JSON_UNESCAPED_UNICODE
					| JSON_NUMERIC_CHECK
				)
			);
		}

		return $updateDatas;
	}

	/**
	 * @param array ...$keys
	 * @return string
	 */
	public function getCacheKey(...$keys)
	{
		$keyString = '';

		foreach ($keys as $key) {
			$keyString .= ':' . $key;
		}

		return $keyString;
	}

	public function flushCacheByKey($key = '')
	{
		app('cache')->forget($key);
		return $this;
	}

	public function flushCacheByTag($tag = '')
	{
		app('cache')->tags($tag)->flush();
		return $this;
	}
}