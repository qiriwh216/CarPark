<?php
require_once __DIR__ . '/GlobalConfig.php';//宏定义

if (!function_exists('postCurl')) {
	/**
	 * 发起http 请求
	 * @param        $url
	 * @param array $body
	 * @param array $header
	 * @param string $method
	 * @return bool|mixed
	 */
	function postCurl($url, $body = array(), $header = array(), $method = 'POST')
	{
		array_push($header, 'Accept: application/json');
		array_push($header, 'Content-Length: ' . strlen(http_build_query($body)));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		switch ($method) {
			case 'GET':
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
		}

		curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);  //原先是FALSE，可改为2

		if ($body) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
		}
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$ret = curl_exec($ch);
		$err = curl_error($ch);
		$errno = curl_errno($ch);

		curl_close($ch);

		if ($errno) {
			app('myLog')->lumenLog(sprintf(
				'postCurl %s %s error: %s[%s] body: %s%s',
				strtoupper($method),
				$url,
				$err,
				$errno,
				json_encode(
					$body,
					JSON_UNESCAPED_SLASHES
					| JSON_UNESCAPED_UNICODE
					| JSON_PRETTY_PRINT
					| JSON_FORCE_OBJECT
				),
				PHP_EOL
			) , 'request_log');
			return false;
		} else {
			app('myLog')->lumenLog(sprintf(
				'postCurl %s %s body: %s%s response: %s%s',
				strtoupper($method),
				$url,
				json_encode(
					$body,
					JSON_UNESCAPED_SLASHES
					| JSON_UNESCAPED_UNICODE
					| JSON_PRETTY_PRINT
					| JSON_FORCE_OBJECT
				),
				PHP_EOL,
				$ret,
				PHP_EOL
			), 'request_log');
		}

		return $ret;
	}

	function postCurlContent($url, $body = array(), $header = array(), $method = 'POST')
	{
		$response = postCurl($url, $body, $header, $method);
		if (!$response) {
			throw new Exception('无法请求远程服务', 9001);
		}

		$response = json_decode($response, true);

		if (!$response || !isset($response['code'])) {
			throw new Exception('无法解析远程服务返回数据', 9002);
		}

		if ($response['code'] != 0) {
			throw new \Exception(
				sprintf('%s[%s]', $response['message'], $response['code']),
				9003
			);
		}

		return $response['content'];
	}
}


if (!function_exists('generate_code')) {
	function generate_code($length = 4)
	{
		return (int)str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
	}
}


if (!function_exists('check_email')) {
	function check_email($email)
	{

		if (!$email) {
			return false;
		}
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
}

if (!function_exists('check_url')) {
	function check_url($url)
	{

		if (!$url) {
			return false;
		}
		return filter_var($url, FILTER_VALIDATE_URL);
	}
}

if (!function_exists('check_mobile')) {
	function check_mobile($mobile)
	{

		if (!$mobile) {
			return false;
		}
		if (preg_match("/^\d{11}$/", $mobile)) {
			return true;
		} else {
			return false;

		}
	}
}

if (!function_exists('curl_post_ssl')) {
	function curl_post_ssl($url, $vars, $second = 30, $aHeader = array(), $sslcert, $sslprivate)
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		//以下两种方式需选择一种

		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, $sslcert);
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, $sslprivate);

		if (count($aHeader) >= 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		$data = curl_exec($ch);
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			//$error = curl_errno($ch);
			//echo "call faild, errorCode:$error\n";
			$err = curl_error($ch);
			//echo "call faild, errorCode:$err\n";
			curl_close($ch);
			return false;
		}
	}
}


if (!function_exists('only_uuid')) {
	/**
	 * 生成UUID
	 * @param string $seperator
	 * @return string
	 */
	function only_uuid($seperator = '')
	{
		return sprintf('%04x%04x%s%04x%s%04x%s%04x%s%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			$seperator,

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			$seperator,

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			$seperator,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			$seperator,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}

if (!function_exists('cache_minute_random')) {
	function cache_minute_random($min = 30, $max = 60)
	{
		return rand(max(0, $min), max($min, $max));
	}
}

if (!function_exists('cache_minute_eod')) {
	function cache_minute_eod()
	{
		return (strtotime('tomorrow') - time()) / 60;
	}
}

if (!function_exists('cache_expire_random')) {
	function cache_expire_random($time = 900, $max = 0, $base = 0)
	{
		$now = time();
		if (is_string($time)) {
			$expireAt = max(strtotime($time), $now);
			$base = (int)$max;
			if (!$base) {
				$base = $now;
			}

			$expire = $expireAt - $base;
		} else {
			$min = (int)$time;
			if (!$min) {
				$min = 900;
			}
			$max = (int)$max;
			if (!$max) {
				$max = 1800;
			}
			$max = max($min, $max);
			$expire = mt_rand($min, $max);

			$base = (int)$base;
			if (!$base) {
				$base = $now;
			}

			$expire = $expire + $base - $now;
		}

		return max(0, $expire);
	}
}

if (!function_exists('cache_expire_eod')) {
	function cache_expire_eod()
	{
		return strtotime('tomorrow') - time();
	}
}

if (! function_exists('output')) {
	/**
	 * 统一返回
	 * @param $content
	 * @param int $code
	 * @param string $message
	 * @param string $error
	 * @return \Illuminate\Http\JsonResponse
	 */
	function output($content, $code=0, $message='请求成功', $error='')
	{
		$result = [
			'code'              => $code,
			'message'           => $message,
			'content'           => $content,
			//'contentEncrypt'    => $error
		];

		return response()->json($result);
	}
}

if (! function_exists('object_array')) {
	function object_array($array) {
		if(is_object($array)) {
			$array = (array)$array;
		} if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = object_array($value);
			}
		}
		return $array;
	}
}

//获取请求者的ip
if (! function_exists('get_request_ip')) {
	function get_request_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}

/*
 * 除去数组中的空值和签名参数
 */
if (! function_exists('paraFilter')) {
	function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $key == "signature"
				|| $key == "ts" || $key == "access_token"|| $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
}

/*
 * 对数组排序
 */
if (! function_exists('argSort')) {
	function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
}

/*
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 */
if (! function_exists('createLinkStringUrlEncode')) {
	function createLinkStringUrlEncode($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);

		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

		return $arg;
	}
}

/*
 * 给定签名方式和data数组组装签名返回签名sign
 */
if (! function_exists('createSign')) {
	function createSign($data , $secret ,$sign_type = 'MD5') {
		$filter_data = paraFilter($data);
		$sort_data = argSort($filter_data);
		$stringA = createLinkstringUrlencode($sort_data);
		$stringSignTemp = $stringA.'&secret='.$secret;
		if($sign_type == 'MD5')
		{
			$sign = StrtoUpper(md5($stringSignTemp));
		}else if($sign_type == 'HMAC-SHA256')
		{
			$sign = StrtoUpper(hash_hmac('sha256' , $stringSignTemp , $secret));
		}else{
			$sign = '';
		}
		return $sign;

	}
}

//php7.2 count兼容对象
if (! function_exists('count_object')) {
	function count_object($arr) {
		$arr = json_encode($arr);
		$re =  json_decode($arr , true);
		return count($re);
	}
}

if (! function_exists('order_no')) {
	function order_no() {
		return (strtotime(date('YmdHis', time()))) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 99999));
	}
}






