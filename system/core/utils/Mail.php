<?php

/**
 * email工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * email工具类，提供发送邮件，定时发送邮件，发送给用户功能
 * 依赖于后台 -> 全局 -> 邮件设置的设置
 *
 * @package application.core.utils
 * @version $Id: mail.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\core\model\Log;
use CJSON;

class Mail
{

    /**
     * 调用云服务发送邮件
     * @param string $to 接收人
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @param string $from 发送人
     * @return boolean 发送成功与否
     */
    public static function sendCloudMail($to, $subject, $message, $from = 'postmaster@ibos.sendcloud.org')
    {
        $data = array(
            'objects' => array(
                array(
                    'type' => 'email',
                    'to' => $to,
                    'message' => $message,
                    'params' => array(
                        'title' => $subject,
                        'from' => $from,
                        'urlparam' => Cloud::getInstance()->getCloudAuthParam(true)
                    )
                )
            )
        );
        $rs = Cloud::getInstance()->fetchPush($data);
        if (!is_array($rs)) {
            $res = CJSON::decode($rs, true);
            if ($res['code'] == '0') {
                return true;
            }
        }
        return false;
    }

    /**
     * 根据后台->全局->邮件设置发送邮件
     * @param string $to 接收人
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @param string $from 发送人
     * @return boolean 发送成功与否
     */
    public static function sendMail($to, $subject, $message, $from = 'IBOS2.0 MAIL CONTROL')
    {
        $setting = Ibos::app()->setting->toArray();
        $mail = $setting['setting']['mail'];
        if (!is_array($mail)) {
            $mail = StringUtil::utf8Unserialize($mail);
        }
        $smtpNums = count($mail['server']);
        if ($smtpNums) {
            // 多个server随机取出一个
            $randId = array_rand($mail['server'], 1);
            $server = $mail['server'][$randId];
            // 分隔符
            // 即使是在 Linux 系统下 \r 时也会无法识别邮箱头部信息
            // 统一使用 \n
            // $delimiter = $mail['maildelimiter'] == 1 ? "\r\n" : ($mail['maildelimiter'] == 2 ? "\r" : "\n");
            $delimiter = PHP_EOL;
            // 单位
            $unit = $setting['setting']['unit'];
            // 设置发送者
            if ($mail['mailsend'] == 2) {
                $emailFrom = empty($from) ? $unit['adminemail'] : $from;
            } else {
                $emailFrom = $from == '' ? '=?' . CHARSET . '?B?' . base64_encode($unit['fullname']) . "?= <" . $unit['adminemail'] . ">" : (preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats) ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $from);
            }
            // 接收人
            $emailTo = preg_match('/^(.+?) \<(.+?)\>$/', $to, $mats) ? ($mail['mailusername'] ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $to;
            // 标题
            $emailSubject = '=?' . CHARSET . '?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . $unit['fullname'] . '] ' . $subject)) . '?=';
            // 内容
            $emailMessage = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));
            $host = $_SERVER['HTTP_HOST'];
            $version = 'IBOS ' . $setting['version'];
            $headers = sprintf("From: %s%s", $emailFrom, $delimiter);
            $headers .= sprintf("X-Priority: 3%s", $delimiter);
            $headers .= sprintf("X-Mailer: %s %s%s", $host, $version, $delimiter);
            $headers .= sprintf("MIME-Version: 1.0%s", $delimiter);
            $headers .= sprintf("Content-type: text/html; charset=%s%s", CHARSET, $delimiter);
            $headers .= sprintf("Content-Transfer-Encoding: base64%s", $delimiter);
            // $headers = "From: {$emailFrom}\r\nX-Priority: 3\r\nX-Mailer: {$host} {$version} \r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=" . CHARSET . "\r\nContent-Transfer-Encoding: base64\r\n";
            // socket方式发送
            if ($mail['mailsend'] == 1) {
                if (!$fp = Env::getSocketOpen($server['server'], $errno, $errstr, $server['port'], 30)) {
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) CONNECT - Unable to connect to the SMTP server", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }
                stream_set_blocking($fp, true);
                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != '220') {
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) CONNECT - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }

                fputs($fp, ($server['auth'] ? 'EHLO' : 'HELO') . " ibos\r\n");
                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != 220 && substr($lastMessage, 0, 3) != 250) {
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) HELO/EHLO - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }

                while (1) {
                    if (substr($lastMessage, 3, 1) != '-' || empty($lastMessage)) {
                        break;
                    }
                    $lastMessage = fgets($fp, 512);
                }

                if ($server['auth']) {
                    fputs($fp, "AUTH LOGIN\r\n");
                    $lastMessage = fgets($fp, 512);
                    if (substr($lastMessage, 0, 3) != 334) {
                        Log::write(array('msg' => "({$server['server']}:{$server['port']}) AUTH LOGIN - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                        return false;
                    }
                    /*
                     * 在后台全局->邮箱设置正确的邮箱信息后，校验时运行到这里错误
                     * 没有检查邮箱是否开启相应的服务（pop/smtp/imap）
                     */
                    fputs($fp, base64_encode($server['username']) . "\r\n");
                    $lastMessage = fgets($fp, 512);
                    if (substr($lastMessage, 0, 3) != 334) {
                        Log::write(array('msg' => "({$server['server']}:{$server['port']}) USERNAME - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                        return false;
                    }

                    fputs($fp, base64_encode($server['password']) . "\r\n");
                    $lastMessage = fgets($fp, 512);
                    if (substr($lastMessage, 0, 3) != 235) {
                        Log::write(array('msg' => "({$server['server']}:{$server['port']}) PASSWORD - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                        return false;
                    }

                    $emailFrom = $server['from'];
                }

                fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $emailFrom) . ">\r\n");
                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != 250) {
                    fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $emailFrom) . ">\r\n");
                    $lastMessage = fgets($fp, 512);
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) MAIL FROM  - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }
                fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $to) . ">\r\n");
                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != 250) {
                    fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $to) . ">\r\n");
                    $lastMessage = fgets($fp, 512);
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) RCPT TO - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }

                fputs($fp, "DATA\r\n");
                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != 354) {
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) DATA - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                    return false;
                }
                $timeOffset = $setting['setting']['timeoffset'];
                if (function_exists('date_default_timezone_set')) {
                    @date_default_timezone_set('Etc/GMT' . ($timeOffset > 0 ? '-' : '+') . (abs($timeOffset)));
                }

                $headers .= 'Message-ID: <' . date('YmdHs') . '.' . substr(md5($emailMessage . microtime()), 0, 6) . rand(100000, 999999) . '@' . $_SERVER['HTTP_HOST'] . ">{$delimiter}";
                fputs($fp, "Date: " . date('r') . "\r\n");
                fputs($fp, "To: " . $emailTo . "\r\n");
                fputs($fp, "Subject: " . $emailSubject . "\r\n");
                fputs($fp, $headers . "\r\n");
                fputs($fp, "\r\n\r\n");
                fputs($fp, "{$emailMessage}\r\n.\r\n");

                $lastMessage = fgets($fp, 512);
                if (substr($lastMessage, 0, 3) != 250) {
                    Log::write(array('msg' => "({$server['server']}:{$server['port']}) END - {$lastMessage}", 'type' => 'SMTP'), 'action', 'sendMail');
                }
                fputs($fp, "QUIT\r\n");
                return true;
            } elseif ($mail['mailsend'] == 2) { // php smtp 发送
                ini_set('SMTP', $server['server']);
                ini_set('smtp_port', $server['port']);
                ini_set('sendmail_from', $emailFrom);
                if (function_exists('mail') && @mail($emailTo, $emailSubject, $emailMessage, $headers)) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }

}