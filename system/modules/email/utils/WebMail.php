<?php

namespace application\modules\email\utils;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\core\utils\Xml;
use application\modules\email\core\WebEmail;
use application\modules\email\core\WebMailImap;
use application\modules\email\core\WebMailPop;
use application\modules\email\model\Email as Email2;
use application\modules\email\model\EmailBody;
use application\modules\email\model\EmailWeb;
use application\modules\email\utils\Email as EmailUtil;
use application\modules\user\model\User;

class WebMail {

	const SERVER_CONF_WEB = 'http://www.ibos.com.cn/resources/email/serverConf.xml'; // 在线服务器配置地址
	const SERVER_CONF_LOCAL = 'system/modules/email/extensions/serverConf.xml'; // 本地服务器配置地址

	/**
	 * 默认的服务器配置数组
	 * @var array 
	 */

	private static $defaultConfig = array(
		'POP3NAME' => '',
		'POP3EntireAddress' => 0,
		'SMTPNAME' => '',
		'IMAPNAME' => '',
		'POP3PORT' => 110,
		'SMTPPORT' => 25,
		'IMAPPORT' => 0,
		'POP3SSL' => 0,
		'SMTPSSL' => 0,
		'IMAPSSL' => 0,
		'IMAPEntireAddress' => 0,
		'DefaultUseIMAP' => 0
	);
	private static $_web = array();

	/**
	 * 检查一个账户是否正确
	 * @param string $address 一个正确的电子邮件全称地址
	 * @param string $password 密码
	 * @param string $configParse 用何种方式解析账户
	 * @return boolean
	 */
	public static function checkAccount( $address, $password, $postConfig = array(), $configParse = 'LOCAL' ) {
		$accountCorrect = false;
		$server = array();
		if ( empty( $postConfig ) ) {
			$server = self::getEmailConfig( $address, $password, $configParse );
		} else {
			$server = self::mergePostConfig( $address, $password, $postConfig );
		}
		if ( !is_string( $server ) ) {
			$accountCorrect = self::connectServer( $server );
		}
		return $accountCorrect;
	}

	/**
	 * 连接远程接收服务器
	 * @param array $serverObj 
	 * @return boolean 
	 */
	private static function connectServer( $conf = array() ) {
        $connected = false;
        /*
          $host = 'imap.qq.com';
          $port = 993;
          $user = 'ibos_gzdzl@qq.com';
          $pass = '123456gzddzl';
         */
        $host = $conf['server'];
        $port = $conf['port'];
        $user = $conf['username'];
        $pass = $conf['password'];
        $ssl = $conf['ssl'];
        $type = $conf['type'];
        $webEmail = new WebEmail($host, $port, $user, $pass, $ssl, $type);
        $connected = $webEmail->isConnected();
        return $connected;
        /**
         * email test
         * 15-8-24 下午1:53 gzdzl
         */
//		if ( !empty( $conf ) ) {
//			if ( $conf['type'] == 'imap' ) {
//				$obj = new WebMailImap();
//			} else {
//				$obj = new WebMailPop();
//			}
//			if ( $obj->connect( $conf['server'], $conf['username'], $conf['password'], $conf['ssl'], $conf['port'] ) ) {
//				$connected = true;
//			}
//		}
//return $connected;
	}

	/**
	 * 获取邮件正文
	 * @param object $conn 一个已经打开的fsock链接
	 * @param string $folder 要打开的邮箱文件夹，一般为'INBOX'
	 * @param int $id 当前列表的邮件ID
	 * @param array $structure 邮件内容结构数组
	 * @param int $part 第几部分的邮件？
	 * @param bool $convert 是否需要转换编码？
	 * @return string 邮件正文
	 */
	private static function fetchBody( $obj, $conn, $folder, $id, $structure, $part ) {
		// fetch body part
		$body = $obj->fetchPartBody( $conn, $folder, $id, $part );
		// decode body part
		$encoding = EmailMime::getPartEncodingCode( $structure, $part );
		if ( $encoding == 3 ) {
			$body = base64_decode( $body );
		} else if ( $encoding == 4 ) {
			$body = quoted_printable_decode( $body );
		}
		/* check if UTF-8 */
		$charset = EmailMime::getPartCharset( $structure, $part );
		if ( empty( $charset ) ) {
			$part_header = $obj->fetchPartHeader( $conn, $folder, $id, $part );
			$pattern = "/charset=[\"]?([a-zA-Z0-9_-]+)[\"]?/";
			preg_match( $pattern, $part_header, $matches );
			if ( count( $matches ) == 2 ) {
				$charset = $matches[1];
			}
		}
		if ( strcasecmp( $charset, "utf-8" ) == 0 ) {
			$is_unicode = true;
			//$body = utf8ToUnicodeEntities($body);
		} else if ( preg_match( "/#[0-9]{5};/", $body ) ) {
			$is_unicode = false;
		} else {
			$is_unicode = false;
		}
		if ( !$is_unicode ) {
			$body = Convert::iIconv( $body, 'gb2312' );
		}
		$url = IBOS::app()->urlManager->createUrl( 'email/web/show', array(
			'webid' => self::$_web['webid'],
			'folder' => $folder,
			'id' => $id,
			'cid' => ''
				)
		);
		$body = preg_replace( "/src=(\")?cid:/i", "src=\"{$url}", $body );
		return $body;
	}

	/**
	 * 获取外部邮件正文文本或html内容
	 * @param type $id
	 * @param type $conn
	 * @param type $obj
	 * @param type $header
	 * @return string
	 */
	public static function getBody( $id, &$conn, &$obj, $header ) {
		$structure_str = $obj->fetchStructureString( $conn, 'INBOX', $id );
		$structure = EmailMime::getRawStructureArray( $structure_str );
		$num_parts = EmailMime::getNumParts( $structure );
		$parent_type = EmailMime::getPartTypeCode( $structure );
		if ( ($parent_type == 1) && ($num_parts == 1) ) {
			$part = 1;
			$num_parts = EmailMime::getNumParts( $structure, $part );
			$parent_type = EmailMime::getPartTypeCode( $structure, $part );
		} else {
			$part = null;
		}
		//------------body-------------
		$body = array();
		$attach = '';
		//show attachments/parts
		if ( $num_parts > 0 ) {
			$attach .= "<table width=100%>\n";
			for ( $i = 1; $i <= $num_parts; $i++ ) {
				//get attachment info
				if ( $parent_type == 1 ) {
					$code = $part . (empty( $part ) ? "" : ".") . $i;
				} else if ( $parent_type == 2 ) {
					$code = $part . (empty( $part ) ? "" : ".") . $i;
				}
				$type = EmailMime::getPartTypeCode( $structure, $code );
				$name = EmailMime::getPartName( $structure, $code );
				if ( is_string( $name ) && !empty( $name ) ) {
					$name = htmlspecialchars( EmailLang::langDecodeSubject( $name, CHARSET ) );
					$fileExt = String::getFileExt( $name );
					$fileType = Attach::attachType( $fileExt );
				} else {
					$fileType = Attach::attachType( 1 );
				}
				$typestring = EmailMime::getPartTypeString( $structure, $code );
				list($dummy, $subtype) = explode( "/", $typestring );
				$bytes = EmailMime::getPartSize( $structure, $code );
//				$encoding = EmailMime::getPartEncodingCode( $structure, $code );
				$disposition = EmailMime::getPartDisposition( $structure, $code );
				//format href
				if ( ($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp( $subtype, "ms-tnef" ) == 0)) ) {
					continue;
//					$href = "read_message.php?user=$user&folder=$folder_url&id=$id&part=" . $code;
				} else {
					$href = IBOS::app()->urlManager->createUrl( 'email/web/show', array(
						'webid' => self::$_web['webid'],
						'folder' => 'INBOX',
						'id' => $id,
						'part' => $code
							)
					);
				}
				//show icon, file name, size
				$attach .= "<tr><td align=\"center\"><img src=\"{$fileType}\" border=0></td>";
				$attach .= "<td><a href=\"" . $href . "\" " . (($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp( $subtype, "ms-tnef" ) == 0)) ? "" : "target=_blank") . ">";
				$attach .= "<span class=\"small\">" . $name . "</span></a>";
				if ( $bytes > 0 ) {
					$attach .= "<td>[" . Convert::sizeCount( $bytes ) . "]</td>\n";
				}
				if ( is_string( $typestring ) ) {
					$attach .= "<td>" . htmlspecialchars( $typestring ) . "</td>\n";
				}
				$attach .= "\n</tr>\n";
			}
			$attach .= "</table>\n";
		}
		$typeCode = EmailMime::getPartTypeCode( $structure, $part );
		list($dummy, $subType) = explode( "/", EmailMime::getPartTypeString( $structure, $part ) );
		if ( ($typeCode == 3) && (strcasecmp( $subType, "ms-tnef" ) == 0) ) {
			//ms-tnef
			$type = $dummy;
		} else if ( $typeCode == 0 ) {
			// major type is "TEXT"
			$typeString = EmailMime::getPartTypeString( $structure, $part );
			// if part=0, and there's a conflict in content-type, use what's specified in header
			if ( empty( $part ) && !empty( $header->ctype ) && strcmp( $typeString, $header->ctype ) != 0 ) {
				$typeString = $header->ctype;
			}
			list($type, $subType) = explode( "/", $typeString );
			$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
		} else if ( $typeCode == 1 && empty( $part ) && ($structure[0][0] == "message") ) {
			// message content type is message/rfc822
			$part = "1.1";
			$typeString = EmailMime::getPartTypeString( $structure, $part );
			list($type, $subType) = explode( "/", $typeString );
			$typeCode = EmailMime::getPartTypeCode( $structure, $part );
			$disposition = EmailMime::getPartDisposition( $structure, $part );
			$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
		} else if ( ($typeCode == 1) || ($typeCode == 2) ) {
			$typeString = EmailMime::getPartTypeString( $structure, $part );
			list($type, $subType) = explode( "/", $typeString );
			$mode = 0;
			$subtypes = array( "mixed" => 1, "signed" => 1, "related" => 1, "array" => 2, "alternative" => 2 );
			$subType = strtolower( $subType );
			if ( $subtypes[$subType] > 0 ) {
				$mode = $subtypes[$subType];
			} else if ( strcasecmp( $subType, "rfc822" ) == 0 ) {
				$temp_num = EmailMime::getNumParts( $structure, $part );
				if ( $temp_num > 0 ) {
					$mode = 2;
				}
			} else if ( strcasecmp( $subType, "encrypted" ) == 0 ) {
				//check for RFC2015
				$encrypted_type = EmailMime::getPartTypeString( $structure, $part . ".1" );
				if ( stristr( $encrypted_type, "pgp-encrypted" ) !== false ) {
					$mode = -1;
				}
			}
			if ( $mode == -1 ) {
				//handle RFC2015 message
				$part = $part . (empty( $part ) ? "" : ".") . "2";
				$typeString = EmailMime::getPartTypeString( $structure, $part );
				list($type, $subType) = explode( "/", $typeString );
				$typeCode = EmailMime::getPartTypeCode( $structure, $part );
				$disposition = EmailMime::getPartDisposition( $structure, $part );
				$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
			} else if ( $mode > 0 ) {
				$originalPart = $part;
				for ( $i = 1; $i <= $num_parts; $i++ ) {
					//get part info
					$part = $originalPart . (empty( $originalPart ) ? "" : ".") . $i;
					$typeString = EmailMime::getPartTypeString( $structure, $part );
					list( $type, $subType) = explode( "/", $typeString );
					$typeCode = EmailMime::getPartTypeCode( $structure, $part );
					$disposition = EmailMime::getPartDisposition( $structure, $part );
					if ( strcasecmp( $disposition, "attachment" ) != 0 ) {
						//if NOT attachemnt...
						if ( ($mode == 1) && ($typeCode == 0) ) {
							//if "mixed" and type is "text" then show
							$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
						} else if ( $mode == 2 ) {
							$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
						} else if ( ($typeCode == 5) && (strcasecmp( $disposition, "inline" ) == 0 ) ) {
							//if type is image and disposition is "inline" show
							$href = IBOS::app()->urlManager->createUrl( 'email/web/show', array(
								'webid' => self::$_web['webid'],
								'folder' => 'INBOX',
								'id' => $id,
								'part' => $part )
							);
							$body[] = "<img src='{$href}'>";
						} else if ( $typeCode == 1 ) {
							//multipart part
							$part = EmailMime::getFirstTextPart( $structure, $part );
							//if HTML preferred, see if next part is HTML
							$next_part = EmailMime::getNextPart( $part );
							$next_type = EmailMime::getPartTypeString( $structure, $next_part );
							//if it is HTML, use it instead of text part
							if ( stristr( $next_type, "html" ) !== false ) {
								$part = $next_part;
							}
							$i++;
							$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
						}
					} else {
						if ( $typeCode == 5 ) {
							$href = IBOS::app()->urlManager->createUrl( 'email/web/show', array(
								'webid' => self::$_web['webid'],
								'folder' => 'INBOX',
								'id' => $id,
								'part' => $part )
							);
							$body[] = "<img src='{$href}'>";
						}
					}
				} // end foreach
			} else {
				// This is a multi-part MIME message;
				if ( strcasecmp( $subType, "rfc822" ) != 0 ) {
					$part = EmailMime::getFirstTextPart( $structure, "" );
					//if HTML preferred, see if next part is HTML
					$next_part = EmailMime::getNextPart( $part );
					$next_type = EmailMime::getPartTypeString( $structure, $next_part );
					//if it is HTML, use it instead of text part
					if ( stristr( $next_type, "html" ) !== false ) {
						$typeString = "text/html";
						$type = "text";
						$subType = "html";
						$part = $next_part;
					}
				}
				$body[] = self::fetchBody( $obj, $conn, 'INBOX', $id, $structure, $part );
			}
		} else {
			// not text or multipart, i.e. it's a file
			$type = EmailMime::getPartTypeCode( $structure, $part );
			$partName = EmailMime::getPartName( $structure, $part );
			$typeString = EmailMime::getPartTypeString( $structure, $part );
			$bytes = EmailMime::getPartSize( $structure, $part );
			$disposition = EmailMime::getPartDisposition( $structure, $part );
			$name = EmailLang::langDecodeSubject( $partName, CHARSET );
			$fileExt = String::getFileExt( $name );
			$fileType = Attach::attachType( $fileExt );
			$size = Convert::sizeCount( $bytes );
			$href = IBOS::app()->urlManager->createUrl( 'email/web/show', array(
				'webid' => self::$_web['webid'],
				'folder' => 'INBOX',
				'id' => $id,
				'part' => $part
					)
			);
			$body[] = <<<EOT
					<table>
						<tr>
							<td align="center">
								<a href="{$href}" target="_blank"><img src="{$fileType}" border=0 /><br/>{$name}<br/>[{$size}]<br/></a>
							</td>
						</tr>
					</table><br/>
EOT;
		}
		$body[] = $attach;
		return $body;
	}

	/**
	 * 获取一级域名.如 mxdomain.qq.com  返回 qq.com
	 * @param string $string 要处理的域名
	 * @return string
	 */
	public static function getDomin( $string ) {
		$parts = explode( '.', $string );
		$count = count( $parts );
		if ( $count > 2 ) {
			$suffix = array_pop( $parts );
			$domain = array_pop( $parts );
			return $domain . '.' . $suffix;
		} else {
			return $string;
		}
	}

	/**
	 * 获取邮件服务器配置
	 * @param string $address
	 * @param string $password
	 * @param string $configParse
	 * @return array
	 */
	public static function getEmailConfig( $address, $password, $configParse = 'LOCAL' ) {
		$server = array();
		$config = self::getServerConfig( $configParse );
		if ( !empty( $config ) ) {
			list(, $server) = explode( '@', $address );
			if ( isset( $config[$server] ) ) {
				$server = self::mergeServerConfig( $address, $password, $config[$server] );
			} else {
				$host = self::getMailAddress( $server );
				if ( $host ) {
					if ( isset( $config[$host] ) ) {
						$server = self::mergeServerConfig( $address, $password, $config[$host] );
					}
				}
			}
		}
		return $server;
	}

	/**
	 * 如果用户提供地址不在服务器配置数组内容，则通过MX lookup 获得邮箱服务器的域名
	 * @param string $domain
	 * @return mixed
	 */
	public static function getMailAddress( $domain ) {

		$host = $ip = false;
		// first try to get MX records
		// the lower the 'pri' value (priority) of MX hosts, the higher its 
		// precedence. if there are 3 MX records for a domain with priority 
		// 10, 20 and 30, a mail server should attempt delivery to that with
		// priority 10 first. if that fails, then 20, and so on. the numeric
		// value in the MX record is abitrary and there's no standard for what
		// it should be set to. the values could just as easily be 1, 2, 3 
		// but are typically 10, 20, 30.
		// the order of records in the array returned by dns_get_record is not
		// necessarily in order of priority, so we have to loop through the
		// array and work out which has the highest priority. this is done
		// with the $priority variable and doing a comparison on each loop
		// to see if this record has a higher priority than the previous ones
		$records = @dns_get_record( $domain, DNS_MX );
		if ( !$records ) {
			return false;
		}
		$priority = null;
		foreach ( $records as $record ) {
			if ( $priority == null || $record['pri'] < $priority ) {
				$myip = gethostbyname( $record['target'] );
				// if the value returned is the same, then the lookup failed
				if ( $myip != $record['target'] ) {
					$ip = $myip;
					$host = self::getDomin( $record['target'] );
					$priority = $record['pri'];
				}
			}
		}
		// if no MX record try A record
		// if no MX records exist for a domain, mail servers are supposed to 
		// attempt delivery instead to the A record for the domain. the final
		// check done here is to see if an A record exists, and if so, that
		// will be returned

		if ( !$ip ) {
			$ip = gethostbyname( $domain );
			// if the value returned is the same, then the lookup failed
			if ( $ip == $domain ) {
				$ip = false;
			} else {
				$info = gethostbyaddr( $ip );
				$info && $host = self::getDomin( $info );
			}
		}
		return $host;
	}

	/**
	 * 获取服务器配置数组
	 * @param string $method LOCAL:本地配置，WEB：网络配置，可保证最新
	 * @return array
	 */
	public static function getServerConfig( $method ) {
		static $config = array();
		if ( empty( $config ) ) {
			switch ( $method ) {
				case 'LOCAL':
					$config = self::parseLocalConfig( self::SERVER_CONF_LOCAL );
					break;
				case 'WEB':
					$config = self::parseWebConfig( self::SERVER_CONF_WEB );
					break;
				default:
					$config = array();
					break;
			}
		}
		return $config;
	}

	/**
	 * 合并表单提交配置数组
	 * @param string $address
	 * @param string $password
	 * @param array $config
	 * @return array
	 */
	public static function mergePostConfig( $address, $password, $config ) {
		$data = array(
			'SMTPNAME' => $config['smtpserver'],
			'SMTPPORT' => $config['smtpport'],
			'SMTPSSL' => isset( $config['smtpssl'] ) ? 1 : 0,
		);
		if($config['agreement'] == '1'){ // POP
			$data['POP3NAME'] = $config['server'];
			$data['POP3PORT'] = $config['port'];
			$data['POP3SSL'] = isset( $config['ssl'] ) ? 1 : 0;
		} else { // IMAP
			$data['IMAPNAME'] = $config['server'];
			$data['IMAPPORT'] = $config['port'];
			$data['IMAPSSL'] = isset( $config['ssl'] ) ? 1 : 0;
			$data['DefaultUseIMAP'] = 1;
		}
		return self::mergeServerConfig( $address, $password, $data );
	}

	/**
	 * 合并服务器配置，返回一个数据表可以识别的数组
	 * @param string $address
	 * @param string $password
	 * @param array $config
	 * @return array
	 */
	private static function mergeServerConfig( $address, $password, $config ) {
		$config = array_merge( self::$defaultConfig, $config );
		$return = array();
		if ( $config['POP3EntireAddress'] || $config['IMAPEntireAddress'] ) {
			$return['username'] = $address;
		} else {
			list($domain, ) = explode( '@', $address );
			$return['username'] = $domain;
		}
		$return['password'] = $password;
		$return['address'] = $address;
		$usingImap = $config['DefaultUseIMAP'] ? true : false;
		$return['type'] = $usingImap ? 'imap' : 'pop';
		$return['server'] = $usingImap ? $config['IMAPNAME'] : $config['POP3NAME'];
		$return['port'] = $usingImap ? $config['IMAPPORT'] : $config['POP3PORT'];
		$return['ssl'] = $usingImap ? $config['IMAPSSL'] : $config['POP3SSL'];
		$return['smtpserver'] = isset( $config['SMTPNAME'] ) ? $config['SMTPNAME'] : '';
		$return['smtpport'] = isset( $config['SMTPPORT'] ) ? $config['SMTPPORT'] : '';
		$return['smtpssl'] = isset( $config['SMTPSSL'] ) ? $config['SMTPSSL'] : '';
		return $return;
	}

	/**
	 * 解析xml格式的服务器配置到一个数组并返回
	 * @param string $address 本地服务器配置xml地址别名，必须要用Yii规定的别名样式
	 * @return array
	 */
	private static function parseLocalConfig( $address ) {
		$config = array();
		if ( is_file( $address ) ) {
			$fileContent = file_get_contents( $address );
			$config = Xml::xmlToArray( $fileContent );
		}
		return $config;
	}

	/**
	 * 解析远程地址的服务器配置
	 * @param string $address
	 */
	private static function parseWebConfig( $address ) {
		//todo::完善解析远程地址服务器配置的方法
	}

	/**
	 * 接收邮件处理
	 * @param array $web
	 * @return int
	 */
	public static function receiveMail( $web ) {
        //检测imap扩展是否开启
        if (!extension_loaded('imap'))
            return 0;
        /**
         * 'webid' => string '15' (length=2)
          'address' => string 'jxnuoh@163.com' (length=14)
          'username' => string 'jxnuoh' (length=6)
          'password' => string 'b538axNhzMgW82AqlJXEzkmfvZVjiMvK0nhFHPLwdGlpOH8XWmkUklLt' (length=56)
          'smtpserver' => string 'smtp.163.com' (length=12)
          'smtpport' => string '25' (length=2)
          'smtpssl' => string '0' (length=1)
          'server' => string 'pop.163.com' (length=11)
          'port' => string '110' (length=3)
          'ssl' => string '0' (length=1)
          'uid' => string '1' (length=1)
          'nickname' => string 'fdsf' (length=4)
          'lastrectime' => string '0' (length=1)
          'fid' => string '19' (length=2)
          'isdefault' => string '0' (length=1)
         */
        self::$_web = $web;
        @set_time_limit(0);
        //ignore_user_abort(true);
        list($prefix,, ) = explode('.', $web['server']);
        $user = User::model()->fetchByUid($web['uid']);
        $pwd = String::authCode($web['password'], 'DECODE', $user['salt']); //解密

        /**
         * email 接收代码改写
         * 15-8-25 上午9:14 gzdzl
         */
        $host = $web['server'];
        $port = $web['port'];
        $user = $web['address'];
        $ssl = $web['ssl'] == '1' ? true : false;

        $webEmail = new WebEmail($host, $port, $user, $pwd, $ssl, $prefix);
        if ($webEmail->isConnected()) {
            //var_dump($webEmail->getMessages());
            $emails = $webEmail->getMessages();
            foreach ($emails as $email) {
                file_put_contents('email.txt', var_export($email, true));
                $data['subject'] = $email['subject'];
                $data['sendtime'] = strtotime($email['date']);
                $data['towebmail'] = $web['address'];
                $data['issend'] = 1;
                $data['fromid'] = $data['secrettoids'] = '';
                $data['fromwebmail'] = EmailLang::langGetParseAddressList($email['from']);
                //收件人
                $data['toids'] = isset($email['to']) ? serialize($email['to']) : '';
                /**
                 * @TODO 获取附件，现在怎么保存
                 * 如果邮件有附件，添加邮件附件信息
                 */
                $data['remoteattachment'] = isset($email['attachments']) ? serialize($email['attachments'])
                            : null;
                //抄送人
                $data['copytoids'] = isset($email['cc']) ? serialize($email['cc'])
                            : '';
                //TODO qq邮箱的body是中文时有问题，会得到空串
                $data['content'] = $email['body'];
                //邮件大小(body)
                $data['size'] = strlen($data['content']);
                //检查是否收取过（可以放在前面）
                if (!EmailBody::isExist($data['sendtime'], $data['fromwebmail'])) {
                    $bodyId = EmailBody::model()->add($data, true);
                    if ($bodyId) {//邮件信息添加成功
                        $emailData = array(
                            'toid' => $web['uid'],
                            'isread' => 0,
                            'fid' => $web['fid'],
                            'isweb' => 1,
                            'bodyid' => $bodyId
                        );
                        Email2::model()->add($emailData);
                    }
                }
                EmailWeb::model()->updateByPk($web['webid'],
                        array('lastrectime' => TIMESTAMP));
            }
            return $webEmail->countMessages();
        }
        return 0;

        /*
          //按类型加载所用的函数库
          if ($prefix == 'imap') {
          $obj = new WebMailImap();
          } else {
          $obj = new WebMailPop();
          }
          $conn = $obj->connect($web['server'], $web['username'], $pwd,
          $web['ssl'], $web['port'], 'plain');
          if (!$conn) {
          return implode(',', $obj->getError());
          } else {
          $totalNum = $obj->countMessages($conn, 'INBOX');
          if ($totalNum > 0) {
          $messagesStr = "1:" . $totalNum;
          } else {
          $messagesStr = "";
          }
          /* 获取头部
          if ($messagesStr != "") {
          $headers = $obj->fetchHeaders($conn, 'INBOX', $messagesStr);
          $headers = $obj->sortHeaders($headers, 'DATE', 'DESC');  //if not from index array
          } else {
          $headers = false;
          }
          if ($headers == false) {
          $headers = array();
          }
          $count = 0;
          if (count($headers) > 0) {
          while (list ($key, $val) = each($headers)) {
          $header = $headers[$key];
          $time = $header->timestamp + 28800; //比林威治标准时间慢8小时，故加多8小时
          if ($web['lastrectime'] == 0 || $web['lastrectime'] < $time) {
          $count++;
          $data = array();
          $data['subject'] = str_replace(array('<', '>'),
          array('&lt;', '&gt;'),
          EmailLang::langDecodeSubject($header->subject,
          CHARSET));
          $encode = mb_detect_encoding($data['subject'],
          array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
          $data['subject'] = Convert::iIconv($data['subject'],
          $encode, CHARSET);
          $data['sendtime'] = $time;
          $data['towebmail'] = $web['address'];
          $data['issend'] = 1;
          $data['fromid'] = $data['secrettoids'] = '';
          $data['fromwebmail'] = EmailLang::langGetParseAddressList($header->from);
          if (isset($header->to) && !empty($header->to)) {
          $data['toids'] = EmailLang::langGetParseAddressList($header->to,
          ',');
          } else {
          $data['toids'] = '';
          }
          if (isset($header->cc) && !empty($header->cc)) {
          $data['copytoids'] = EmailLang::langGetParseAddressList($header->cc,
          ',');
          } else {
          $data['copytoids'] = '';
          }
          $body = self::getBody($header->id, $conn, $obj, $header);
          $data['content'] = implode('', $body);
          $data['size'] = EmailUtil::getEmailSize($data['content']);
          $bodyId = EmailBody::model()->add($data, true);
          if ($bodyId) {
          $email = array(
          'toid' => $web['uid'],
          'isread' => 0,
          'fid' => $web['fid'],
          'isweb' => 1,
          'bodyid' => $bodyId
          );
          Email::model()->add($email);
          }
          }
          }
          EmailWeb::model()->updateByPk($web['webid'],
          array('lastrectime' => TIMESTAMP));
          }
          return $count;
          }
         */
	}

	/**
	 * 发送外部邮件
	 * @param string $toUser 要发送的邮件地址，可多个
	 * @param array $body
	 * @param array $web
	 * @return mixed boolean|发送成功 string|错误信息
	 */
	public static function sendWebMail( $toUser, $body, $web ) {
		$user = User::model()->fetchByUid( $web['uid'] );
		$password = String::authCode( $web['password'], 'DECODE', $user['salt'] );
		$mailer = IBOS::createComponent( 'application\modules\email\extensions\mailer\EMailer' );
        $mailer = new \application\modules\email\extensions\mailer\EMailer();
		$mailer->IsSMTP();
		$mailer->SMTPDebug = 0;
		$mailer->Host = $web['smtpserver'];
		$mailer->Port = $web['smtpport'];
		$mailer->CharSet = 'UTF-8';
		if ( $web['smtpssl'] ) {
			$mailer->SMTPSecure = 'ssl';
		}
		$mailer->SMTPAuth = true;
		$mailer->Username = $web['username'];
		$mailer->Password = $password;
		$mailer->setFrom( $web['address'], $web['nickname'] );
		foreach ( explode( ';', $toUser ) as $address ) {
			$mailer->addAddress( $address );
		}
		$mailer->Subject = $body['subject'];
		$mailer->msgHTML( $body['content'] );
		$mailer->AltBody = 'This is a plain-text message body';
		if ( !empty( $body['attachmentid'] ) ) {
			$attachs = Attach::getAttachData( $body['attachmentid'] );
			$attachUrl = File::getAttachUrl();
			foreach ( $attachs as $attachment ) {
				$url = $attachUrl . '/' . $attachment['attachment'];
				if ( LOCAL ) {
					$mailer->addAttachment( $url, $attachment['filename'] );
				} else {
					$temp = IBOS::engine()->IO()->file()->fetchTemp( $url );
					$mailer->addAttachment( $temp, $attachment['filename'] );
				}
			}
		}
		$status = $mailer->send();
		if ( $status ) {
			return true;
		} else {
			return $mailer->ErrorInfo;
		}
	}

}
