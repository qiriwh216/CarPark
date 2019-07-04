<?php

namespace App\Helpers;

/**
 * ICE接入服务
 * Class ICEService
 * @package App\Helpers\ICE
 */
class ICEService
{

	protected static $instance;
	protected $appID = '';
	protected $token = '';
	protected $baseUrl = '';
	protected $queryData;
	protected $queryUrl;
	protected $version;

	public function __construct()
	{
		$this->appID = env('ICE_APP_ID', '');
		$this->token = env('ICE_TOKEN', '');
		$this->baseUrl = env('ICE_AG_URL', '');
		$this->version = env('ICE_AG_VER', 'v1');
	}

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function setVersion($version = '')
	{
		$this->version = trim($version, ' /');
	}

	/**
	 * 服务 url
	 * @param string $interface
	 * @param array $queryData
	 * @return string
	 */
	protected function getQueryUrl($interface = '', $queryData = array())
	{
		$url = sprintf(
			$queryData ? '%s%s/%s?%s' : '%s/%s',
			trim($this->baseUrl, ' /'),
			$this->version ? ('/' . $this->version) : '',
			trim($interface, ' /'),
			$queryData ? http_build_query($queryData) : ''
		);

		app('log')->info('ICE请求地址:' . $url . PHP_EOL);

		return $url;
	}

	/**
	 * 获取签名
	 * @param int $timestamp
	 * @return string
	 */
	protected function getQuerySign($timestamp = 0)
	{
		if (!$timestamp) {
			$timestamp = time();
		}
		return md5(sprintf(
			'%s%s%s%s',
			$this->appID,
			$timestamp,
			$this->token,
			'false'
		));
	}


	/**
	 * 处理传递参数
	 * @param array $data
	 * @return array
	 */
	protected function getQueryData($data = array())
	{
		$timestamp = time();
		$queryData = array(
			'ts' => $timestamp,
			'appID' => $this->appID,
			'sign' => $this->getQuerySign($timestamp),
		);

		foreach ($data as $key => $item) {
			// 排除ICE保留词
			if (isset($queryData[$key])) {
				continue;
			}

			$queryData[$key] = $item;
		}

		return $queryData;
	}

	protected function getPostData($data = array())
	{
		$parsedData = array();

		if ($data && is_array($data)) {
			foreach ($data as $key => $item) {
				// @ 开头的字段会被认为是文件上传
				// 处理方式，@开头的，先添加空格，然后服务器端去空格
				$pos = strpos($item, '@');
				if ($pos !== false && $pos == 0) {
					$item = ' ' . $item;
				}

				$parsedData[$key] = $item;
			}
		}

		app('log')->info('ICE请求参数:' . json_encode($parsedData) . PHP_EOL);


		return $parsedData;
	}

	/**
	 * 解析请求返回数据
	 * @param string $response
	 * @return mixed|string
	 */
	protected function parseQueryResponse($response = '')
	{
		app('log')->info('ICE结果:' . $response . PHP_EOL);

		$response = json_decode($response, true);

		app('log')->info(
			sprintf(
				'调用ICE接口: %s, 参数: %s, 返回信息: %s.',
				$this->queryUrl,
				json_encode($this->queryData),
				var_export($response, true)
			)
		);

		return $response;
	}

	/**
	 * 请求ICE接口
	 * @param string $queryUrl
	 * @param array $queryData
	 * @param string $method
	 * @return mixed|string
	 */
	protected function request($queryUrl = '', $queryData = array(), $method = 'GET')
	{

		switch (strtoupper($method)) {
			default:
			case 'GET':
				$response = postCurl($queryUrl, $queryData, array(), 'GET');
				break;

			case 'POST':
				$response = postCurl($queryUrl, $queryData, array(), 'POST');
				break;

			case 'PUT':
				$response = postCurl($queryUrl, $queryData, array(), 'PUT');
				break;
			case 'DELETE':
				$response = postCurl($queryUrl, $queryData, array(), 'DELETE');
				break;
		}

		return $this->parseQueryResponse($response);
	}

	/**
	 * 接口转发
	 * @param string $interface
	 * @param array $getParam
	 * @param array $postParam
	 * @param string $method
	 * @return mixed
	 * @throws \Exception
	 */
	public function dispatch($interface = '', $getParam = array(), $postParam = array(), $method = 'GET')
	{
		try {
			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getQueryUrl(
					$interface,
					// 处理 queryString 参数
					$this->getQueryData($getParam)
				),
				// 处理 post field 参数
				$this->getPostData($postParam),
				$method
			);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$code = $e->getCode();
			throw new \Exception(
				sprintf(
					'ICE请求'.$interface.'失败：%s[%s]。请重试!',
					$message ? $message : '连接出错',
					$code ? $code : '-1'
				),
				501
			);
		}

		if (!$response) {
			throw new \Exception(
				'ICE请求'.$interface.'失败,请求地址: ' . $this->getQueryUrl($interface, $this->getQueryData($getParam)).'请求参数'.json_encode($this->getPostData($postParam)),
				500
			);
		}
		if (isset($response['code']) && $response['code'] != 0) {
			throw new \Exception(
				$response['message'],
				$response['code']
			);
		}
		if (isset($response['result']) && $response['result'] != 0) {
			throw new \Exception(
				$response['reason'],
				$response['result']
			);
		}
		return isset($response['content']) ? $response['content'] : $response;
	}

	public function dispatchNoException($interface = '', $getParam = array(), $postParam = array(), $method = 'GET')
	{
		try {
			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getQueryUrl(
					$interface,
					// 处理 queryString 参数
					$this->getQueryData($getParam)
				),
				// 处理 post field 参数
				$this->getPostData($postParam),
				$method
			);
		} catch (\Exception $e) {
			app('log')->info('接口'.$interface.'code=:' .  $e->getCode() .'message'. $e->getMessage(). PHP_EOL);
			return false;
		}
		if (!$response) {
			app('log')->info('ICE请求'.$interface.'失败,请求地址: ' . json_encode($this->getQueryUrl($interface, $this->getQueryData($getParam)).'请求参数'.$this->getPostData($postParam)). PHP_EOL);
			return false;
		}

		if (isset($response['code']) && $response['code'] != 0) {
			app('log')->info('接口'.$interface.'code=:' .  $response['code'] .'message'. json_encode($response['message']). PHP_EOL);
			return false;
		}
		if (isset($response['result']) && $response['result'] != 0) {
			app('log')->info('接口'.$interface.'code=:' .  $response['code'] .'message'. json_encode($response['message']). PHP_EOL);
			return false;
		}

		return isset($response['content']) ? $response['content'] : $response;
	}

	protected function getNewQueryUrl($forward_url ,$interface = '', $queryData = array())
	{
		return sprintf(
			$queryData ? '%s/%s?%s' : '%s/%s',
			trim($forward_url, ' /'),
			trim($interface, ' /'),
			$queryData ? http_build_query($queryData) : ''
		);
	}

	/**
	 * 处理传递参数
	 * @param array $data
	 * @return array
	 */
	protected function getNewQueryData($data = array())
	{
		$queryData = array(
			'access_token' => app('authPrivilege10')->getAccessToken()
		);

		foreach ($data as $key => $item) {
			// 排除ICE保留词
			if (isset($queryData[$key])) {
				continue;
			}
			// @ 开头的字段会被认为是文件上传
			// 处理方式，@开头的，先添加空格，然后服务器端去空格
			/*$pos = strpos($item, '@');
            if ($pos !== false && $pos == 0) {
                $item = ' ' . $item;
            }*/

			$queryData[$key] = $item;
		}

		return $queryData;
	}

	public function forward($forward_url = '', $interface = '',
							$getParam = array(), $postParam = array(),
							$method = 'GET')
	{
		try {
			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getNewQueryUrl(
					$forward_url,
					$interface,
					// 处理 queryString 参数
					$this->getNewQueryData($getParam)
				),
				// 处理 post field 参数
				$this->getPostData($postParam),
				$method
			);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$code = $e->getCode();
			throw new \Exception(
				sprintf(
					'%s[%s]',
					$message ? $message : '连接出错',
					$code ? $code : '-1'
				),
				501
			);
		}

		if (!$response || !isset($response['code'])) {
			throw new \Exception(
				$forward_url.'请求失败，无结果返回: ' . json_decode($response),
				500
			);
		}


		if (!$response) {
			throw new \Exception(
				'ICE请求失败,无结果返回: ' . json_encode($response),
				500
			);
		}

		if (isset($response['code']) && $response['code'] != 0) {
			throw new \Exception(
				$response['message'],
				$response['code']
			);
		}
		if (isset($response['result']) && $response['result'] != 0) {
			throw new \Exception(
				$response['reason'],
				$response['result']
			);
		}
		return isset($response['content']) ? $response['content'] : $response;
	}

	public function forwardNoException($forward_url = '', $interface = '',
							$getParam = array(), $postParam = array(),
							$method = 'GET')
	{
		try {
			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getNewQueryUrl(
					$forward_url,
					$interface,
					// 处理 queryString 参数
					$this->getNewQueryData($getParam)
				),
				// 处理 post field 参数
				$this->getPostData($postParam),
				$method
			);
		} catch (\Exception $e) {
			app('log')->info('接口'.$interface.'code=:' .  $e->getCode() .'message'. $e->getMessage(). PHP_EOL);
			return false;
		}

		if (!$response) {
			app('log')->info('ICE请求'.$interface.'失败,请求地址: ' . json_encode($this->getQueryUrl($interface, $this->getQueryData($getParam)).'请求参数'.$this->getPostData($postParam)). PHP_EOL);
			return false;
		}

		if (isset($response['code']) && $response['code'] != 0) {
			app('log')->info('接口'.$interface.'code=:' .  $response['code'] .'message'. $response['message']. PHP_EOL);
			return false;
		}
		if (isset($response['result']) && $response['result'] != 0) {
			app('log')->info('接口'.$interface.'code=:' .  $response['code'] .'message'. $response['message']. PHP_EOL);
			return false;
		}
		return isset($response['content']) ? $response['content'] : $response;

	}

	public function dispatchWithPage($interface = '', $getParam = array(), $postParam = array(), $method = 'GET')
	{
		try {
			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getQueryUrl(
					$interface,
					// 处理 queryString 参数
					$this->getQueryData($getParam)
				),
				// 处理 post field 参数
				$this->getPostData($postParam),
				$method
			);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$code = $e->getCode();
			throw new \Exception(
				sprintf(
					'ICE请求'.$interface.'失败：%s[%s]。请重试!',
					$message ? $message : '连接出错',
					$code ? $code : '-1'
				),
				501
			);
		}

		if (!$response) {
			throw new \Exception(
				'ICE请求'.$interface.'失败,请求地址: ' . json_encode($this->getQueryUrl($interface, $this->getQueryData($getParam)).'请求参数'.$this->getPostData($postParam)),
				500
			);
		}
		if (isset($response['code']) && $response['code'] != 0) {
			throw new \Exception(
				$response['message'],
				$response['code']
			);
		}
		if (isset($response['result']) && $response['result'] != 0) {
			throw new \Exception(
				$response['reason'],
				$response['result']
			);
		}
		$return_data = [];
		if($response['content'])
		{
			$return_data['content'] = $response['content'];
		}
		if($response['pageSize'])
		{
			$return_data['pageSize'] = $response['pageSize'];
		}
		if($response['totalPage'])
		{
			$return_data['totalPage'] = $response['totalPage'];
		}
		if($response['currentPage'])
		{
			$return_data['currentPage'] = $response['currentPage'];
		}
		if($response['totalRecord'])
		{
			$return_data['totalRecord'] = $response['totalRecord'];
		}
		return $return_data;
	}


	/**
	 * 转发金融平台接口
	 * @param string $interface
	 * @param array $getParam
	 * @param array $postParam
	 * @param string $method
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function dispatchFinance($interface = '', $getParam = array(), $postParam = array(), $method = 'GET')
	{
		try {
			$getParam = $this->getQueryData($getParam);
			$getParam['ver'] = 300;
			$getParam['cmdno'] = only_uuid('-');
			$postParam = $this->getPostData($postParam);
			$params = array_merge($getParam, $postParam);
			ksort($params);
			$postParam['fp'] = md5(http_build_query($params) . $this->appID);

			$response = $this->request(
			// 拼接请求url，支持post既有get参数又有post参数
				$this->getQueryUrl(
					$interface,
					$getParam
				),
				$postParam,
				$method
			);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$code = $e->getCode();
			throw new \Exception(
				sprintf(
					'ICE请求失败：%s[%s]。请重试!',
					$message ? $message : '连接出错',
					$code ? $code : '-1'
				),
				501
			);
		}

		if (!$response) {
			throw new \Exception(
				'ICE请求失败,无结果返回: ' . json_encode($response),
				500
			);
		}

		if (isset($response['code']) && $response['code'] != 0) {
			throw new \Exception(
				$response['message'],
				$response['code']
			);
		}
		if (isset($response['result']) && $response['result'] != 0) {
			throw new \Exception(
				$response['reason'],
				$response['result']
			);
		}

		return isset($response['content']) ? $response['content'] : $response;
	}

}