<?php

/**
 * CoController.class.file
 * 
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 */
/**
 * 酷办公API处理类
 * @package application.modules.message.core.co
 * @author mumu <2317216477@qq.com>
 */

namespace application\modules\message\core\co;

use application\core\utils\WebSite;
use application\core\utils\Api;
use application\core\utils\Env;
use application\core\utils\File;
use application\modules\main\model\Setting;

class CoApi extends Api {

	const CO_URL = 'http://www.ibos.cn/';
	const API_CENTER = 'http://api.ibos.cn/';
	const API_USER_GET_TOKEN = 'http://api.ibos.cn/v1/users/login';
	const API_USER_GET_INFO = 'http://api.ibos.cn/v1/users/view';
	const API_CORP_SEARCH = 'http://api.ibos.cn/v1/corp/search';
	const API_CORP_GET_INFO = 'http://api.ibos.cn/v1/corp/view';
	const API_CORP_CREATE = 'http://api.ibos.cn/v1/corp/create';
	const API_CORP_UPDATE_INFO = 'http://api.ibos.cn/v1/corp/update';
	const API_CORP_QUIT = 'http://api.ibos.cn/v1/corp/quit';
	const API_USER_REGISTER = 'http://api.ibos.cn/v1/users/register';
	const API_VERIFYCODE_GET = 'http://api.ibos.cn/v1/users/verify';
	const API_VERIFYCODE_CHECK = 'http://api.ibos.cn/v1/users/verify';
	const API_CHECK_MOBILE = 'http://api.ibos.cn/v1/users/checkmobile';
	const IBOS_KEY = '3569c4ee701cb512fef319fc16ec88af';

	/**
	 * 签名的参数名称
	 */
	private $_signParam = 'sign';

	/**
	 * 
	 * @param void $param
	 */
	public function setSignParam( $param ) {
		$this->_signParam = $param;
	}

	/**
	 * 
	 * @return string
	 */
	public function getSignParam() {
		return $this->_signParam;
	}

	/**
	 * 签名方法的参数名称
	 */
	private $_signTypeParam = 'method';

	/**
	 * 
	 * @param string $param
	 */
	public function setSignTypeParam( $param ) {
		$this->_signTypeParam = $param;
	}

	/**
	 * 
	 * @return string
	 */
	public function getSignTypeParam() {
		return $this->_signTypeParam;
	}

	/**
	 * 用于验证签名的私钥
	 * @var string 
	 */
	private $_authkey = '';

	/**
	 * 设置验证签名用的私钥
	 * @param string $key
	 */
	public function setAuthKey( $key ) {
		$this->_authkey = $key;
	}

	/**
	 * 返回私钥
	 * @return string
	 */
	public function getAuthKey() {
		return $this->_authkey;
	}

	/**
	 * 签名的方法
	 * @var string 
	 */
	private $_signType = 'md5';

	/**
	 * 设置签名方法
	 * @param string $type
	 */
	public function setSignType( $type ) {
		$this->_signType = $type;
	}

	/**
	 * 返回签名方法
	 * @return string
	 */
	public function getSignType() {
		return $this->_signType;
	}

	public static function getInstance( $className = __CLASS__ ) {
		return parent::getInstance( $className );
	}

	/**
	 * 根据酷办公账号密码获取对应accesstoken的信息
	 * @param string $mobile
	 * @param string $password
	 * @return array
	 */
	public function getCoToken( $mobile, $password ) {
		$post = array(
			'mobile' => $mobile,
			'password' => md5( $password ),
		);
		$postJson = json_encode( $post );
		$param = $this->returnSignParam();
		$url = Api::getInstance()->buildUrl( self::API_USER_GET_TOKEN, $param );
		$res = Api::getInstance()->fetchResult( $url, $postJson, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 根据accesstoken获取用户的信息
	 * @param string $accesstoken
	 * @param string $uid
	 * @return array
	 */
	public function getUserInfo( $accesstoken, $uid = '' ) {
		$param = array(
			'accesstoken' => $accesstoken,
		);
		if ( !empty( $uid ) ) {
			$param['uid'] = $uid;
		}
		$res = Api::getInstance()->fetchResult( self::API_USER_GET_INFO, $param );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 根据corptoken获取corp的信息
	 * @param type $corptoken
	 * @return type
	 */
	public function getCorpByCorpToken( $corptoken ) {
		$param = array(
			'corptoken' => $corptoken,
		);
		$res = Api::getInstance()->fetchResult( self::API_CORP_GET_INFO, $param );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 通过corptoken更新corp信息
	 * 支持的参数
	 * "aeskey":"xxx",
	 * "regip":"xxxx",
	 * "logo":"xx",
	 * "name":"xx",
	 * "shortname":"xx",
	 * "area":"xx",
	 * "systemurl":"http://oa.xxx.com",
	 * "sysuser":"admin",
	 * "syspassword":"123456",
	 * "opencloud":0
	 * @param type $corptoken
	 * @param type $post
	 * @return type
	 */
	public function updateCorpByCorpToken( $corptoken, $post ) {
		$postData = json_encode( $post );
		$res = Api::getInstance()->fetchResult( self::API_CORP_UPDATE_INFO . '?corptoken=' . $corptoken, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 根据corptoken退出corp
	 * @param type $corptoken
	 * @return type
	 */
	public function quitCorpByCorpToken( $corptoken ) {
		$param = array(
			'corptoken' => $corptoken,
		);
		$res = Api::getInstance()->fetchResult( self::API_CORP_QUIT, $param );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 根据accesstoken创建corp
	 * 请求参数说明：
	 *
	 * - `accesstoken`：个人令牌。
	 * - `name`：企业名称。
	 * - `code`：企业代码
	 * - `createfrom`：(可选)用于标识哪个平台创建的
	 * - `regip`：(可选)注册IP
	 * - `opencloud`:(可选) yes or no 是否开启云端版IBOS
	 * @param type $accesstoken
	 * @param array $post 参照上面参数
	 * @return type
	 */
	public function createCorpByToken( $accesstoken, $post ) {
		$postData = json_encode( $post );
		$res = Api::getInstance()->fetchResult( self::API_CORP_CREATE . '?accesstoken=' . $accesstoken, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 验证手机号是否注册
	 * @param type $mobile
	 * @return type
	 */
	public function checkMobile( $mobile ) {
		$get = array(
			'mobile' => $mobile,
		);
		$param = $this->returnSignParam( $get );
		$url = Api::getInstance()->buildUrl( self::API_CHECK_MOBILE, $param );
		$res = Api::getInstance()->fetchResult( $url, $get );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 注册用户
	 * 请求参数说明：
	 * mobile： 用户注册手机号。
	 * email : 用户邮箱
	 * username : 用户名
	 * 以上三项不可同时为空。可全部填也可只提供一项。
	 * password：MD5加密用户明文密码后的字符串
	 * openid:（可选）微信登录openid
	 * @param array $post
	 * @param string $openId
	 * @return type
	 */
	public function registerUser( $post, $openId = '' ) {
		if ( !empty( $openId ) ) {
			$post['openid'] = $openId;
		}
		$post['password'] = md5( $post['password'] );
		$postData = json_encode( $post );
		$param = $this->returnSignParam();
		$url = Api::getInstance()->buildUrl( self::API_USER_REGISTER, $param );
		$res = Api::getInstance()->fetchResult( $url, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 获取验证码
	 * @param array $post 这里只有一个mobile参数
	 * @return type
	 */
	public function getVerifyCode( $post ) {
		$postData = json_encode( $post );
		$param = $this->returnSignParam();
		$url = Api::getInstance()->buildUrl( self::API_VERIFYCODE_GET, $param );
		$res = Api::getInstance()->fetchResult( $url, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 验证验证码
	 * @param array $post 需要带上mobiel和code参数
	 * @return array
	 */
	public function checkVerifyCode( $post ) {
		$postData = json_encode( $post );
		$param = $this->returnSignParam();
		$url = Api::getInstance()->buildUrl( self::API_VERIFYCODE_CHECK, $param );
		$res = Api::getInstance()->fetchResult( $url, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 搜索企业
	 * @param string $key 搜索关键字
	 * @param boolean $unique 是否完全匹配
	 * @param integer $page 当前页数
	 * @param integer $size 每页显示条数
	 * @return array
	 */
	public function searchCorp( $key, $unique = false, $page = 0, $size = 20 ) {
		$postData = json_encode( array(
			'key' => $key,
			'page' => $page,
			'size' => $size,
			'unique' => $unique,
				) );
		$param = $this->returnSignParam();
		$url = Api::getInstance()->buildUrl( self::API_CORP_SEARCH, $param );
		$res = Api::getInstance()->fetchResult( $url, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

	/**
	 * 返回签名数组
	 * @param type $arr
	 * @return array
	 */
	public function returnSignParam( $arr = array() ) {
		$param = array(
			'method' => 'md5',
			'timestamp' => time(),
			'platform' => 'ibos',
		);
		$param = array_merge( $param, $arr );
		$param['sign'] = $this->getSignature( $param );
		return $param;
	}

	/**
	 * 获取签名
	 * @param type $param
	 * @param type $method
	 * @param type $key
	 * @return type
	 */
	public function getSignature( $param, $method = 'md5', $key = self::IBOS_KEY ) {
		//除去待签名参数数组中的空值和签名参数
		$paraFilter = $this->paraFilter( $param );
		//对待签名参数数组排序
		$paraSort = $this->argSort( $paraFilter );
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring( $paraSort );
		return $method( $prestr . $key );
	}

	/**
	 * 让json_decode的第二个默认参数是false改成true
	 * @param string $res 返回的json格式字符串
	 * @param boolean $bool
	 * @return type
	 */
	public function returnJsonDecode( $res, $bool = true ) {
		return json_decode( $res, $bool );
	}

//————————————————————
	/**
	 * 除去数组中的空值和签名参数
	 * @param $para 签名参数组
	 * @return 去掉空值与签名参数后的新签名参数组
	 */
	protected function paraFilter( $para ) {
		$paraFilter = array();
		while ( list ($key, $val) = each( $para ) ) {
			if ( $key == 'sign' || $key == 'method' || $val == "" ) {
				continue;
			} else {
				$paraFilter[$key] = $para[$key];
			}
		}
		return $paraFilter;
	}

	/**
	 * 对数组排序
	 * @param $param 排序前的数组
	 * @return 排序后的数组
	 */
	protected function argSort( $param ) {
		ksort( $param );
		reset( $param );
		return $param;
	}

	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $param 需要拼接的数组
	 * @return string 拼接完成以后的字符串
	 */
	protected function createLinkstring( $param ) {
		$arg = "";
		while ( list ($key, $val) = each( $param ) ) {
			$arg.=$key . "=" . $val . "&";
		}
		//去掉最后一个&字符
		$arg = substr( $arg, 0, count( $arg ) - 2 );
		//如果存在转义字符，那么去掉转义
		if ( get_magic_quotes_gpc() ) {
			$arg = stripslashes( $arg );
		}
		return $arg;
	}

}
