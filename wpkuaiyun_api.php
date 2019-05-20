<?php
/**
 * KuaiYun Api 接口方法
 */

class wpKuaiyunURLRequest
{
    public $url;
    public $headers;
    public $params;
    public $body;
    public $expectedFormat;
    public $method;
    public $data;
    public $timeout;


    /**
     * URLRequest constructor.
     * 公共方法
     * @param $aUrl
     * @param array $aHeaders
     * @param array $aParams
     * @param string $aFormat
     * @param bool $isPost
     * @param string $aBody
     * @param string $timeout (s)
     */
    public function __construct($aUrl, array $aHeaders, array $aParams, $aFormat = "json", $isPost = True, $aBody = "+", $timeout = '3600')
    {
        $this->url = $aUrl;
        $this->headers = $aHeaders;
        $this->params = $aParams;
        $this->expectedFormat = $aFormat;
        $this->method = ($isPost ? "POST" : "GET");
        $this->body = $aBody;
        $this->timeout = $timeout;

    }


	public function exec()
	{

		$url = $this->url;

		$args = array(
			'body' => $this->body,
			'timeout' => $this->timeout,
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $this->headers,
		);

		if($this->method == "POST")
		{
			$response = wp_remote_post( $url, $args );
		} else {
			$response = wp_remote_get( $url, $args );
		}

		$httpCode = wp_remote_retrieve_response_code( $response );
		if ($httpCode == '200') {
			$body = wp_remote_retrieve_body( $response );
			return $body;
		}else{
			return $response;
		}
	}
}


/** 获取操作秘钥token函数 **/
function wpkuaiyun_get_token($voucher, $accessKey, $secretKey, $resource){
    $url = "http://api.storagesdk.com/restful/storageapi/storage/getToken"; //获取操作秘钥token方法
    $query = array();
    $data = array();
    $data["voucher"]= $voucher;
    $data["accessKey"]= $accessKey ;
    $data["secretKey"]= $secretKey;
    $data["resource"]= $resource;
    $body = json_encode($data);
    $headers = array(
	    'Content-Type' => 'application/json; charset=utf-8',
    );;
    $request = new wpKuaiyunURLRequest($url, $headers, $query, "json", true, $body, '5');
    $response = $request->exec();
    $msg = json_decode($response,true)["message"];
    $arr = explode(":",$msg);
    $token = $arr[1];
    return $token;
}


function wpkuaiyun_option_and_token(){
	$now = time();
	$opt = get_option('wpkuaiyun_options');
	if ($now >= $opt['token']['express']) {
		$token = wpkuaiyun_get_token($opt['voucher'], $opt['accessKey'], $opt['secretKey'], $opt['resource']);
		$opt['token']['value'] = $token;
		$opt['token']['express'] = $now + 86400;
		update_option('wpkuaiyun_options', $opt);
	}
	return $opt;
}


/*
 * 上传变量
 *
 *   $localFile = "C:\\demo.png";// 本地图片路径
 *   $fileName = "demo.jpg"; //上传到快云存储中显示的文件名；如果是多级的，自动创建文件夹，例test/demo.png，自动创建test目录，并在该目录下上传demo.png文件
 *
 */
function wpkuaiyun_send_file($localFile, $fileName){
    $url = "http://api.storagesdk.com/restful/storageapi/file/uploadFile"; //上传文件方法
	$opt = wpkuaiyun_option_and_token();
	$token = $opt['token']['value'];
    $data = array();
    $data['input'] = file_get_contents($localFile);
    $file = base64_encode($fileName);
    $len = strlen(file_get_contents($localFile));

    $headers = array(
    	'Content-Type' => 'application/json;charset=utf-8',
        'token' => "{$token}",
        'fileName' => "{$file}",
        'bucketName' => "{$opt['bucketName']}",
        'resource' => "{$opt['resource']}",
        'length' => "{$len}",
    );
    $body = $data["input"];
    $request = new wpKuaiyunURLRequest($url, $headers, $data, "json", true, $body);
    $response = $request->exec();
    $msg = json_decode($response,true)["message"];
    return $msg;
}


/*
 * 删除文件变量
 * $delfileName = "test/demo.png";
 */
function wpkuaiyun_del_file($fileName){
    $url = "http://api.storagesdk.com/restful/storageapi/file/deleteFile"; // 删除文件方法
    $query = array();
    $data = array();
    $opt = wpkuaiyun_option_and_token();

    $data["token"] = $opt['token']['value'];
    $data["fileName"] = $fileName;
    $data["bucketName"] = $opt['bucketName'];
    $data["resource"] = $opt['resource'];
    $body = json_encode($data);
    $headers = array(
		'Content-Type' => 'application/json; charset=utf-8',
	);
    $request = new wpKuaiyunURLRequest($url, $headers, $query, "json", true, $body);
    $response = $request->exec();
    $result	= json_decode($response, true)["message"];
    return $result;

}
