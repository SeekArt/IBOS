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

use application\core\utils\Api;
use application\core\utils\ApiCode;
use CJSON;

class CoApi extends Api {

    // 。。。。
    const IBOS_KEY = '3569c4ee701cb512fef319fc16ec88af';
    // 酷办公系统地址
    const CO_URL = 'http://www.kubangong.com/';
    // API 中心地址
    const API_CENTER = 'http://api.ibos.cn/';
    // API 中心用户登录接口链接
    const API_USER_GET_TOKEN = 'http://api.ibos.cn/v2/users/login';
    // API 中心获取用户信息接口链接
    const API_USER_GET_INFO = 'http://api.ibos.cn/v2/users/view';
    // API 中心查询企业接口链接
    const API_CORP_SEARCH = 'http://api.ibos.cn/v2/corp/search';
    // API 中心获取企业信息接口链接
    const API_CORP_GET_INFO = 'http://api.ibos.cn/v2/corp/view';
    // API 中心获取企业列表信息接口链接
    const API_CORP_GET_ALL = 'http://api.ibos.cn/v2/corp/getcorplist';
    // API 中心创建新企业接口链接
    const API_CORP_CREATE = 'http://api.ibos.cn/v2/corp/create';
    // API 中心更新企业数据接口链接
    const API_CORP_UPDATE_INFO = 'http://api.ibos.cn/v2/corp/update';
    // API 中心更新酷办公企业代码接口链接
    const API_CORP_UPDATE_CODE = 'http://api.ibos.cn/v2/corp/updatecorpcode';
    // API 中心退出企业接口链接
    const API_CORP_QUIT = 'http://api.ibos.cn/v2/corp/quit';
    // API 中心用户注册接口链接
    const API_USER_REGISTER = 'http://api.ibos.cn/v2/users/register';
    // API 中心手机号获取验证码接口链接
    const API_VERIFYCODE_GET = 'http://api.ibos.cn/v2/users/sendcode';
    // API 中心手机验证码验证接口链接
    const API_CODE_VERIFY = 'http://api.ibos.cn/v2/users/verify';
    // API 中心根据验证码登录接口链接
    // const API_CODE_VERIFY = 'http://api.ibos.cn/v2/users/loginbyvcode';
    // API 中心验证手机号是否已被注册接口链接
    const API_CHECK_MOBILE = 'http://api.ibos.cn/v2/users/checkmobile';
    // API 中心同步用户密码接口链接
    const API_SYNC_PASSWORD = 'http://api.ibos.cn/v2/users/syncpassword';
    // API 中心邀请用户接口链接
    const API_USER_INVITE = 'http://api.ibos.cn/v2/users/sendmessage';
    // API 中心绑定酷办公接口
    const API_BIND_CO = 'http://api.ibos.cn/v2/corp/bindingco';
    // API 中心解绑酷办公接口
    const API_UNBIND_CO = 'http://api.ibos.cn/v2/corp/unbundingoa';

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
		$url = $this->buildUrl( self::API_USER_GET_TOKEN, $param );
		$res = $this->fetchResult( $url, $postJson, 'post' );
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
		$res = $this->fetchResult( self::API_USER_GET_INFO, $param );
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
		$res = $this->fetchResult( self::API_CORP_GET_INFO, $param );
		return $this->returnJsonDecode( $res );
	}

    /**
     * 根据 accesstoken 获取用户的企业列表信息
     * @param  string $accesstoken 用户的 accesstoken
     * @return array              解析后的接口返回 json 数据
     */
    public function getCorpListByAccessToken($accesstoken) {
        $param = array(
            'accesstoken' => $accesstoken,
        );
        $result = $this->fetchResult(self::API_CORP_GET_ALL, $param);
        return $this->returnJsonDecode($result);
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
		$res = $this->fetchResult( self::API_CORP_UPDATE_INFO . '?corptoken=' . $corptoken, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

    /**
     * 根据 corptoken & corpcode 更新对应酷办公企业的企业代码
     * 被用于 IBOS 后台绑定酷办公企业出现企业代码不一致时统一企业代码
     * @param  string $corptoken corptoken
     * @param  string $corpcode  统一后的企业代码
     * @return array            解析后的接口返回 json 数据
     */
    public function updateCorpCodeByCorpToken($corptoken, $corpcode) {
        $url = sprintf('%s?corptoken=%s&corpcode=%s', self::API_CORP_UPDATE_CODE, $corptoken, $corpcode);
        $result = $this->fetchResult($url);
        return $this->returnJsonDecode($result);
    }

    /**
     * 根据corptoken退出corp
     * @param type $corptoken
     * @return type
     */
    public function quitCorpByCorpToken($corptoken) {
		$param = array(
			'corptoken' => $corptoken,
		);
		$res = $this->fetchResult( self::API_CORP_QUIT, $param );
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
		$res = $this->fetchResult( self::API_CORP_CREATE . '?accesstoken=' . $accesstoken, $postData, 'post' );
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
		$url = $this->buildUrl( self::API_CHECK_MOBILE, $param );
		$res = $this->fetchResult( $url, $get );
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
		$url = $this->buildUrl( self::API_USER_REGISTER, $param );
		$res = $this->fetchResult( $url, $postData, 'post' );
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
		$url = $this->buildUrl( self::API_VERIFYCODE_GET, $param );
		$res = $this->fetchResult( $url, $postData, 'post' );
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
        $url = $this->buildUrl(self::API_CODE_VERIFY, $param);
		$res = $this->fetchResult( $url, $postData, 'post' );
		return $this->returnJsonDecode( $res );
	}

    /**
     * 同步用户密码
     * @param  string $accesstoken 用户令牌
     * @param  array $post        需要 post 的参数，[需要同步的密码的密文，盐]
     * @return array
     */
    public function syncPassword($accesstoken, $post) {
        $postData = json_encode($post);
        $url = self::API_SYNC_PASSWORD . '?accesstoken=' . $accesstoken;
        $result = $this->fetchResult($url, $postData, 'post');
        return $this->returnJsonDecode($result);
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
		$url = $this->buildUrl( self::API_CORP_SEARCH, $param );
		$res = $this->fetchResult( $url, $postData, 'post' );
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
		$param['sign'] = $this->getSignature( array_merge( $param, $arr ) );
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
	 * @return type
	 */
	public function returnJsonDecode( $res ) {
		if ( !is_array( $res ) ) {
			return CJSON::decode( $res, true );
		} else {
			return array(
				'code' => ApiCode::CURL_ERROR,
				'message' => $res['error'],
			);
		}
	}

    /**
     * 获取用户列表差异
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    public function getDiffUsers($post) {
        $postJson = json_encode($post);
        $param = $this->returnSignParam();
        $url = $this->buildUrl(self::CO_URL . '/api/syncapi/diff', $param);
        $result = $this->fetchResult($url, $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    /**
     * 根据提供的绑定关系列表，在酷办公创建对应的绑定关系
     * @param  array $post 需要 POST 的数据
     * @return array
     */
    public function createRelationByList($post) {
        $postJson = json_encode($post);
        $result = $this->fetchResult(self::CO_URL . '/api/syncapi/addbind', $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    /**
     * 根据提供的绑定关系列表，在酷办公删除对应的绑定关系
     * @param  array $post 需要 POST 的数据
     * @return array
     */
    public function removeRelationByList($post) {
        $postJson = json_encode($post);
        $result = $this->fetchResult(self::CO_URL . '/api/syncapi/deletebind', $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    /**
     * 根据提供的用户数据列表，在酷办公创建对应的用户
     * @param  array $post 需要 POST 的数据
     * @return array
     */
    public function createCoUserByList($post) {
        $postJson = json_encode($post);
        $result = $this->fetchResult(self::CO_URL . '/api/syncapi/adduser', $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    /**
     * 根据提供的用户数据列表，在酷办公移除对应的用户
     * @param  array $post 需要 POST 的数据
     * @return array
     */
    public function removeCoUserByList($post) {
        $postJson = json_encode($post);
        $result = $this->fetchResult(self::CO_URL . '/api/syncapi/deleteuser', $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    public function bindingCo($corptoken, $post) {
        $postJson = json_encode($post);
        $result = $this->fetchResult(self::API_BIND_CO . '?corptoken=' . $corptoken, $postJson, 'post');
        return $this->returnJsonDecode($result);
    }

    /**
     * 解除绑定
     * @param array $post 需要 POST 的数据
     * @return array
     */
    public function unbindingCo($corptoken) {
        $postJson = json_encode(array());
        $params = array(
            'corptoken' => $corptoken,
            'type' => 'ibos',
        );
        $url = $this->buildUrl(self::API_UNBIND_CO, $params);
        $result = $this->fetchResult($url, $postJson, 'post');
        return $this->returnJsonDecode($result);
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
