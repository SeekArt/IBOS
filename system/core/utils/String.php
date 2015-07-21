<?php

/**
 * 字符串操作工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 字符串操作工具类,提供字符串操作的所需方法，如过滤，截取，查找，加解密等
 * 
 * @package application.core.utils
 * @version $Id: string.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\core\utils\IBOS;
use application\extensions\Tree;
use application\modules\department\model\DepartmentRelated;
use application\modules\message\utils\Expression;
use application\modules\user\model\User;

class String {

	/**
	 * 检测一个字符串是否email格式
	 * @param string $email
	 * @return boolean
	 */
	public static function isEmail( $email ) {
		return strlen( $email ) > 6 && preg_match( "/^[\w\-\.]+@[\w\-]+(\.\w+)+$/", $email );
	}

	/**
	 * 检测一个字符串是否手机格式
	 * @param string $str
	 * @return boolean
	 */
	public static function isMobile( $str ) {
		return preg_match( "/^1\\d{10}/", $str );
	}

	/**
	 * 字符串方式实现 preg_match("/(s1|s2|s3)/", $string, $match)
	 * @param string $string 源字符串
	 * @param array $arr 要查找的字符串 如array('s1', 's2', 's3')
	 * @param boolean $returnValue 是否返回找到的值
	 * @return boolean
	 */
	public static function istrpos( $string, $arr, $returnValue = false ) {
		if ( empty( $string ) ) {
			return false;
		}
		foreach ( (array) $arr as $v ) {
			if ( strpos( $string, $v ) !== false ) {
				$return = $returnValue ? $v : true;
				return $return;
			}
		}
		return false;
	}

	/**
	 * 对字符串或者输入进行 addslashes 操作
	 * @param string $string
	 * @param integer $force
	 * @return mixed
	 */
	public static function iaddSlashes( $string, $force = 1 ) {
		if ( is_array( $string ) ) {
			$keys = array_keys( $string );
			foreach ( $keys as $key ) {
				$val = $string[$key];
				unset( $string[$key] );
				$string[addslashes( $key )] = self::iaddSlashes( $val, $force );
			}
		} else {
			$string = addslashes( $string );
		}
		return $string;
	}

	/**
	 * 对字符串进行加密和解密
	 * @param string $string
	 * @param string $operation  DECODE 解密 | ENCODE  加密
	 * @param string $key 当为空的时候,取全局密钥
	 * @param integer $expiry 有效期,单位秒
	 * @return string
	 * @author Ring 
	 */
	public static function authCode( $string, $operation = 'DECODE', $key = '', $expiry = 0 ) {
		$ckeyLength = 4;
		$key = md5( $key != '' ? $key : IBOS::app()->setting->get( 'authkey' )  );
		$keya = md5( substr( $key, 0, 16 ) );
		$keyb = md5( substr( $key, 16, 16 ) );
		$keyc = $ckeyLength ? ($operation == 'DECODE' ?
						substr( $string, 0, $ckeyLength ) :
						substr( md5( microtime() ), -$ckeyLength )) : '';

		$cryptkey = $keya . md5( $keya . $keyc );
		$keyLength = strlen( $cryptkey );

		$string = $operation == 'DECODE' ?
				base64_decode( substr( $string, $ckeyLength ) ) :
				sprintf( '%010d', $expiry ? $expiry + time() : 0  ) .
				substr( md5( $string . $keyb ), 0, 16 ) . $string;
		$stringLength = strlen( $string );

		$result = '';
		$box = range( 0, 255 );

		$rndkey = array();
		for ( $i = 0; $i <= 255; $i++ ) {
			$rndkey[$i] = ord( $cryptkey[$i % $keyLength] );
		}

		for ( $j = $i = 0; $i < 256; $i++ ) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for ( $a = $j = $i = 0; $i < $stringLength; $i++ ) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr( ord( $string[$i] ) ^ ($box[($box[$a] + $box[$j]) % 256]) );
		}

		if ( $operation == 'DECODE' ) {
			if ( (substr( $result, 0, 10 ) == 0 || substr( $result, 0, 10 ) - time() > 0) && substr( $result, 10, 16 ) == substr( md5( substr( $result, 26 ) . $keyb ), 0, 16 ) ) {
				return substr( $result, 26 );
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace( '=', '', base64_encode( $result ) );
		}
	}

	/**
	 * 产生随机码
	 * @param integer $length 要多长
	 * @param integer $numberic 数字还是字符串
	 * @return string $hash 返回字符串
	 */
	public static function random( $length, $numeric = 0 ) {
		$seed = base_convert( md5( microtime() . $_SERVER['DOCUMENT_ROOT'] ), 16, $numeric ? 10 : 35  );
		$seed = $numeric ? (str_replace( '0', '', $seed ) . '012340567890') : ($seed . 'zZ' . strtoupper( $seed ));
		$hash = '';
		$max = strlen( $seed ) - 1;
		for ( $index = 0; $index < $length; $index++ ) {
			$hash .= $seed{mt_rand( 0, $max )};
		}
		return $hash;
	}

	/**
	 * HTML转义字符
	 * @param mixed $string 数组或字符串
	 * @param mixed $flags htmlspecialchars函数的标记
	 * @link http://www.php.net/manual/zh/function.htmlspecialchars.php
	 * @return string 返回转义好的字符串
	 */
	public static function ihtmlSpecialChars( $string, $flags = null ) {
		if ( is_array( $string ) ) {
			foreach ( $string as $key => $val ) {
				$string[$key] = self::ihtmlSpecialChars( $val, $flags );
			}
		} else {
			if ( $flags === null ) {
				$string = str_replace( array( '&', '"', '<', '>' ), array( '&amp;', '&quot;', '&lt;', '&gt;' ), $string );
				if ( strpos( $string, '&amp;#' ) !== false ) {
					$string = preg_replace( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string );
				}
			} else {
				if ( PHP_VERSION < '5.4.0' ) {
					$string = htmlspecialchars( $string, $flags );
				} else {
					if ( strtolower( CHARSET ) == 'utf-8' ) {
						$charset = 'UTF-8';
					} else {
						$charset = 'ISO-8859-1';
					}
					$string = htmlspecialchars( $string, $flags, $charset );
				}
			}
		}
		return $string;
	}

	/**
	 * 根据中文裁减字符串
	 * @param string $string - 字符串
	 * @param integer $length - 长度
	 * @param string $doc - 缩略后缀 default=' ...'
	 * @return string 返回带省略号被裁减好的字符串
	 */
	public static function cutStr( $string, $length, $dot = ' ...' ) {
		$strlen = self::iStrLen( $string );
		if ( $strlen <= $length ) {
			return $string;
		}
		$pre = chr( 1 );
		$end = chr( 1 );
		$string = str_replace( array( '&amp;', '&quot;', '&lt;', '&gt;' ), array( $pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end ), $string );
		$strCut = '';
		if ( strtolower( CHARSET ) == 'utf-8' ) {
			$n = $tn = $noc = 0;
			while ( $n < $strlen ) {
				$t = ord( $string[$n] );
				if ( $t == 9 || $t == 10 || (32 <= $t && $t <= 126) ) {
					$tn = 1;
					$n++;
					$noc++;
				} elseif ( 194 <= $t && $t <= 223 ) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				} elseif ( 224 <= $t && $t <= 239 ) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				} elseif ( 240 <= $t && $t <= 247 ) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				} elseif ( 248 <= $t && $t <= 251 ) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				} elseif ( $t == 252 || $t == 253 ) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n++;
				}
				if ( $noc >= $length ) {
					break;
				}
			}
			if ( $noc > $length ) {
				$n -= $tn;
			}
			$strCut = substr( $string, 0, $n );
		} else {
			for ( $i = 0; $i < $length; $i++ ) {
				$strCut .= ord( $string[$i] ) > 127 ? $string[$i] . $string[++$i] : $string[$i];
			}
		}
		$strCut = str_replace( array( $pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end ), array( '&amp;', '&quot;', '&lt;', '&gt;' ), $strCut );
		$pos = strrpos( $strCut, chr( 1 ) );
		if ( $pos !== false ) {
			$strCut = substr( $strCut, 0, $pos );
		}
		return $strCut . $dot;
	}

	/**
	 * 针对uft-8进行特殊处理的strlen函数 (原名dstrlen)
	 * @param string $str
	 * @return integer 字符串的长度
	 * @author Ring
	 */
	public static function iStrLen( $str ) {
		if ( strtolower( CHARSET ) != 'utf-8' ) {
			return strlen( $str );
		}
		$count = 0;
		for ( $index = 0; $index < strlen( $str ); $index++ ) {
			$value = ord( $str[$index] );
			if ( $value > 127 ) {
				$count++;
				if ( $value >= 192 && $value <= 223 ) {
					$index++;
				} elseif ( $value >= 224 && $value <= 239 ) {
					$index = $index + 2;
				} elseif ( $value >= 240 && $value <= 247 ) {
					$index = $index + 3;
				}
			}
			$count++;
		}
		return $count;
	}

	/**
	 * 查找是否包含在内,两边都可以是英文逗号相连的字符串 原名(findin)
	 * @param string $string 目标范围
	 * @param  string $id 所有值
	 * @return boolean
	 * @author Ring
	 */
	public static function findIn( $string, $id ) {
		$string = trim( $string, "," );
		$newId = trim( $id, "," );
		if ( $newId == '' || $newId == ',' ) {
			return false;
		}
		$idArr = explode( ",", $newId );
		$strArr = explode( ",", $string );
		if ( array_intersect( $strArr, $idArr ) ) {
			return true;
		}
		return false;
	}

	/**
	 * 判断给定的参数是否是一个有效的IP地址 原名(isip)
	 * @param string $ip ip地址字符串
	 * @return boolean 
	 * @author Ring
	 */
	public static function isIp( $ip ) {
		if ( !strcmp( long2ip( sprintf( "%u", ip2long( $ip ) ) ), $ip ) ) {
			return true;
		}
		return false;
	}

	/**
	 * 判断一个字符串是否在另一个字符串中存在
	 *
	 * @param string 原始字串 $string
	 * @param string 查找 $find
	 * @return boolean
	 */
	public static function strExists( $string, $find ) {
		return !(strpos( $string, $find ) === false);
	}

	/**
	 * 返回一个形如10.0.*.*这样的IP 原名(get_sub_ip)
	 * @param string $ip ip地址字符串
	 * @return string
	 * @author Ring
	 */
	public static function getSubIp( $ip = '' ) {
		if ( empty( $ip ) ) {
			$ip = $clientIp = IBOS::app()->setting->get( 'clientip' );
		}
		$reg = '/(\d+\.)(\d+\.)(\d+)\.(\d+)/';
		return preg_replace( $reg, "$1$2*.*", $ip );
	}

	/**
	 * 返回显示IP的字符串 原名(ip_show)
	 * @param string $str 
	 * @return string
	 * @author Ring
	 */
	public static function displayIp( $str ) {
		if ( self::isIp( $str ) ) {
			return self::getSubIp( $str );
		}
		return $str;
	}

	/**
	 * 特别处理数组连接成字符串 带单引号 原名(dimplode)
	 * @param array $array
	 * @return string
	 * @author Ring
	 */
	public static function iImplode( $array ) {
		if ( !empty( $array ) ) {
			$array = array_map( 'addslashes', $array );
			return "'" . implode( "','", is_array( $array ) ? $array : array( $array )  ) . "'";
		} else {
			return '';
		}
	}

	/**
	 * 分割参数字符串返回数组。方便url组件创建URL
	 * <code>
	 * 		$param = 'a=3&b=4';
	 * 		$splitParam = String::splitParam($param);
	 * </code>
	 * @param string $param
	 * @return array
	 */
	public static function splitParam( $param ) {
		$return = array();
		if ( !empty( $param ) ) {
			$params = explode( '&', trim( $param ) );
			foreach ( $params as $data ) {
				list($key, $value) = explode( '=', $data );
				$return[$key] = $value;
			}
		}
		return $return;
	}

	/**
	 * 处理sql语句
	 * @param string $sql 原始的sql
	 * @return array 
	 */
	public static function splitSql( $sql ) {
		$sql = str_replace( "\r", "\n", $sql );
		$ret = array();
		$num = 0;
		$queriesArr = explode( ";\n", trim( $sql ) );
		unset( $sql );
		foreach ( $queriesArr as $querys ) {
			$queries = explode( "\n", trim( $querys ) );
			foreach ( $queries as $query ) {
				$val = substr( trim( $query ), 0, 1 ) == "#" ? null : $query;
				if ( isset( $ret[$num] ) ) {
					$ret[$num] .= $val;
				} else {
					$ret[$num] = $val;
				}
			}
			$num++;
		}
		return $ret;
	}

	/**
	 * 把密码字符串转换为可显示的形式
	 * <code>
	 * $password = '19881014';
	 * echo String::passwordMask($password);
	 * // returns '1********14';
	 * </code>
	 * @param string $password
	 * @return string
	 */
	public static function passwordMask( $password ) {
		return !empty( $password ) ? $password{0} . '********' . substr( $password, -2 ) : '';
	}

	/**
	 * 过滤日志字符串
	 * @param mixed $str
	 * @return mixed
	 */
	public static function clearLogString( $str ) {
		if ( !empty( $str ) ) {
			if ( !is_array( $str ) ) {
				$str = self::ihtmlSpecialChars( trim( $str ) );
				$str = str_replace( array( "\t", "\r\n", "\n", "   ", "  " ), ' ', $str );
			} else {
				foreach ( $str as $key => $val ) {
					$str[$key] = self::clearLogString( $val );
				}
			}
		}
		return $str;
	}

	/**
	 * 封装Tree类，快速获取有层级的分类树
	 * <code>
	 * 	$format = "<option value='\$catid' \$selected>\$spacer\$name</option>";
	 *  $data = Ibos:app()->setting->get('cache/positioncategory');
	 *  $trees = String::getTree($data,$format);
	 * </code>
	 * @param array $data 树数据
	 * @param string $format 生成的格式字符串
	 * @param integer $id 被选中的ID，比如在做树型下拉框的时候需要用到
	 * @param string $nbsp 间隔，取决于$spacer放在格式字符串的那里
	 * @param array $icon 修饰符号，可以换成图片
	 * @return string
	 */
	public static function getTree( $data, $format = "<option value='\$catid' \$selected>\$spacer\$name</option>", $id = 0, $nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;', $icon = array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;' ) ) {
		$tree = new Tree();
		//-- 生成前台显示样式 --
		$tree->init( $data );
		//-- 样式处理 --
		$tree->icon = $icon;
		$tree->nbsp = $nbsp;
		$trees = $tree->get_tree( 0, $format, $id );
		return $trees;
	}

	/**
	 * 获取分与不分前缀的id
	 * @param mixed $ids
	 * @param boolean $index 按前缀索引
	 * @return array
	 */
	public static function getId( $ids, $index = false ) {
		$newIds = array();
		$idList = is_array( $ids ) ? $ids : explode( ',', $ids );
		foreach ( $idList as $idstr ) {
			if ( !empty( $idstr ) ) {
				if ( $index ) {
					$prefix = substr( $idstr, 0, 1 );
					$newIds[$prefix][] = substr( $idstr, 2 );
				} else {
					$newIds[] = substr( $idstr, 2 );
				}
			}
		}
		return $newIds;
	}

	/**
	 * 获取有前缀的id字符串所包含的uid数组
	 * @param mixed $ids
	 * @return array
	 */
	public static function getUid( $ids ) {
		$uids = array();
		$idList = is_array( $ids ) ? $ids : array( $ids );
		foreach ( $idList as $idstr ) {
			if ( !empty( $idstr ) ) {
				$identifier = $idstr{0};
				$uid = self::getUidByIdentifier( $identifier, $idstr );
				$uids = array_merge( $uids, $uid );
			}
		}
		return array_unique( $uids );
	}

	/**
	 * 封装id加上前缀
	 * <code>
	 * $id = array(23,24,25,26);
	 * print_r(String::wrapId($id));
	 * return 'u_23,u_24,u_25,u_26';
	 * </code>
	 * @param array $ids id数组\
	 * @param string $identifier 前缀
	 * @param string $glue 分隔符
	 * @return string
	 */
	public static function wrapId( $ids, $identifier = 'u', $glue = ',' ) {
		if ( empty( $ids ) ) {
			return '';
		}
		$id = is_array( $ids ) ? $ids : explode( ',', $ids );
		$wrapId = array();
		foreach ( $id as $tempId ) {
			if ( !empty( $tempId ) ) {
				$wrapId[] = $identifier . '_' . $tempId;
			}
		}
		return implode( $glue, $wrapId );
	}

	/**
	 * 根据id标识符查找所包含的uid
	 * @param string $identifier 标识符,eg:u,d,p
	 * @param string $str 完整id字符串
	 * @return array 返回uid组成的数组
	 */
	public static function getUidByIdentifier( $identifier, $str ) {
		$id = substr( $str, 2 );
		if ( strcmp( $identifier, 'u' ) == 0 ) {
			return array( $id );
		} else if ( strcmp( $identifier, 'd' ) == 0 ) {
			$main = User::model()->fetchAllUidByDeptid( $id );
			$auxiliary = DepartmentRelated::model()->fetchAllUidByDeptId( $id );
			return array_merge( $main, $auxiliary );
		} else if ( strcmp( $identifier, 'p' ) == 0 ) {
			return User::model()->fetchUidByPosId( $id );
		}
	}

	/**
	 * 封装Intval函数，加上数组支持
	 * @param integer $int
	 * @param boolean $allowArray
	 * @return integer
	 */
	public static function iIntval( $int, $allowArray = false ) {
		$ret = intval( $int );
		if ( $int == $ret || !$allowArray && is_array( $int ) )
			return $ret;
		if ( $allowArray && is_array( $int ) ) {
			foreach ( $int as &$v ) {
				$v = self::iIntval( $v, true );
			}
			return $int;
		} elseif ( $int <= 0xffffffff ) {
			$l = strlen( $int );
			$m = substr( $int, 0, 1 ) == '-' ? 1 : 0;
			if ( ($l - $m) === strspn( $int, '0987654321', $m ) ) {
				return $int;
			}
		}
		return $ret;
	}

	/**
	 * 获取文件扩展名
	 * @param $fileName 文件名
	 * @since IBOS1.0
	 */
	public static function getFileExt( $fileName ) {
		return addslashes( strtolower( substr( strrchr( $fileName, '.' ), 1, 10 ) ) ) . '';
	}

	/**
	 * 
	 * 正则替换和过滤内容
	 * 
	 * @param  $html
	 * @author jason
	 */
	public static function pregHtml( $html ) {
		$p = array( "/<[a|A][^>]+(topic=\"true\")+[^>]*+>#([^<]+)#<\/[a|A]>/",
			"/<[a|A][^>]+(data=\")+([^\"]+)\"[^>]*+>[^<]*+<\/[a|A]>/",
			"/<[img|IMG][^>]+(src=\")+([^\"]+)\"[^>]*+>/" );
		$t = array( 'topic{data=$2}', '$2', 'img{data=$2}' );
		$html = preg_replace( $p, $t, $html );
		$html = strip_tags( $html, "<br/>" );
		return $html;
	}

	/**
	 * 获取字符串的长度
	 *
	 * 计算时, 汉字或全角字符占1个长度, 英文字符占0.5个长度
	 *
	 * @param string  $str
	 * @param boolean $filter 是否过滤html标签
	 * @return int 字符串的长度
	 */
	public static function getStrLength( $str, $filter = false ) {
		if ( $filter ) {
			$str = html_entity_decode( $str, ENT_QUOTES );
			$str = strip_tags( $str );
		}
		return (strlen( $str ) + mb_strlen( $str, 'UTF8' )) / 4;
	}

	/**
	 * 用于过滤标签，输出没有html的干净的文本
	 * @param string text 文本内容
	 * @return string 处理后内容
	 */
	public static function filterCleanHtml( $text ) {
		$text = nl2br( $text );
		$text = self::realStripTags( $text );
		$text = addslashes( $text );
		$text = trim( $text );
		return $text;
	}

	/**
	 * 
	 * @param type $str
	 * @param type $allowableTags
	 * @return type
	 */
	public static function realStripTags( $str, $allowableTags = "" ) {
		$str = stripslashes( htmlspecialchars_decode( $str ) );
		return strip_tags( $str, $allowableTags );
	}

	/**
	 * 用于过滤不安全的html标签，输出安全的html
	 * @param string $text 待过滤的字符串
	 * @param string $type 保留的标签格式
	 * @return string 处理后内容
	 */
	public static function filterDangerTag( $text, $type = 'html' ) {
		// 无标签格式
		$textTags = '';
		//只保留链接
		$linkTags = '<a>';
		//只保留图片
		$imageTags = '<img>';
		//只存在字体样式
		$fontTags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
		//标题摘要基本格式
		$baseTags = $fontTags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
		//兼容Form格式
		$formTags = $baseTags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
		//内容等允许HTML的格式
		$htmlTags = $baseTags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
		//专题等全HTML格式
		$allTags = $formTags . $htmlTags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
		//过滤标签
		$text = self::realStripTags( $text, ${$type . 'Tags'} );
		// 过滤攻击代码
		if ( $type != 'all' ) {
			// 过滤危险的属性，如：过滤on事件lang js
			while ( preg_match( '/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat ) ) {
				$text = str_ireplace( $mat[0], $mat[1] . $mat[3], $text );
			}
			while ( preg_match( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat ) ) {
				$text = str_ireplace( $mat[0], $mat[1] . $mat[3], $text );
			}
		}
		return $text;
	}

	/**
	 * 过滤字符串
	 * @param string $string 要过滤的字符串
	 * @param string $delimiter 分割符
	 * @param bool $unique 是否过滤重复值
	 * @return string 过滤后的字符串
	 */
	public static function filterStr( $string, $delimiter = ',', $unique = true ) {
		$filterArr = array();
		$strArr = explode( $delimiter, $string );
		foreach ( $strArr as $str ) {
			if ( !empty( $str ) ) {
				$filterArr[] = trim( $str );
			}
		}
		return implode( $delimiter, $unique ? array_unique( $filterArr ) : $filterArr  );
	}

	/**
	 * 把Unicode的中文字符转换成Utf8格式
	 * @param string $str
	 * @return string 返回utf8格式的中文字符串
	 */
	public static function unicodeToUtf8( $str ) {
		if ( !$str ) {
			return $str;
		}
		$decode = json_decode( $str );
		if ( $decode ) {
			return $decode;
		}
		$str = '["' . $str . '"]';
		$decode = json_decode( $str );
		if ( count( $decode ) == 1 ) {
			return $decode[0];
		}
		return $str;
	}

	/**
	 * 解析成api显示格式
	 * @param type $html
	 * @return type
	 */
	public static function parseForApi( $html ) {
		$html = self::filterDangerTag( $html );
		$html = str_replace( array( '[SITE_URL]', '&nbsp;' ), array( IBOS::app()->setting->get( 'siteurl' ), ' ' ), $html );
		//@提到某人处理
		$html = preg_replace_callback( "/@([\w\x{2e80}-\x{9fff}\-]+)/u", "self::parseWapAtByUname", $html );
		return $html;
	}

	/**
	 * 
	 * @param type $name
	 * @return type
	 */
	public static function parseWapAtByUname( $name ) {
		/* $info = static_cache( 'user_info_uname_' . $name[1] );
		  if ( !$info ) {
		  $info = model( 'User' )->getUserInfoByName( $name[1] );
		  if ( !$info ) {
		  $info = 1;
		  }
		  static_cache( 'user_info_uname_' . $name[1], $info );
		  }
		  if ( $info && $info['is_active'] && $info['is_audit'] && $info['is_init'] ) {
		  return '<a href="' . U( 'wap/Index/weibo', array( 'uid' => $info['uid'] ) ) . '" >' . $name[0] . "</a>";
		  } else {
		  return $name[0];
		  } */
	}

	/**
	 * 表情替换(用于页面显示)
	 * @param string $html
	 * @return string
	 */
	public static function replaceExpression( $html ) {
		return preg_replace_callback( "/(\[.+?\])/is", 'self::parseExpression', $html );
	}

	/**
	 * 解析数据成网页端显示格式
	 * @param type $html
	 * @return type
	 */
	public static function parseHtml( $html ) {

		$html = htmlspecialchars_decode( $html );
		//链接替换
		$html = str_replace( '[SITE_URL]', IBOS::app()->setting->get( 'siteurl' ), $html );
		// 外网链接地址处理
		$html = preg_replace_callback( '/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’,，。]*)?)/u', 'self::parseUrl', $html );
		//表情处理
		$html = preg_replace_callback( "/(\[.+?\])/is", 'self::parseExpression', $html );
		//话题处理
		$html = str_replace( "＃", "#", $html );
		$html = preg_replace_callback( "/#([^#]*[^#^\s][^#]*)#/is", 'self::parseTheme', $html );
		//@提到某人处理
		$html = preg_replace_callback( "/@([\w\x{2e80}-\x{9fff}\-]+)/u", 'self::parseAtByUserName', $html );
		return $html;
	}

	/**
	 * 表情替换 [格式化微博与格式化评论专用]
	 * @param array $data
	 */
	private static function parseExpression( $data ) {
		if ( preg_match( "/#.+#/i", $data[0] ) ) {
			return $data[0];
		}
		$allExpression = Expression::getAllExpression();
		$info = isset( $allExpression[$data[0]] ) ? $allExpression[$data[0]] : false;
		if ( $info ) {
			return preg_replace( "/\[.+?\]/i", "<img class='exp-img' src='" . STATICURL . "/image/expression/" . $info['icon'] . "' />", $data[0] );
		} else {
			return $data[0];
		}
	}

	/**
	 * 根据用户昵称获取用户ID [格式化微博与格式化评论专用]
	 * @param array $name
	 * @return string
	 */
	private static function parseAtByUserName( $name ) {
		$info = Cache::get( 'userInfoRealName_' . md5( $name[1] ) );
		if ( !$info ) {
			$info = User::model()->fetchByRealname( $name[1] );
			Cache::set( 'userInfoRealName_' . md5( $name[1] ), $info );
		}
		if ( $info ) {
			return '<a class="anchor" data-toggle="usercard" data-param="uid=' . $info['uid'] . '" href="' . $info['space_url'] . '" target="_blank">' . $name[0] . "</a>";
		} else {
			return $name[0];
		}
	}

	/**
	 * 话题替换 [格式化微博专用]
	 * @param array $data
	 * @return string
	 */
	private static function parseTheme( $data ) {
		// 如果话题被锁定，则不带链接
		$lock = IBOS::app()->db->createCommand()
				->select( 'lock' )
				->from( '{{feed_topic}}' )
				->where( sprintf( "topicname = '%s'", $data[1] ) )
				->queryScalar();
		if ( !$lock ) {
			return "<a class='wb-source' href=" . IBOS::app()->urlManager->createUrl( 'weibo/topic/detail', array( 'k' => urlencode( $data[1] ) ) ) . ">" . $data[0] . "</a>";
		} else {
			return $data[0];
		}
	}

	/**
	 * 格式化微博,替换链接地址
	 * @param string $url
	 */
	public static function parseUrl( $url ) {
		$str = '<div class="url">';
		if ( preg_match( "/(youku.com|youtube.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com|yinyuetai.com)/i", $url[0], $hosts ) ) {
			// TODO: 语言包
			$str .= '<a href="' . $url[0] . '" target="_blank" data-node-type="wbUrl" class="o-url-video">视频</a>';
		} else if ( strpos( $url[0], 'taobao.com' ) ) {
			$str .= '<a href="' . $url[0] . '" target="_blank" data-node-type="wbUrl" class="o-url-taobao">淘宝</a>';
		} else {
			$str .= '<a href="' . $url[0] . '" target="_blank" data-node-type="wbUrl" class="o-url-web">网页</a>';
		}

		$str .= '</div>';
		return $str;
	}

	/**
	 * 格式化微博内容中url内容的长度
	 * @param string $match 匹配后的字符串
	 * @return string 格式化后的字符串
	 */
	public static function formatFeedContentUrlLength( $match ) {
		static $i = 97;
		$result = '{iurl==' . chr( $i ) . '}';
		$i++;
		$GLOBALS['replaceHash'][$result] = $match[0];
		return $result;
	}

	public static function replaceUrl( $content ) {
		//$content = preg_replace_callback('/((?:https?|ftp):\/\/(?:[a-zA-Z0-9][a-zA-Z0-9\-]*)*(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’,，。]*)?)/u', '_parse_url', $content);
		$content = str_replace( '[SITE_URL]', IBOS::app()->setting->get( 'siteurl' ), $content );
		$content = preg_replace_callback( '/((?:https?|mailto|ftp):\/\/([^\x{2e80}-\x{9fff}\s<\'\"“”‘’，。}]*)?)/u', 'self::parseUrl', $content );
		return $content;
	}

	/**
	 * 生成GUID(用户唯一ID)
	 * @return string
	 */
	public static function createGuid() {
		$charid = strtoupper( md5( uniqid( mt_rand(), true ) ) );
		$hyphen = chr( 45 ); // "-"     
		$uuid = substr( $charid, 0, 8 ) . $hyphen
				. substr( $charid, 8, 4 ) . $hyphen
				. substr( $charid, 12, 4 ) . $hyphen
				. substr( $charid, 16, 4 ) . $hyphen
				. substr( $charid, 20, 12 );
		return $uuid;
	}

	/**
	 * 组合选人框的值
	 * @param string $deptid 部门id
	 * @param string $positionid 岗位Id
	 * @param string $uid 用户id
	 * @return type 
	 */
	public static function joinSelectBoxValue( $deptid, $positionid, $uid ) {
		$tmp = array();
		if ( !empty( $deptid ) ) {
			if ( $deptid == 'alldept' ) {
				return 'c_0';
			}
			$tmp[] = self::wrapId( $deptid, 'd' );
		}
		if ( !empty( $positionid ) ) {
			$tmp[] = self::wrapId( $positionid, 'p' );
		}
		if ( !empty( $uid ) ) {
			$tmp[] = self::wrapId( $uid, 'u' );
		}
		return implode( ',', $tmp );
	}

}
