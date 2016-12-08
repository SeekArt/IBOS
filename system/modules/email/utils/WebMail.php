<?php

namespace application\modules\email\utils;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\Xml;
use application\modules\email\model\Email as Email2;
use application\modules\email\model\EmailBody;
use application\modules\email\model\EmailWeb;
use application\modules\main\components\WebMailAttach;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentUnused;
use application\modules\user\model\User;
use ezcMailFile;
use ezcMailImapTransport;
use ezcMailImapTransportOptions;
use ezcMailParser;
use ezcMailPop3Transport;
use ezcMailPop3TransportOptions;
use ezcMailTransportException;

class WebMail
{

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
     * @param array $postConfig
     * @param string $configParse 用何种方式解析账户
     * @return bool
     */
    public static function checkAccount($address, $password, $postConfig = array(), $configParse = 'LOCAL')
    {
        $accountCorrect = false;
        $server = array();
        if (empty($postConfig)) {
            $server = self::getEmailConfig($address, $password, $configParse);
        } else {
            $server = self::mergePostConfig($address, $password, $postConfig);
        }
        if (!is_string($server)) {
            $accountCorrect = self::connectServer($server);
        }
        return $accountCorrect;
    }

    /**
     * 连接远程接收服务器
     * @param array $conf
     * @return boolean
     */
    private static function connectServer($conf = array())
    {
        $connected = false;
        // 新认证方法，不依赖 imap 扩展
        require PATH_ROOT . '/system/modules/email/extensions/vendor/autoload.php';
        /**
         * 'username' => string 'ibos_gzdzl' (length=10)
         * 'password' => string '123456gzdzl' (length=11)
         * 'address' => string 'ibos_gzdzl@qq.com' (length=17)
         * 'type' => string 'pop' (length=3)
         * 'server' => string 'pop.qq.com' (length=10)
         * 'port' => string '995' (length=3)
         * 'ssl' => int 1
         * 'smtpserver' => string 'smtp.qq.com' (length=11)
         * 'smtpport' => string '25' (length=2)
         * 'smtpssl' => int 0
         */
        // 协议类型
        if ($conf['type'] == 'pop') {
            $options = new ezcMailPop3TransportOptions ();
            if ($conf['ssl'] == 1) {
                $options->ssl = true;
            }
            try {
                $pop3 = new ezcMailPop3Transport($conf['server'], $conf['port'], $options);
                $pop3->authenticate($conf['address'], $conf['password']);
                // 没有异常就是认证通过了
                $connected = true;
            } catch (ezcMailTransportException $exc) {
                //echo $exc->getTraceAsString();
                // @todo 邮箱验证异常处理
            }
        } elseif ($conf['type'] == 'imap') {
            $options = new ezcMailImapTransportOptions();
            if ($conf['ssl'] == 1) {
                $options->ssl = true;
            }
            try {
                $imap = new ezcMailImapTransport($conf['server'], $conf['port'], $options);
                $imap->authenticate($conf['address'], $conf['password']);
                $connected = true;
            } catch (ezcMailTransportException $exc) {
                //echo $exc->getTraceAsString();
                // @todo 邮箱验证异常处理
            }
        } else {
            return false;
        }
        return $connected;
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
    private static function fetchBody($obj, $conn, $folder, $id, $structure, $part)
    {
// fetch body part
        $body = $obj->fetchPartBody($conn, $folder, $id, $part);
// decode body part
        $encoding = EmailMime::getPartEncodingCode($structure, $part);
        if ($encoding == 3) {
            $body = base64_decode($body);
        } else if ($encoding == 4) {
            $body = quoted_printable_decode($body);
        }
        /* check if UTF-8 */
        $charset = EmailMime::getPartCharset($structure, $part);
        if (empty($charset)) {
            $part_header = $obj->fetchPartHeader($conn, $folder, $id, $part);
            $pattern = "/charset=[\"]?([a-zA-Z0-9_-]+)[\"]?/";
            preg_match($pattern, $part_header, $matches);
            if (count($matches) == 2) {
                $charset = $matches[1];
            }
        }
        if (strcasecmp($charset, "utf-8") == 0) {
            $is_unicode = true;
//$body = utf8ToUnicodeEntities($body);
        } else if (preg_match("/#[0-9]{5};/", $body)) {
            $is_unicode = false;
        } else {
            $is_unicode = false;
        }
        if (!$is_unicode) {
            $body = Convert::iIconv($body, 'gb2312');
        }
        $url = Ibos::app()->urlManager->createUrl('email/web/show', array(
                'webid' => self::$_web['webid'],
                'folder' => $folder,
                'id' => $id,
                'cid' => ''
            )
        );
        $body = preg_replace("/src=(\")?cid:/i", "src=\"{$url}", $body);
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
    public static function getBody($id, &$conn, &$obj, $header)
    {
        $structure_str = $obj->fetchStructureString($conn, 'INBOX', $id);
        $structure = EmailMime::getRawStructureArray($structure_str);
        $num_parts = EmailMime::getNumParts($structure);
        $parent_type = EmailMime::getPartTypeCode($structure);
        if (($parent_type == 1) && ($num_parts == 1)) {
            $part = 1;
            $num_parts = EmailMime::getNumParts($structure, $part);
            $parent_type = EmailMime::getPartTypeCode($structure, $part);
        } else {
            $part = null;
        }
//------------body-------------
        $body = array();
        $attach = '';
//show attachments/parts
        if ($num_parts > 0) {
            $attach .= "<table width=100%>\n";
            for ($i = 1; $i <= $num_parts; $i++) {
//get attachment info
                if ($parent_type == 1) {
                    $code = $part . (empty($part) ? "" : ".") . $i;
                } else if ($parent_type == 2) {
                    $code = $part . (empty($part) ? "" : ".") . $i;
                }
                $type = EmailMime::getPartTypeCode($structure, $code);
                $name = EmailMime::getPartName($structure, $code);
                if (is_string($name) && !empty($name)) {
                    $name = htmlspecialchars(EmailLang::langDecodeSubject($name, CHARSET));
                    $fileExt = StringUtil::getFileExt($name);
                    $fileType = Attach::attachType($fileExt);
                } else {
                    $fileType = Attach::attachType(1);
                }
                $typestring = EmailMime::getPartTypeString($structure, $code);
                list($dummy, $subtype) = explode("/", $typestring);
                $bytes = EmailMime::getPartSize($structure, $code);
//				$encoding = EmailMime::getPartEncodingCode( $structure, $code );
                $disposition = EmailMime::getPartDisposition($structure, $code);
//format href
                if (($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp($subtype, "ms-tnef") == 0))) {
                    continue;
//					$href = "read_message.php?user=$user&folder=$folder_url&id=$id&part=" . $code;
                } else {
                    $href = Ibos::app()->urlManager->createUrl('email/web/show', array(
                            'webid' => self::$_web['webid'],
                            'folder' => 'INBOX',
                            'id' => $id,
                            'part' => $code
                        )
                    );
                }
//show icon, file name, size
                $attach .= "<tr><td align=\"center\"><img src=\"{$fileType}\" border=0></td>";
                $attach .= "<td><a href=\"" . $href . "\" " . (($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp($subtype, "ms-tnef") == 0)) ? "" : "target=_blank") . ">";
                $attach .= "<span class=\"small\">" . $name . "</span></a>";
                if ($bytes > 0) {
                    $attach .= "<td>[" . Convert::sizeCount($bytes) . "]</td>\n";
                }
                if (is_string($typestring)) {
                    $attach .= "<td>" . htmlspecialchars($typestring) . "</td>\n";
                }
                $attach .= "\n</tr>\n";
            }
            $attach .= "</table>\n";
        }
        $typeCode = EmailMime::getPartTypeCode($structure, $part);
        list($dummy, $subType) = explode("/", EmailMime::getPartTypeString($structure, $part));
        if (($typeCode == 3) && (strcasecmp($subType, "ms-tnef") == 0)) {
//ms-tnef
            $type = $dummy;
        } else if ($typeCode == 0) {
// major type is "TEXT"
            $typeString = EmailMime::getPartTypeString($structure, $part);
// if part=0, and there's a conflict in content-type, use what's specified in header
            if (empty($part) && !empty($header->ctype) && strcmp($typeString, $header->ctype) != 0) {
                $typeString = $header->ctype;
            }
            list($type, $subType) = explode("/", $typeString);
            $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
        } else if ($typeCode == 1 && empty($part) && ($structure[0][0] == "message")) {
// message content type is message/rfc822
            $part = "1.1";
            $typeString = EmailMime::getPartTypeString($structure, $part);
            list($type, $subType) = explode("/", $typeString);
            $typeCode = EmailMime::getPartTypeCode($structure, $part);
            $disposition = EmailMime::getPartDisposition($structure, $part);
            $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
        } else if (($typeCode == 1) || ($typeCode == 2)) {
            $typeString = EmailMime::getPartTypeString($structure, $part);
            list($type, $subType) = explode("/", $typeString);
            $mode = 0;
            $subtypes = array("mixed" => 1, "signed" => 1, "related" => 1, "array" => 2, "alternative" => 2);
            $subType = strtolower($subType);
            if ($subtypes[$subType] > 0) {
                $mode = $subtypes[$subType];
            } else if (strcasecmp($subType, "rfc822") == 0) {
                $temp_num = EmailMime::getNumParts($structure, $part);
                if ($temp_num > 0) {
                    $mode = 2;
                }
            } else if (strcasecmp($subType, "encrypted") == 0) {
//check for RFC2015
                $encrypted_type = EmailMime::getPartTypeString($structure, $part . ".1");
                if (stristr($encrypted_type, "pgp-encrypted") !== false) {
                    $mode = -1;
                }
            }
            if ($mode == -1) {
//handle RFC2015 message
                $part = $part . (empty($part) ? "" : ".") . "2";
                $typeString = EmailMime::getPartTypeString($structure, $part);
                list($type, $subType) = explode("/", $typeString);
                $typeCode = EmailMime::getPartTypeCode($structure, $part);
                $disposition = EmailMime::getPartDisposition($structure, $part);
                $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
            } else if ($mode > 0) {
                $originalPart = $part;
                for ($i = 1; $i <= $num_parts; $i++) {
//get part info
                    $part = $originalPart . (empty($originalPart) ? "" : ".") . $i;
                    $typeString = EmailMime::getPartTypeString($structure, $part);
                    list($type, $subType) = explode("/", $typeString);
                    $typeCode = EmailMime::getPartTypeCode($structure, $part);
                    $disposition = EmailMime::getPartDisposition($structure, $part);
                    if (strcasecmp($disposition, "attachment") != 0) {
//if NOT attachemnt...
                        if (($mode == 1) && ($typeCode == 0)) {
//if "mixed" and type is "text" then show
                            $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
                        } else if ($mode == 2) {
                            $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
                        } else if (($typeCode == 5) && (strcasecmp($disposition, "inline") == 0)) {
//if type is image and disposition is "inline" show
                            $href = Ibos::app()->urlManager->createUrl('email/web/show', array(
                                    'webid' => self::$_web['webid'],
                                    'folder' => 'INBOX',
                                    'id' => $id,
                                    'part' => $part)
                            );
                            $body[] = "<img src='{$href}'>";
                        } else if ($typeCode == 1) {
//multipart part
                            $part = EmailMime::getFirstTextPart($structure, $part);
//if HTML preferred, see if next part is HTML
                            $next_part = EmailMime::getNextPart($part);
                            $next_type = EmailMime::getPartTypeString($structure, $next_part);
//if it is HTML, use it instead of text part
                            if (stristr($next_type, "html") !== false) {
                                $part = $next_part;
                            }
                            $i++;
                            $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
                        }
                    } else {
                        if ($typeCode == 5) {
                            $href = Ibos::app()->urlManager->createUrl('email/web/show', array(
                                    'webid' => self::$_web['webid'],
                                    'folder' => 'INBOX',
                                    'id' => $id,
                                    'part' => $part)
                            );
                            $body[] = "<img src='{$href}'>";
                        }
                    }
                } // end foreach
            } else {
// This is a multi-part MIME message;
                if (strcasecmp($subType, "rfc822") != 0) {
                    $part = EmailMime::getFirstTextPart($structure, "");
//if HTML preferred, see if next part is HTML
                    $next_part = EmailMime::getNextPart($part);
                    $next_type = EmailMime::getPartTypeString($structure, $next_part);
//if it is HTML, use it instead of text part
                    if (stristr($next_type, "html") !== false) {
                        $typeString = "text/html";
                        $type = "text";
                        $subType = "html";
                        $part = $next_part;
                    }
                }
                $body[] = self::fetchBody($obj, $conn, 'INBOX', $id, $structure, $part);
            }
        } else {
// not text or multipart, i.e. it's a file
            $type = EmailMime::getPartTypeCode($structure, $part);
            $partName = EmailMime::getPartName($structure, $part);
            $typeString = EmailMime::getPartTypeString($structure, $part);
            $bytes = EmailMime::getPartSize($structure, $part);
            $disposition = EmailMime::getPartDisposition($structure, $part);
            $name = EmailLang::langDecodeSubject($partName, CHARSET);
            $fileExt = StringUtil::getFileExt($name);
            $fileType = Attach::attachType($fileExt);
            $size = Convert::sizeCount($bytes);
            $href = Ibos::app()->urlManager->createUrl('email/web/show', array(
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
    public static function getDomin($string)
    {
        $parts = explode('.', $string);
        $count = count($parts);
        if ($count > 2) {
            $suffix = array_pop($parts);
            $domain = array_pop($parts);
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
    public static function getEmailConfig($address, $password, $configParse = 'LOCAL')
    {
        $server = array();
        $config = self::getServerConfig($configParse);
        if (!empty($config)) {
            list(, $server) = explode('@', $address);
            if (isset($config[$server])) {
                $server = self::mergeServerConfig($address, $password, $config[$server]);
            } else {
                $host = self::getMailAddress($server);
                if ($host) {
                    if (isset($config[$host])) {
                        $server = self::mergeServerConfig($address, $password, $config[$host]);
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
    public static function getMailAddress($domain)
    {

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
        $records = @dns_get_record($domain, DNS_MX);
        if (!$records) {
            return false;
        }
        $priority = null;
        foreach ($records as $record) {
            if ($priority == null || $record['pri'] < $priority) {
                $myip = gethostbyname($record['target']);
// if the value returned is the same, then the lookup failed
                if ($myip != $record['target']) {
                    $ip = $myip;
                    $host = self::getDomin($record['target']);
                    $priority = $record['pri'];
                }
            }
        }
// if no MX record try A record
// if no MX records exist for a domain, mail servers are supposed to
// attempt delivery instead to the A record for the domain. the final
// check done here is to see if an A record exists, and if so, that
// will be returned

        if (!$ip) {
            $ip = gethostbyname($domain);
// if the value returned is the same, then the lookup failed
            if ($ip == $domain) {
                $ip = false;
            } else {
                $info = gethostbyaddr($ip);
                $info && $host = self::getDomin($info);
            }
        }
        return $host;
    }

    /**
     * 获取服务器配置数组
     * @param string $method LOCAL:本地配置，WEB：网络配置，可保证最新
     * @return array
     */
    public static function getServerConfig($method)
    {
        static $config = array();
        if (empty($config)) {
            switch ($method) {
                case 'LOCAL':
                    $config = self::parseLocalConfig(self::SERVER_CONF_LOCAL);
                    break;
                case 'WEB':
                    $config = self::parseWebConfig(self::SERVER_CONF_WEB);
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
    public static function mergePostConfig($address, $password, $config)
    {
        $data = array(
            'SMTPNAME' => $config['smtpserver'],
            'SMTPPORT' => $config['smtpport'],
            'SMTPSSL' => isset($config['smtpssl']) ? 1 : 0,
        );
        if ($config['agreement'] == '1') { // POP
            $data['POP3NAME'] = $config['server'];
            $data['POP3PORT'] = $config['port'];
            $data['POP3SSL'] = isset($config['ssl']) ? 1 : 0;
        } else { // IMAP
            $data['IMAPNAME'] = $config['server'];
            $data['IMAPPORT'] = $config['port'];
            $data['IMAPSSL'] = isset($config['ssl']) ? 1 : 0;
            $data['DefaultUseIMAP'] = 1;
        }
        return self::mergeServerConfig($address, $password, $data);
    }

    /**
     * 合并服务器配置，返回一个数据表可以识别的数组
     * @param string $address
     * @param string $password
     * @param array $config
     * @return array
     */
    private static function mergeServerConfig($address, $password, $config)
    {
        $config = array_merge(self::$defaultConfig, $config);
        $return = array();
        if ($config['POP3EntireAddress'] || $config['IMAPEntireAddress']) {
            $return['username'] = $address;
        } else {
            list($domain,) = explode('@', $address);
            $return['username'] = $domain;
        }
        $return['password'] = $password;
        $return['address'] = $address;
        $usingImap = $config['DefaultUseIMAP'] ? true : false;
        $return['type'] = $usingImap ? 'imap' : 'pop';
        $return['server'] = $usingImap ? $config['IMAPNAME'] : $config['POP3NAME'];
        $return['port'] = $usingImap ? $config['IMAPPORT'] : $config['POP3PORT'];
        $return['ssl'] = $usingImap ? $config['IMAPSSL'] : $config['POP3SSL'];
        $return['smtpserver'] = isset($config['SMTPNAME']) ? $config['SMTPNAME'] : '';
        $return['smtpport'] = isset($config['SMTPPORT']) ? $config['SMTPPORT'] : '';
        $return['smtpssl'] = isset($config['SMTPSSL']) ? $config['SMTPSSL'] : '';
        return $return;
    }

    /**
     * 解析xml格式的服务器配置到一个数组并返回
     * @param string $address 本地服务器配置xml地址别名，必须要用Yii规定的别名样式
     * @return array
     */
    private static function parseLocalConfig($address)
    {
        $config = array();
        if (is_file($address)) {
            $fileContent = file_get_contents($address);
            $config = Xml::xmlToArray($fileContent);
        }
        return $config;
    }

    /**
     * 解析远程地址的服务器配置
     * @param string $address
     */
    private static function parseWebConfig($address)
    {
        //todo::完善解析远程地址服务器配置的方法
    }

    /**
     * 接收邮件处理
     * @param array $web
     * @return int
     */
    public static function receiveMail($web)
    {
        set_time_limit(600);
        self::$_web = $web;
        list($prefix, ,) = explode('.', $web['server']);
        $user = User::model()->fetchByUid($web['uid']);
        $pwd = StringUtil::authCode($web['password'], 'DECODE', $user['salt']); //解密

        require_once PATH_ROOT . '/system/modules/email/extensions/vendor/autoload.php';

        try {
            if ($prefix == 'pop') {
                return self::receivePopMail($web, $pwd);
            } elseif ($prefix == 'imap') {
                return self::receiveImapMail($web, $pwd);
            } else {
                return self::receiveOtherMail($web, $pwd);
            }
        } catch (\Exception $e) {
            if (Ibos::app()->request->getIsAjaxRequest()) {
                return Ibos::app()->controller->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => $e->getMessage(),
                ));
            }

            return Ibos::app()->controller->error($e->getMessage());
        }

        return 0;
    }

    public static function fetchUnreceivedMail($server, $port, $user, $pass, $potions = array(), $max = 10)
    {
        $pop3 = new ezcMailPop3Transport($server, $port, $potions);
        $pop3->authenticate($user, $pass);
        // 获取所有邮件编号（不会接收邮件正文）
        $set = $pop3->fetchAll();
        $messages = $set->getMessageNumbers();
        if (empty($messages)) {
            return array();
        }

        $messages = array_reverse($messages);
        // 为了减少资源的占用，一次最多取 $max 条邮件
        $messages = array_slice($messages, 0, $max);
        $parser = new ezcMailParser();
        $mails = array();
        foreach ($messages as $message) {
            $setOne = $pop3->fetchByMessageNr($message);
            $mail = $parser->parseMail($setOne);
            // $parser->parseMail 返回的是一个数组。即使只有一封邮件。
            $mail = isset($mail[0]) ? $mail[0] : array();

            if (empty($mail)) {
                continue;
            }
            if (EmailBody::isExist($mail->timestamp, $mail->from->email)) {
                return $mails;
            }

            $mails[] = $mail;
        }

        return $mails;
    }

    /**
     * 发送外部邮件
     * @param string $toUser 要发送的邮件地址，可多个
     * @param array $body
     * @param array $web
     * @return mixed boolean|发送成功 string|错误信息
     */
    public static function sendWebMail($toUser, $body, $web)
    {
        $user = User::model()->fetchByUid($web['uid']);
        $password = StringUtil::authCode($web['password'], 'DECODE', $user['salt']);

        require_once PATH_ROOT . '/system/modules/email/extensions/mailer/phpmailer/PHPMailerAutoload.php';

        $mailer = new \PHPMailer();
        $mailer->IsSMTP();
        $mailer->SMTPDebug = 0;
        $mailer->Host = $web['smtpserver'];
        $mailer->Port = $web['smtpport'];
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = 30;
        if ($web['smtpssl']) {
            $mailer->SMTPSecure = 'ssl';
        }
        $mailer->SMTPAuth = true;
        $mailer->Username = $web['address'];
        $mailer->Password = $password;
        $mailer->setFrom($web['address'], $web['nickname']);
        foreach (explode(';', $toUser) as $address) {
            $mailer->addAddress($address);
        }
        $mailer->Subject = $body['subject'];
        $mailer->msgHTML($body['content']);
        $mailer->AltBody = 'This is a plain-text message body';
        if (!empty($body['attachmentid'])) {
            $attachs = Attach::getAttachData($body['attachmentid']);
            $attachUrl = File::getAttachUrl();
            foreach ($attachs as $attachment) {
                $url = $attachUrl . '/' . $attachment['attachment'];
                if (LOCAL) {
                    $mailer->addAttachment($url, $attachment['filename']);
                } else {
                    // 获取文件的远程地址
                    $url = Ibos::engine()->IO()->file()->fileName($url);
                    // 将远程文件下载到本地，并返回一个本地临时地址
                    $temp = Ibos::engine()->IO()->file()->fetchTemp($url);
                    $mailer->addAttachment($temp, $attachment['filename']);
                }
            }
        }
        $status = $mailer->send();
        if ($status) {
            return true;
        } else {
            return $mailer->ErrorInfo;
        }
    }

    /**
     * @param $web
     * @param $pwd
     * @return mixed
     * @throws \ezcMailInvalidLimitException
     * @throws \ezcMailOffsetOutOfRangeException
     */
    protected static function receivePopMail($web, $pwd)
    {
        $options = new ezcMailPop3TransportOptions();
        if ($web['ssl'] == 1) {
            $options->ssl = true;
        }
        $options->timeout = 30;
        $mails = self::fetchUnreceivedMail($web['server'], $web['port'], $web['address'], $pwd, $options);
        self::saveMails($web, $mails);
        return count($mails);
    }

    /**
     * @param $web
     * @param array $mails
     * @return true
     */
    protected static function saveMails($web, $mails)
    {
        for ($i = 0; $i < count($mails); $i++) {
            // 是否已经接收过
            if ((!$mails[$i]->timestamp || !$mails[$i]->from->email) || EmailBody::isExist($mails[$i]->timestamp, $mails[$i]->from->email)) {
                continue;
            }
            // 收件人
            $toemails = array();
            if ($mails[$i]->to && !empty($mails[$i]->to)) {
                for ($j = 0; $j < count($mails[$i]->to); $j++) {
                    $toemails[] = $mails[$i]->to[$j]->email;
                }
            }
            $data['towebmail'] = implode(';', $toemails);
            $data['toids'] = serialize($toemails);
            // 密送人
            $bccmails = array();
            if ($mails[$i]->bcc && !empty($mails[$i]->bcc)) {
                for ($j = 0; $j < count($mails[$i]->bcc); $j++) {
                    $bccmails[] = $mails[$i]->bcc[$j]->email;
                }
            }
            $data['secrettoids'] = serialize($bccmails);
            // 抄送人
            $ccmails = array();
            if ($mails[$i]->cc && !empty($mails[$i]->cc)) {
                for ($j = 0; $j < count($mails[$i]->cc); $j++) {
                    $ccmails[] = $mails[$i]->cc[$j]->email;
                }
            }
            $data['copytoids'] = serialize($ccmails);
            $data['subject'] = StringUtil::removeEmoji($mails[$i]->subject);
            // @todo 这里返回来的邮件内容有时可能是空
            // Fixed bug:不是返回的邮件内容为空，而是使用的邮件插件只有 ezcMailText 这个类下才有邮件内容 text
            // body 为 ezcMailText 类时直接用 ezcMailText->text 拿邮件内容
            // body 为 ezcMailMultipartAlternative 类时 需要用 ezcMailMultipartAlternative->getParts()[0] 拿到 part 下的类
            // part 为 ezcMailText 类时直接用 ezcMailText->text 拿邮件内容
            // part 为 ezcMailMultipartRelated 类时 需要用 ezcMailMultipartRelated->getMainPart() 拿到 part 下的类
            // 如果 part 还不是 ezcMailText 类的话，根据实际情况继续，直到拿到 ezcMailText 类为止
            $ezcMailText = $mails[$i]->body;
            while (!isset($ezcMailText->text)) {
                if (in_array('getParts', get_class_methods($ezcMailText))) {
                    $temp = $ezcMailText->getParts();
                    $ezcMailText = $temp[0];
                } else if (in_array('getMainPart', get_class_methods($ezcMailText))) {
                    $ezcMailText = $ezcMailText->getMainPart();
                }
            }
            $data['content'] = StringUtil::removeEmoji($ezcMailText->text);
            $data['size'] = $mails[$i]->size;
            $data['sendtime'] = $mails[$i]->timestamp;
            // 发件人
            $data['fromwebmail'] = $mails[$i]->from->email;
            // @todo 外部邮件接收 issend 写死是 1 是否正确?
            $data['issend'] = 1;
            // 附件
            $parts = $mails[$i]->fetchParts();
            $data['attachmentid'] = self::saveAttach($parts);
            $bodyId = EmailBody::model()->add($data, true);
            if ($bodyId) {
                $emailData = array(
                    'toid' => $web['uid'],
                    'isread' => 0,
                    'fid' => $web['fid'],
                    'isweb' => 1,
                    'bodyid' => $bodyId
                );
                Email2::model()->add($emailData);
            }
            EmailWeb::model()->updateByPk($web['webid'], array('lastrectime' => TIMESTAMP));
        }

        return true;
    }

    /**
     * 接收未知类型的邮箱邮件
     * 先尝试使用 pop 协议连接；如果连接失败，则再尝试使用 imap 协议连接。
     *
     * @param $web
     * @param $pwd
     * @return mixed
     */
    protected static function receiveOtherMail($web, $pwd)
    {
        try {
            return self::receivePopMail($web, $pwd);
        } catch (\Exception $e) {
            return self::receiveImapMail($web, $pwd);
        }
    }


    /**
     * @param $web
     * @param $pwd
     * @return mixed
     * @throws \ezcMailInvalidLimitException
     * @throws \ezcMailOffsetOutOfRangeException
     */
    protected static function receiveImapMail($web, $pwd)
    {
        $data = array();
        $options = new ezcMailImapTransportOptions();
        if ($web['ssl'] == 1) {
            $options->ssl = true;
        }
        $options->timeout = 30;
        $imap = new ezcMailImapTransport($web['server'], $web['port'], $options);
        try {
            $imap->authenticate($web['address'], $pwd);
            // IMAP 方式的必须
            $imap->selectMailbox('Inbox');
            // $num 邮件数
            // $size　总大小
            $imap->status($num, $size);
            // @todo 判断是否读取过，读取 100 封之前的邮件，读取新邮件
            // 读取最新的 100 封
            if (0 < $num && $num <= 100) {
                // 少于等于 100 封时全部去读
                $set = $imap->fetchAll();
            } elseif ($num > 100) {
                // 多于 100 封时， 读取最新的 100 封
                // 最新邮件的 message id 最大 (mysql 中的自增主键)
                $set = $imap->fetchFromOffset($num - 100, 100);
            } else {
                return 0;
            }
            $return = count($set);
            $parser = new ezcMailParser ();
            $mail = $parser->parseMail($set);
            for ($i = 0; $i < count($mail); $i++) {
                // 是否已经接收过
                if ((!$mail[$i]->timestamp || !$mail[$i]->from->email) || EmailBody::isExist($mail[$i]->timestamp, $mail[$i]->from->email)) {
                    continue;
                }
                // 收件人
                $toemails = array();
                if ($mail[$i]->to && !empty($mail[$i]->to)) {
                    for ($j = 0; $j < count($mail[$i]->to); $j++) {
                        $toemails[] = $mail[$i]->to[$j]->email;
                    }
                }
                $data['towebmail'] = implode(';', $toemails);
                $data['toids'] = serialize($toemails);
                // 密送人
                $bccmails = array();
                if ($mail[$i]->bcc && !empty($mail[$i]->bcc)) {
                    for ($j = 0; $j < count($mail[$i]->bcc); $j++) {
                        $bccmails[] = $mail[$i]->bcc[$j]->email;
                    }
                }
                $data['secrettoids'] = serialize($bccmails);
                // 抄送人
                $ccmails = array();
                if ($mail[$i]->cc && !empty($mail[$i]->cc)) {
                    for ($j = 0; $j < count($mail[$i]->cc); $j++) {
                        $ccmails[] = $mail[$i]->cc[$j]->email;
                    }
                }
                $data['copytoids'] = serialize($ccmails);
                $data['subject'] = $mail[$i]->subject;
                // @todo 这里返回来的邮件内容有时可能是空
                // Fixed bug:不是返回的邮件内容为空，而是使用的邮件插件只有 ezcMailText 这个类下才有邮件内容 text
                // body 为 ezcMailText 类时直接用 ezcMailText->text 拿邮件内容
                // body 为 ezcMailMultipartAlternative 类时 需要用 ezcMailMultipartAlternative->getParts()[0] 拿到 part 下的类
                // part 为 ezcMailText 类时直接用 ezcMailText->text 拿邮件内容
                // part 为 ezcMailMultipartRelated 类时 需要用 ezcMailMultipartRelated->getMainPart() 拿到 part 下的类
                // 如果 part 还不是 ezcMailText 类的话，根据实际情况继续，直到拿到 ezcMailText 类为止
                $ezcMailText = $mail[$i]->body;
                while (!isset($ezcMailText->text)) {
                    if (in_array('getParts', get_class_methods($ezcMailText))) {
                        $temp = $ezcMailText->getParts();
                        $ezcMailText = $temp[0];
                    } else if (in_array('getMainPart', get_class_methods($ezcMailText))) {
                        $ezcMailText = $ezcMailText->getMainPart();
                    }
                }
                $data['content'] = $ezcMailText->text;
                $data['size'] = $mail[$i]->size;
                $data['sendtime'] = $mail[$i]->timestamp;
                // 发件人
                $data['fromwebmail'] = $mail[$i]->from->email;
                // @todo 外部邮件接收 issend 写死是 1 是否正确?
                $data['issend'] = 1;
                // 附件
                $parts = $mail[$i]->fetchParts();
                $files = array();
                foreach ($parts as $k => $part) {
                    if ($part instanceof ezcMailFile) {
                        // 中文文件名需要解码
                        // base64 | quoted_printable_decode
                        // copy($part->fileName, basename($part->fileName));
                        $files[$k]['name'] = $part->fileName;
                        $files[$k]['contentType'] = $part->contentType;
                        $files[$k]['mimeType'] = $part->mimeType;
                        $files[$k]['size'] = $part->size;
                        // @todo 附件是否保存下来
                    }
                }
                $data['remoteattachment'] = serialize($files);
                $bodyId = EmailBody::model()->add($data, true);
                if ($bodyId) {
                    $emailData = array(
                        'toid' => $web['uid'],
                        'isread' => 0,
                        'fid' => $web['fid'],
                        'isweb' => 1,
                        'bodyid' => $bodyId
                    );
                    Email2::model()->add($emailData);
                }
                EmailWeb::model()->updateByPk($web['webid'], array('lastrectime' => TIMESTAMP));
            }
            return $return;
        } catch (ezcMailTransportException $exc) {
//                die($exc->getTraceAsString());
            // @todo 接收邮件异常处理
        }
    }

    /**
     * 保存邮件附件
     *
     * @param \ezcMailFilePart $parts
     * @return string 附件id，格式：1,2,3
     */
    protected static function saveAttach($parts)
    {
        $uid = (int)Ibos::app()->user->uid;

        $attachArr = array();
        foreach ($parts as $k => $part) {
            if ($part instanceof ezcMailFile) {
                // 添加附件
                $displayFileName = $part->contentDisposition->displayFileName;
                $fullName = self::getFullName($displayFileName);
                $fileSize = filesize($part->fileName);
                Ibos::engine()->io()->file()->uploadFile(File::getAttachUrl() . DIRECTORY_SEPARATOR . $fullName, $part->fileName);
                $aid = Attachment::model()->add(array('uid' => $uid, 'tableid' => 127), true);
                $data = array(
                    'aid' => $aid,
                    'uid' => $uid,
                    'dateline' => TIMESTAMP,
                    'filename' => $displayFileName,
                    'filesize' => $fileSize,
                    'attachment' => $fullName,
                    'isimage' => (int)StringUtil::isImageFile($fullName),
                );
                AttachmentUnused::model()->add($data);
                Attach::updateAttach($aid);
                $attachArr[] = $aid;
            }
        }

        return implode(',', $attachArr);
    }

    /**
     * 获取文件全名
     *
     * @param string $filename
     * @return string
     */
    protected static function getFullName($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return sprintf('email/%s/%s/%s.%s', date('Ym'), date('d'), StringUtil::random(25), $extension);
    }

}
