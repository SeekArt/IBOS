<?php

/**
 * api应用工具类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 提供api curl的连接调用
 * @package application.core.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

class Api extends System {

	/**
	 * 默认的CURL选项
	 * @var array 
	 */
	protected $curlopt = array(
		CURLOPT_RETURNTRANSFER => true, // 返回页面内容
		CURLOPT_HEADER => false, // 不返回头部
		CURLOPT_ENCODING => "", // 处理所有编码
		CURLOPT_USERAGENT => "spider", // 
		CURLOPT_AUTOREFERER => true, // 自定重定向
		CURLOPT_CONNECTTIMEOUT => 15, // 链接超时时间
		CURLOPT_TIMEOUT => 20, // 超时时间
		CURLOPT_MAXREDIRS => 10, // 超过十次重定向后停止
		CURLOPT_SSL_VERIFYHOST => 0, // 不检查ssl链接
		CURLOPT_SSL_VERIFYPEER => false, //
		CURLOPT_VERBOSE => 1 //
	);

	public static function getInstance( $className = __CLASS__ ) {
		return parent::getInstance( $className );
	}

	/**
	 * 设置curl选项
	 * @param array $opt
	 */
	public function setOpt( $opt ) {
		if ( !empty( $opt ) ) {
			$this->curlopt = $opt + $this->curlopt;
		}
	}

	/**
	 * 返回curl默认选项
	 * @return array
	 */
	public function getOpt() {
		return $this->curlopt;
	}

	/**
	 * 创建api链接
	 * @param string $url 链接地址
	 * @param array $param 附件的参数
	 * @return string
	 */
	public function buildUrl( $url, $param = array() ) {
		$param = http_build_query( $param );
		return $url . (strpos( $url, '?' ) ? '&' : '?') . $param;
	}

	/**
	 * 获取调用api结果
	 * @param string $url api地址
	 * @param string $type 发送的类型 get or post
	 * @param array $param 如果类型为post时，要提交的参数
	 * @return array
	 */
	public function fetchResult( $url, $param = array(), $type = 'get' ) {
		$opt = $this->getOpt();
		if ( $type == 'post' ) {
			$opt = array(
				CURLOPT_POST => 1, // 是否post提交数据
				CURLOPT_POSTFIELDS => $param, // post的值
					) + $opt;
		} else {
			$url = $this->buildUrl( $url, $param );
		}
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $opt );
		$result = curl_exec( $ch );
		if ( $result === false ) {
			$curl_errno = curl_errno( $ch );
			$curl_error = curl_error( $ch );
			return array(
				'error' => ApiCode::getInstance()->getCurlMsg( $curl_errno, $curl_error ),
				'errno' => $curl_errno,
			);
		}
		curl_close( $ch );
		return $result;
	}

}
