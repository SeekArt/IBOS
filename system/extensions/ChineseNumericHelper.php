<?php
/**
 * 阿拉伯数字转中文数字
 *
 * @namespace application\extensions
 * @filename ChineseNumerals.php
 * @encoding UTF-8
 * @author mouson
 * @link https://github.com/mouson/chinese-numerals
 * @datetime 2016/10/22 16:37
 */

namespace application\extensions;


class ChineseNumericHelper
{
    static protected $map = array(
        'LoCaseNumber' => array('', '一', '二', '三', '四', '五', '六', '七', '八', '九'),
        'UpCaseNumber' => array('', '壹', '貳', '參', '肆', '伍', '陸', '柒', '捌', '玖'),
        'LoCaseLowUnit' => array('', '十', '百', '千'),
        'UpCaseLowUnit' => array('', '拾', '佰', '仟'),
        'HighUnit' => array(
            '', '萬', '億', '兆', '京', '垓', '秭', '穰', '溝', '澗', '正', '載',
            '極', '恆河沙', '阿僧祇', '那由他', '不可思議', '無量大數'
        )
    );

    /**
     * 阿拉伯數字轉中文數字工具
     *
     * @param  int|double|string $number 要轉換的數字，String僅允許數字，否則回傳false
     * @param  bool $is_upper_case 轉換的結果為大寫或小寫中文數字
     *
     * @return bool|string         bool 當轉換錯誤時回傳 false
     *                               string 當轉換正確時回傳中文數字字串
     */
    public static function numeric2Chinese($number, $is_upper_case = false)
    {
        // 輸入非數字
        if (!is_numeric($number)) {
            return false;
        }

        // 超過 PHP 數字限制，要回傳 false
        if (
            (is_float($number) || is_int($number))
            && $number > PHP_INT_MAX
        ) {
            return false;
        }
        $num_str = (string)$number;

        $sign = "";
        if (strpos($num_str, '-') !== false) {
            $num_str = substr($num_str, 1);
            $sign = "負";
        }

        // 輸入 0 則顯示 零
        if ($num_str == "0") {
            return "零";
        }

        // 存在小數點，不處理
        if (strpos($num_str, '.') !== false) {
            return false;
        }

        $ret = '';
        $len = strlen($num_str);

        // 超過 10^73 次方，沒有單位可顯示 回傳 false
        if ($len >= 73) {
            return false;
        }

        for ($i = 0; $i < $len; $i++) {
            $num = $num_str[$i];
            // 補前面的零
            // 當該處理的數非零，而上一位數字為零
            if ($num != 0 && $num_str[($i - 1 > 0) ? $i - 1 : 0] == 0) {
                $ret .= "零";
            }
            $pos = $len - ($i + 1);

            // 取數字中文值
            // 當居最高位數且為 10 ~ 19 時，不讀一
            // Ex:
            // 15 讀 十五 非 一十五 但 115 讀 一百一十五
            // 110000 讀 十一萬 非 一十一萬
            if (!(($i == 0) && ($len % 4) == 2 && $num == 1)) {
                if ($is_upper_case) {
                    $ret .= self::$map['UpCaseNumber'][$num];
                } else {
                    $ret .= self::$map['LoCaseNumber'][$num];
                }
            }

            // 取十為基底的單位
            if ($pos % 4 != 0 && $num != 0) {
                if ($is_upper_case) {
                    $ret .= self::$map['UpCaseLowUnit'][$pos % 4];
                } else {
                    $ret .= self::$map['LoCaseLowUnit'][$pos % 4];
                }
            }

            // 取萬為基底的單位
            if ($pos % 4 == 0) {
                if (substr($number, ($i - 3 > 0) ? $i - 3 : 0, 4) != "0000") {
                    $ret .= self::$map['HighUnit'][$pos / 4];
                }
            }
        }
        // 判斷式否增加負數符號
        return ($sign !== "" ? $sign . $ret : $ret);
    }
}