<?php

namespace application\modules\email\utils;

use application\core\utils\Convert;

class EmailLang
{

    public static function langDecodeAddressList($str, $charset, $web)
    {
        $a = self::langParseAddressList($str);
        $res = '';
        if (is_array($a)) {
            $j = 0;
            reset($a);
            while (list($i, $val) = each($a)) {
                $j++;
                $address = $a[$i]["address"];
                $name = str_replace("\"", "", $a[$i]["name"]);
                $res .= self::langFormAddressHTML($web, $name, $address, $charset) . ',';
            }
        }

        return $res;
    }

    /**
     * 解码邮件MIME的字符串，一般是邮件标题
     * @param type $str
     * @return type
     */
    public static function langDecodeMimeString($str)
    {
        $a = explode("?", $str);
        $count = count($a);
        if ($count >= 3) {   //should be in format "charset?encoding?base64_string"
            $rest = '';
            for ($i = 2; $i < $count; $i++) {
                $rest .= $a[$i];
            }
            if (($a[1] == "B") || ($a[1] == "b")) {
                $rest = base64_decode($rest);
                $rest = Convert::iIconv($rest, $a[0]);
                return $rest;
            } else if (($a[1] == "Q") || ($a[1] == "q")) {
                $rest = str_replace("_", " ", $rest);
                return quoted_printable_decode($rest);
            }
        } else {
            return $str;  //we dont' know what to do with this
        }
    }

    /**
     * 解码邮件标题
     * @param string $input base64编码的字符串
     * @param string $charset 要转换的编码
     * @return string 解码后的字符串
     */
    public static function langDecodeSubject($input, $charset)
    {
        $out = "";
        $pos = strpos($input, "=?");
        if ($pos !== false) {
            $out = substr($input, 0, $pos);
            $end_cs_pos = strpos($input, "?", $pos + 2);
            $end_en_pos = strpos($input, "?", $end_cs_pos + 1);
            $end_pos = strpos($input, "?=", $end_en_pos + 1);
            $encstr = substr($input, $pos + 2, ($end_pos - $pos - 2));
            $rest = substr($input, $end_pos + 2);
            $out .= self::langDecodeMimeString($encstr, $charset);
            $out .= self::langDecodeSubject($rest, $charset);
            return $out;
        } else {
            return self::langConvert($input, $charset, $charset);
        }
    }

    public static function langDisableHTML($str)
    {
        $result = $str;
        $result = str_replace("<", "&lt;", $result);
        $result = str_replace(">", "&gt;", $result);
        return $result;
    }

    public static function langEncode8bitLatin($str)
    {
        // following code inspired by SquirrelMail's
        // charset_decode_utf8 in utf-8.php
        /* Only do the slow convert if there are 8-bit characters */
        /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
        if (!preg_match("/[\200-\237]/", $str) and !preg_match("/[\241-\377]/", $str)) {
            return $str;
        }
        // encode 8-bit ISO-8859-1 into HTML entities 
        // works because Unicode uses ISO-8859-1 for those ranges
        $str = preg_replace("/([\200-\377])/e", "'&#'.(ord('\\1')).';'", $str);
        return $str;
    }

    public static function langIs8Bit($string)
    {
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            if (ord($string[$i]) >= 128) {
                return true;
            }
        }
        return false;
    }

    public static function langEncodeSubject($input)
    {
        if (self::langIs8Bit($input)) {
            return "=?UTF-8?B?" . base64_encode($input) . "?=";
        } else {
            return $input;
        }
    }

    public static function langEncodeMessage($input, $charset)
    {
        $message = $input;
        $result["type"] = "Content-Type: text/plain; charset=\"{$charset}\"\r\n";
        $result["encoding"] = "";
        $result["data"] = $message;

        return $result;
    }

    public static function langEncodeAddressList($str, $charset)
    {
        $str = str_replace(", ", ",", $str);
        $str = str_replace(",", ", ", $str);
        $str = str_replace("; ", ";", $str);
        $str = str_replace(";", "; ", $str);

        $a = self::langExplodeQuotedString(" ", $str);
        if (is_array($a)) {
            $c = count($a);
            for ($i = 0; $i < $c; $i++) {
                if ((strpos($a[$i], "@") > 0) && (strpos($a[$i], ".") > 0)) {
                    //probably an email address, leave it alone
                } else {
                    //some string, encode
                    $word = stripslashes($a[$i]);
                    $len = strlen($word);
                    $enc = self::langEncodeSubject(str_replace("\"", "", $word), $charset);
                    if (($word[0] == "\"") && ($word[$len - 1] == "\"")) {
                        $enc = "\"" . $enc . "\"";
                    }
                    $a[$i] = $enc;
                }
            }
            return implode(" ", $a);
        } else {
            return $str;
        }
    }

    public static function langExplodeQuotedString($delimiter, $string)
    {
        $quotes = explode("\"", $string);
        while (list($key, $val) = each($quotes)) {
            if (($key % 2) == 1) {
                $quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
            }
        }
        $string = implode("\"", $quotes);
        $result = explode($delimiter, $string);
        while (list($key, $val) = each($result)) {
            $result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);
        }

        return $result;
    }

    public static function langFormAddressHTML($web, $name, $address, $charset)
    {
        $target = "_blank";
        if (empty($name)) {
            $name = $address;
        }
        $decoded_name = self::langDecodeSubject($name, $charset);
        if (strpos($decoded_name, " ") !== false) {
            $q_decoded_name = "\"" . $decoded_name . "\"";
        } else {
            $q_decoded_name = $decoded_name;
        }
        $url = "compose2.php?user=" . $web['webid'] . "&to=" . urlencode($q_decoded_name . " <" . $address . ">");
        $res = "";
        $res .= "<a href=\"$url\" target=\"$target\">" . self::langDisableHTML($decoded_name) . "</a>";
        $res .= "[<a href=\"edit_contact.php?user={$web['webid']}&name=" . urlencode($decoded_name) . "&email=" . urlencode($address) . "&edit=-1\">+</a>]";
        return $res;
    }

    public static function langFormatDate($timestamp, $format)
    {
        $date = getdate($timestamp);
        $result = $format;
        $result = str_replace("%d", $date["mday"], $result);
        $result = str_replace("%m", $date["mon"], $result);
        $result = str_replace("%y", $date["year"], $result);
        $result = str_replace("%t", $date["hour"] . ":" . $date["minutes"], $result);
        $result = str_replace("%S", date('S', $timestamp), $result);

        return $result;
    }

    public static function langFormatIntTime($time, $system, $ampm, $format)
    {
        //purpose: take "930" and format as "9:30am" or "0930" as necessary
        $min_pos = strlen($time) - 2;
        $hours = substr($time, 0, $min_pos);
        $minutes = substr($time, $min_pos);
        if ($system == 12) {
            if ($hours >= 12) {
                if ($hours > 12) {
                    $hours -= 12;
                }
                $a = "pm";
            } else {
                $a = "am";
            }
        }
        $result = $format;
        if (!$hours) {
            $hours = "00";
        }
        if (!$minutes) {
            $minutes = "00";
        }
        $result = str_replace("%h", $hours, $result);
        $result = str_replace("%m", $minutes, $result);
        $result = str_replace("%a", $ampm[$a], $result);
        return $result;
    }

    public static function langInsertStringsFromAK($dest, $source_a)
    {
        if (!is_array($source_a)) {
            return $dest;
        } else {
            while (list($key, $val) = each($source_a)) {
                $place_holder = "%" . $key;
                $dest = str_replace($place_holder, $val, $dest);
            }
        }
        return $dest;
    }

    public static function langGetParseAddressList($str, $delimeter = ';')
    {
        $arr = self::langParseAddressList($str);
        $address = Convert::getSubByKey($arr, 'address');
        return implode($delimeter, $address);
    }

    public static function langParseAddressList($str)
    {
        $str = trim($str, ',');
        $a = self::langExplodeQuotedString(",", $str);
        $result = array();
        reset($a);
        while (list($key, $val) = each($a)) {
            $val = str_replace("\"<", "\" <", $val);
            $sub_a = self::langExplodeQuotedString(" ", $val);
            reset($sub_a);
            $result[$key]["address"] = $result[$key]["name"] = '';
            while (list($k, $v) = each($sub_a)) {
                if ((strpos($v, "@") > 0) && (strpos($v, ".") > 0)) {
                    $result[$key]["address"] = str_replace("<", "", str_replace(">", "", $v));
                } else {
                    $result[$key]["name"] .= (empty($result[$key]["name"]) ? "" : " ") . str_replace("\"", "", stripslashes($v));
                }
            }
            if (empty($result[$key]["name"])) {
                $result[$key]["name"] = $result[$key]["address"];
            }
        }
        return $result;
    }

    public function langShowAddresses($str)
    {
        $a = self::langParseAddressList($str);
        if (is_array($a)) {
            $c = count($a);
            $j = 0;
            reset($a);
            while (list($i, $val) = each($a)) {
                $j++;
                $address = $a[$i]["address"];
                $name = str_replace("\"", "", $a[$i]["name"]);
                $res .= htmlspecialchars("\"$name\" <$address>");
                if ((($j % 3) == 0) && (($c - $j) > 1)) {
                    $res .= ",<br>&nbsp;&nbsp;&nbsp;";
                } else if ($c > $j) {
                    $res .= ",&nbsp;";
                }
            }
        }
        return $res;
    }

    public static function langSmartWrap($text, $len)
    {
        $lines = explode("\n", $text);

        if (!is_array($lines)) {
            return "";
        }

        while (list($i, $line) = each($lines)) {
            if (!ereg("^>", $line)) {
                $lines[$i] = self::langWrapLine(chop($line), $len);
            }
        }

        return implode("\n", $lines);
    }

    public static function langWrapLine($line, $width)
    {
        $line_len = strlen($line);
        $i = 0;

        //if line is less than width, we're good
        if ($line_len <= $width) {
            return $line;
        }

        for ($prev_i = 0, $i = $width; $i < $line_len; $prev_i = $i, $i += $width) {
            //extract last segment that is $width wide
            $chunk = substr($line, $prev_i, ($i - $prev_i)) . "\n";
            //find last space in this chunk
            $last_space = strrpos($chunk, " ");
            $last_space = $prev_i + $last_space;

            if ($last_space == $prev_i) {
                //no space found in this chunk
                $next_space = strpos($line, " ", $i);
                if ($next_space !== false) {
                    $i = $next_space;
                    $line[$next_space] = "\n";
                }
            } else {
                //replace last space before width with newline
                $line[$last_space] = "\n";
                $i = $last_space;
            }
        }

        return $line;
    }

    public static function langConvert($string, $charset, $charset2)
    {
        if ($charset != $charset2) {
            return utf8_encode($string);
        } else {
            return $string;
        }
    }

    public static function utf8ToUnicodeEntities($source)
    {
        // array used to figure what number to decrement from character order value
        // according to number of characters used to map unicode to ascii by utf-8
        $decrement[4] = 240;
        $decrement[3] = 224;
        $decrement[2] = 192;
        $decrement[1] = 0;

        // the number of bits to shift each charNum by
        $shift[1][0] = 0;
        $shift[2][0] = 6;
        $shift[2][1] = 0;
        $shift[3][0] = 12;
        $shift[3][1] = 6;
        $shift[3][2] = 0;
        $shift[4][0] = 18;
        $shift[4][1] = 12;
        $shift[4][2] = 6;
        $shift[4][3] = 0;

        $pos = 0;
        $len = strlen($source);
        $encodedString = '';
        while ($pos < $len) {
            $asciiPos = ord(substr($source, $pos, 1));
            if (($asciiPos >= 240) && ($asciiPos <= 255)) {
                // 4 chars representing one unicode character
                $thisLetter = substr($source, $pos, 4);
                $pos += 4;
            } else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
                // 3 chars representing one unicode character
                $thisLetter = substr($source, $pos, 3);
                $pos += 3;
            } else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
                // 2 chars representing one unicode character
                $thisLetter = substr($source, $pos, 2);
                $pos += 2;
            } else {
                // 1 char (lower ascii)
                $thisLetter = substr($source, $pos, 1);
                $pos += 1;
            }

            // process the string representing the letter to a unicode entity
            $thisLen = strlen($thisLetter);
            $thisPos = 0;
            $decimalCode = 0;
            while ($thisPos < $thisLen) {
                $thisCharOrd = ord(substr($thisLetter, $thisPos, 1));
                if ($thisPos == 0) {
                    $charNum = intval($thisCharOrd - $decrement[$thisLen]);
                    $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                } else {
                    $charNum = intval($thisCharOrd - 128);
                    $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                }

                $thisPos++;
            }

            if ($thisLen == 1) {
                $encodedLetter = "&#" . str_pad($decimalCode, 3, "0", STR_PAD_LEFT) . ';';
            } else {
                $encodedLetter = "&#" . str_pad($decimalCode, 5, "0", STR_PAD_LEFT) . ';';
            }

            $encodedString .= $encodedLetter;
        }

        return $encodedString;
    }

}
