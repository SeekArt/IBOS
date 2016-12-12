<?php

/**
 * 转换工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 转换工具类,提供字符串及数字类型的转换，如字节数，颜色值，时间值等
 *
 * @package application.utils
 * @version $Id: convert.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\extensions\chinese\Chinese;

class Convert
{

    /**
     * 字节格式化单位
     * @param integer $size 大小(字节)
     * @return string 返回格式化后的文本
     */
    public static function sizeCount($size)
    {
        if ($size >= 1073741824) {
            $size = round($size / 1073741824 * 100) / 100 . ' GB';
        } elseif ($size >= 1048576) {
            $size = round($size / 1048576 * 100) / 100 . ' MB';
        } elseif ($size >= 1024) {
            $size = round($size / 1024 * 100) / 100 . ' KB';
        } else {
            $size = $size . ' Bytes';
        }
        return $size;
    }

    /**
     * 格式化时间
     * @param string $timestamp 时间戳
     * @param string $format dt=日期时间 d=日期 t=时间 u=个性化 其他=自定义 默认为'dt'
     * @param string $timeOffset 时区偏移
     * @param string $uformat 用户自定义格式
     * @return string
     */
    public static function formatDate($timestamp, $format = 'dt', $timeOffset = '9999', $uformat = '')
    {
        $setting = Ibos::app()->setting->get('setting');
        $dateConvert = $setting['dateconvert'];
        if ($format == 'u' && !$dateConvert) {
            $format = 'dt';
        }
        $dateFormat = $setting['dateformat'];
        $timeFormat = $setting['timeformat'];
        $dayTimeFormat = $dateFormat . ' ' . $timeFormat;
        $offset = $setting['timeoffset'];

        $timeOffset = ($timeOffset == '9999') ? $offset : $timeOffset;
        $timestamp += $timeOffset * 3600;
        if (empty($format) || $format == 'dt') {
            $format = $dayTimeFormat;
        } elseif ($format == 'd') {
            $format = $dateFormat;
        } elseif ($format == 't') {
            $format = $timeFormat;
        }
        if ($format == 'u') {
            $todayTimestamp = TIMESTAMP - (TIMESTAMP + $timeOffset * 3600) % 86400 + $timeOffset * 3600;
            $outputStr = gmdate(!$uformat ? $dayTimeFormat : $uformat, $timestamp);
            $time = TIMESTAMP + $timeOffset * 3600 - $timestamp;
            if ($timestamp >= $todayTimestamp) {
                $replace = array('{outputStr}' => $outputStr);
                if ($time > 3600) {
                    $replace['{outputTime}'] = intval($time / 3600);
                    $returnTimeStr = Ibos::lang('Time greaterthan 3600', 'date', $replace);
                } elseif ($time > 1800) {
                    $returnTimeStr = Ibos::lang('Time greaterthan 1800', 'date', $replace);
                } elseif ($time > 60) {
                    $replace['{outputTime}'] = intval($time / 60);
                    $returnTimeStr = Ibos::lang('Time greaterthan 60', 'date', $replace);
                } elseif ($time > 0) {
                    $replace['{outputTime}'] = $time;
                    $returnTimeStr = Ibos::lang('Time greaterthan 0', 'date', $replace);
                } elseif ($time == 0) {
                    $returnTimeStr = Ibos::lang('Time equal 0', 'date', $replace);
                } else {
                    return $outputStr;
                }
                return $returnTimeStr;
            } elseif (($days = intval(($todayTimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
                $replace = array(
                    '{outputStr}' => $outputStr,
                    '{outputDay}' => gmdate($timeFormat, $timestamp)
                );
                if ($days == 0) {
                    $returnTimeStr = Ibos::lang('Day equal 0', 'date', $replace);
                } elseif ($days == 1) {
                    $returnTimeStr = Ibos::lang('Day equal 1', 'date', $replace);
                } else {
                    $replace['{outputDay}'] = $days + 1;
                    $returnTimeStr = Ibos::lang('Day equal else', 'date', $replace);
                }
                return $returnTimeStr;
            } else {
                return $outputStr;
            }
        } else {
            $returnTimeStr = gmdate($format, $timestamp);
            return $returnTimeStr;
        }
    }

    /**
     * RGB 转 十六进制
     * @param string $rgb RGB颜色的字符串 如：rgb(255,255,255);
     * @return string 十六进制颜色值 如：#FFFFFF
     */
    public static function RGBToHex($rgb)
    {
        $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
        $re = preg_match($regexp, $rgb, $match);
        $re = array_shift($match);
        $hexColor = "#";
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        for ($i = 0; $i < 3; $i++) {
            $r = null;
            $c = $match[$i];
            $hexAr = array();
            while ($c > 16) {
                $r = $c % 16;

                $c = ($c / 16) >> 0;
                array_push($hexAr, $hex[$r]);
            }
            array_push($hexAr, $hex[$c]);

            $ret = array_reverse($hexAr);
            $item = implode('', $ret);
            $item = str_pad($item, 2, '0', STR_PAD_LEFT);
            $hexColor .= $item;
        }
        return $hexColor;
    }

    /**
     * 十六进制颜色转 RGB
     * @param string $hexColor 十六颜色 ,如：#ff00ff
     * @return array RGB数组
     */
    public static function hexColorToRGB($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = str_replace('#', '', $hexColor);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }

    /**
     * 转换IP
     * @param string $ip 要转换的ip
     * @return string
     */
    public static function convertIp($ip)
    {
        $return = '';
        if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
            $ipArr = explode('.', $ip);
            if ($ipArr[0] == 10 || $ipArr[0] == 127 || ($ipArr[0] == 192 && $ipArr[1] == 168) || ($ipArr[0] == 172 && ($ipArr[1] >= 16 && $ipArr[1] <= 31))) {
                $return = '- LAN';
            } elseif ($ipArr[0] > 255 || $ipArr[1] > 255 || $ipArr[2] > 255 || $ipArr[3] > 255) {
                $return = '- Invalid IP Address';
            } else {
                $tinyIpFile = 'data/ipdata/tiny.dat';
                $fullIpFile = 'data/ipdata/full.dat';
                if (@file_exists($tinyIpFile)) {
                    $return = self::convertTinyIp($ip, $tinyIpFile);
                } elseif (@file_exists($fullIpFile)) {
                    $return = self::convertFullIp($ip, $fullIpFile);
                }
            }
        }
        return $return;
    }

    /**
     * 转换IP (简单版本)
     * @staticvar null $fp
     * @staticvar array $offset
     * @staticvar null $index
     * @param type $ip
     * @param type $ipDataFile
     * @return string
     */
    public static function convertTinyIp($ip, $ipDataFile)
    {

        static $fp = null, $offset = array(), $index = null;

        $ipdot = explode('.', $ip);
        $ip = pack('N', ip2long($ip));

        $ipdot[0] = (int)$ipdot[0];
        $ipdot[1] = (int)$ipdot[1];
        if ($fp === null && $fp = @fopen($ipDataFile, 'rb')) {
            $offset = @unpack('Nlen', @fread($fp, 4));
            $index = @fread($fp, $offset['len'] - 4);
        } elseif ($fp == false) {
            return '- Invalid IP data file';
        }
        $length = $offset['len'] - 1028;
        $start = @unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
        for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
            if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
                $indexOffset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
                $indexLength = @unpack('Clen', $index{$start + 7});
                break;
            }
        }
        @fseek($fp, $offset['len'] + $indexOffset['len'] - 1024);
        if ($indexLength['len']) {
            return '- ' . @fread($fp, $indexLength['len']);
        } else {
            return '- Unknown';
        }
    }

    /**
     * 转换IP (全格式ip数据库版本)
     * @param string $ip 要转换的ip地址
     * @param string $ipDataFile ip数据库文件
     * @return string 转换结果
     */
    public static function convertFullIp($ip, $ipDataFile)
    {

        if (!$fd = @fopen($ipDataFile, 'rb')) {
            return '- Invalid IP data file';
        }

        $ip = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

        if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4))) {
            return;
        }
        @$ipbegin = implode('', unpack('L', $DataBegin));
        if ($ipbegin < 0) {
            $ipbegin += pow(2, 32);
        }
        @$ipend = implode('', unpack('L', $DataEnd));
        if ($ipend < 0) {
            $ipend += pow(2, 32);
        }
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1 = $ipAddr2 = '';
        $EndNum = $ipAllNum;

        while ($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle = intval(($EndNum + $BeginNum) / 2);

            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if (strlen($ipData1) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if ($ip1num < 0) {
                $ip1num += pow(2, 32);
            }

            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }

            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if (strlen($ipData2) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if ($ip2num < 0) {
                $ip2num += pow(2, 32);
            }

            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    return '- Unknown';
                }
                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if (strlen($ipSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }

        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }

            $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }
        } else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }

            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }
        }
        fclose($fd);

        if (preg_match('/http/i', $ipAddr2)) {
            $ipAddr2 = '';
        }
        $ipaddr = "{$ipAddr1} {$ipAddr2}";
        $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
        $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
        if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
            $ipaddr = '- Unknown';
        }

        return '- ' . $ipaddr;
    }

    /**
     * 转换一个数组为字符串
     * @param array $array 待处理的数组
     * @param array $skip 要跳过处理的数组参数
     * @return string 处理后的字符串
     */
    public static function implodeArray($array, $skip = array())
    {
        $return = '';
        if (is_array($array) && !empty($array)) {
            foreach ($array as $key => $value) {
                if (empty($skip) || !in_array($key, $skip, true)) {
                    if (is_array($value)) {
                        $return .= "{$key}={" . self::implodeArray($value, $skip) . "}; ";
                    } elseif (!empty($value)) {
                        $return .= "{$key}={$value}; ";
                    } else {
                        $return .= '';
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
     *
     * @param array $pArray 一个二维数组
     * @param string $pKey 数组的键的名称
     * @param string $pCondition
     * @return array 返回新的一维数组
     */
    public static function getSubByKey($pArray, $pKey = "", $pCondition = "")
    {
        $result = array();
        if (is_array($pArray)) {
            foreach ($pArray as $tempArray) {
                if (is_object($tempArray)) {
                    $tempArray = (array)$tempArray;
                }
                if (("" != $pCondition && $tempArray[$pCondition[0]] == $pCondition[1]) || "" == $pCondition) {
                    $result[] = ("" == $pKey) ? $tempArray : isset($tempArray[$pKey]) ? $tempArray[$pKey] : "";
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 转换一个阿拉伯数字为中文数字
     * @param integer $num
     * @return string 转换后的中文数字
     */
    public static function ToChinaseNum($num)
    {
        $char = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $dw = array("", "十", "百", "千", "万", "亿", "兆");
        $retval = "";
        $proZero = false;
        for ($i = 0; $i < strlen($num); $i++) {
            if ($i > 0) {
                $temp = (int)(($num % pow(10, $i + 1)) / pow(10, $i));
            } else {
                $temp = (int)($num % pow(10, 1));
            }
            if ($proZero == true && $temp == 0) {
                continue;
            }

            if ($temp == 0) {
                $proZero = true;
            } else {
                $proZero = false;
            }

            if ($proZero) {
                if ($retval == "") {
                    continue;
                }
                $retval = $char[$temp] . $retval;
            } else {
                $retval = $char[$temp] . $dw[$i] . $retval;
            }
        }
        if ($retval == "一十") {
            $retval = "十";
        }
        return $retval;
    }

    /**
     * 编码转换
     * @param <string> $str 要转码的字符
     * @param <string> $inCharset 输入字符集
     * @param <string> $outCharset 输出字符集(默认当前)
     * @param <boolean> $ForceTable 强制使用码表(默认不强制)
     *
     */
    public static function iIconv($str, $inCharset, $outCharset = CHARSET, $forceTable = false)
    {

        $inCharset = strtoupper($inCharset);
        $outCharset = strtoupper($outCharset);

        if (empty($str) || $inCharset == $outCharset) {
            return $str;
        }

        $out = '';

        if (!$forceTable) {
            if (function_exists('iconv')) {
                $out = iconv($inCharset, $outCharset . '//IGNORE', $str);
            } elseif (function_exists('mb_convert_encoding')) {
                $out = mb_convert_encoding($str, $outCharset, $inCharset);
            }
        }

        if ($out == '') {
            $chinese = new Chinese($inCharset, $outCharset, true);
            $out = $chinese->Convert($str);
        }
        return $out;
    }

    /**
     * 获取$string的拼音
     * @param string $string 词条字符串
     * @param boolean $first 是否只取首字
     * @param bool $phonetic 是否返回音节
     * @return string
     */
    public static function getPY($string, $first = false, $phonetic = false)
    {
        // 多音字dat数据
        $pyDataPath = 'data/pydata/py.dat';

        // 缓存文件
        static $pyData = null;
        if (empty($pyData)) {
            $pyData = file_get_contents($pyDataPath, 'rb');
        }

        $in_code = strtoupper(CHARSET);
        $out_code = 'GBK';
        $strLen = mb_strlen($string, $in_code);

        // 拼音结果
        $pyStr = '';
        for ($i = 0; $i < $strLen; $i++) {
            $izh = mb_substr($string, $i, 1, $in_code);
            if (preg_match('/^[a-zA-Z0-9]$/', $izh)) {
                $pyStr .= $izh;
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $izh)) {
                // 只取纯汉字，其他非汉字符号一概忽略
                $char = iconv($in_code, $out_code, $izh);
                $high = ord($char[0]) - 0x81;
                $low = ord($char[1]) - 0x40;
                $offset = ($high << 8) + $low - ($high * 0x40);
                if ($offset >= 0) {
                    $p_arr = unpack('a8py', substr($pyData, $offset * 8, 8));
                    $py = isset($p_arr['py']) ? ($phonetic ? $p_arr['py'] : substr($p_arr['py'], 0, -1)) : '';
                    $pyStr .= $first ? $py[0] : '' . $py;
                }
            }
        }
        return $pyStr;
    }


    /**
     * 模拟JS的unescape函数，解码 %u6D4B %u8BD5 %u6A21 %u677F %u54AF 诸如此类的字符
     * @param string $str
     * @return string
     */
    public static function unescape($str)
    {
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            if ($str[$i] == '%' && $str[$i + 1] == 'u') {
                $val = hexdec(substr($str, $i + 2, 4));
                if ($val < 0x7f) {
                    $ret .= chr($val);
                } else if ($val < 0x800) {
                    $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f));
                } else {
                    $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
                }
                $i += 5;
            } else if ($str[$i] == '%') {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            } else {
                $ret .= $str[$i];
            }
        }
        return $ret;
    }

    /**
     * 通用导出CSV函数
     * @param string $name 文件名
     * @param array $header 头部
     * @param array $body 与头部表头对应的数据
     */
    public static function exportCsv($name, $header, $body)
    {
        set_time_limit(0);
        header("Content-Type:application/vnd.ms-excel");
        $fileName = iconv('utf-8', 'gbk', $name);
        header("Content-Disposition: attachment;filename={$fileName}.csv");
        header('Cache-Control: max-age = 0');
        foreach ($header as $i => $v) {
            //CSV的Excel支持GBK编码，一定要转换，否则乱码
            $header[$i] = iconv('utf-8', 'gbk', $v);
        }
        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');
        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $header);
        //计数器
        $cnt = 0;
        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100;
        foreach ($body as $row) {
            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $cnt) {
                ob_flush();
                flush();
                $cnt = 0;
            }
            $cnt++;
            fputcsv($fp, $row);
        }
        exit();
    }

}
