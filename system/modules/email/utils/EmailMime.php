<?php

namespace application\modules\email\utils;

class EmailMime
{

    const MIME_INVALID = -1;
    const MIME_TEXT = 0;
    const MIME_MULTIPART = 1;
    const MIME_MESSAGE = 2;
    const MIME_APPLICATION = 3;
    const MIME_AUDIO = 4;
    const MIME_IMAGE = 5;
    const MIME_VIDEO = 6;
    const MIME_OTHER = 7;

    public static function getFirstTextPart($structure, $part)
    {
        if ($part == 0) {
            $part = "";
        }
        $typeCode = -1;
        while ($typeCode != 0) {
            $typeCode = self::getPartTypeCode($structure, $part);
            if ($typeCode == 1) {
                $part .= (empty($part) ? "" : ".") . "1";
            } else if ($typeCode > 0) {
                $parts_a = explode(".", $part);
                $lastPart = count($parts_a) - 1;
                $parts_a[$lastPart] = (int)$parts_a[$lastPart] + 1;
                $part = implode(".", $parts_a);
            } else if ($typeCode == -1) {
                return "";
            }
        }

        return $part;
    }

    public static function getNextPart($part)
    {
        if (strpos($part, ".") === false) {
            return $part++;
        } else {
            $parts_a = explode(".", $part);
            $num_levels = count($parts_a);
            $parts_a[$num_levels - 1]++;
            return implode(".", $parts_a);
        }
    }

    public static function getNumParts($a, $part = null)
    {
        if (is_array($a)) {
            $parent = self::getPartArray($a, $part);
            if (is_string($parent[0]) && is_string($parent[1]) && (strcasecmp($parent[0], "message") == 0) && (strcasecmp($parent[1], "rfc822") == 0)) {
                $parent = $parent[8];
            }

            $is_array = true;
            $c = 0;
            while ((list ($key, $val) = each($parent)) && ($is_array)) {
                $is_array = is_array($parent[$key]);
                if ($is_array) {
                    $c++;
                }
            }
            return $c;
        }
        return false;
    }

    public static function getPartArray($a, $part = null)
    {
        if (!is_array($a)) {
            return false;
        }
        if (strpos($part, ".") > 0) {
            $original_part = $part;
            $pos = strpos($part, ".");
            $rest = substr($original_part, $pos + 1);
            $part = substr($original_part, 0, $pos);
            if (is_string($a[0]) && is_string($a[1]) && (strcasecmp($a[0], "message") == 0) && (strcasecmp($a[1], "rfc822") == 0)) {
                $a = $a[8];
            }
            return self::getPartArray($a[$part - 1], $rest);
        } else if ($part > 0) {
            if (is_string($a[0]) && is_string($a[1]) && (strcasecmp($a[0], "message") == 0) && (strcasecmp($a[1], "rfc822") == 0)) {
                $a = $a[8];
            }
            if (is_array($a[$part - 1])) {
                return $a[$part - 1];
            } else {
                return false;
            }
        } else if (($part == 0) || (empty($part))) {
            return $a;
        }
    }

    public static function getPartCharset($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                if (is_array($part_a[2])) {
                    $name = "";
                    while (list($key, $val) = each($part_a[2])) {
                        if (strcasecmp($val, "charset") == 0) {
                            $name = $part_a[2][$key + 1];
                        }
                    }
                    return $name;
                } else {
                    return "";
                }
            }
        } else {
            return "";
        }
    }

    public static function getPartDisposition($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                $id = count($part_a) - 2;
                if (is_array($part_a[$id])) {
                    return $part_a[$id][0];
                } else {
                    return "";
                }
            }
        } else {
            return "";
        }
    }

    public static function getPartEncodingCode($a, $part)
    {
        $encodings = array("7BIT", "8BIT", "BINARY", "BASE64", "QUOTED-PRINTABLE", "OTHER");
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                $str = $part_a[5];
            }
            $code = 5;
            while (list($key, $val) = each($encodings)) {
                if (strcasecmp($val, $str) == 0) {
                    $code = $key;
                }
            }
            return $code;
        } else {
            return -1;
        }
    }

    public function getPartEncodingString($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                return $part_a[5];
            }
        } else {
            return -1;
        }
    }

    public static function getPartID($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                return $part_a[3];
            }
        } else {
            return -1;
        }
    }

    public static function getPartList($a, $part)
    {
        //echo "MOO?"; flush();
        $data = array();
        $num_parts = self::getNumParts($a, $part);
        //echo "($num_parts)"; flush();
        if ($num_parts !== false) {
            //echo "<!-- ($num_parts parts)//-->\n";
            for ($i = 0; $i < $num_parts; $i++) {
                $part_code = $part . (empty($part) ? "" : ".") . ($i + 1);
                $part_type = self::getPartTypeCode($a, $part_code);
                $part_disposition = self::getPartDisposition($a, $part_code);
                //echo "<!-- part: $part_code type: $part_type //-->\n";
                if (strcasecmp($part_disposition, "attachment") != 0 &&
                    (($part_type == 1) || ($part_type == 2))
                ) {
                    $data = array_merge($data, self::getPartList($a, $part_code));
                } else {
                    $data[$part_code]["typestring"] = self::getPartTypeString($a, $part_code);
                    $data[$part_code]["disposition"] = $part_disposition;
                    $data[$part_code]["size"] = self::getPartSize($a, $part_code);
                    $data[$part_code]["name"] = self::getPartName($a, $part_code);
                    $data[$part_code]["id"] = self::getPartID($a, $part_code);
                }
            }
        }
        return $data;
    }

    public static function getPartSize($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                return $part_a[6];
            }
        } else {
            return -1;
        }
    }

    public static function getPartTypeCode($a, $part = null)
    {
        $types = array(
            0 => "text",
            1 => "multipart",
            2 => "message",
            3 => "application",
            4 => "audio",
            5 => "image",
            6 => "video",
            7 => "other"
        );
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                $str = "multipart";
            } else {
                $str = $part_a[0];
            }
            $code = 7;
            while (list($key, $val) = each($types)) {
                if (strcasecmp($val, $str) == 0) {
                    $code = $key;
                }
            }
            return $code;
        } else {
            return -1;
        }
    }

    public static function parseBSString($str)
    {
        $id = 0;
        $a = array();
        $len = strlen($str);
        $in_quote = 0;
        $a[$id] = '';
        for ($i = 0; $i < $len; $i++) {
            if ($str[$i] == "\"") {
                $in_quote = ($in_quote + 1) % 2;
            } else if (!$in_quote) {
                if ($str[$i] == " ") {
                    $id++; //space means new element
                    $a[$id] = '';
                } else if ($str[$i] == "(") { //new part
                    $i++;
                    $endPos = self::closingParenPos($str, $i);
                    $partLen = $endPos - $i;
                    $part = substr($str, $i, $partLen);
                    $a[$id] = self::parseBSString($part); //send part string
                    $i = $endPos;
                } else {
                    $a[$id] .= $str[$i]; //add to current element in array
                }
            } else if ($in_quote) {
                if ($str[$i] == "\\") {
                    $i++; //escape backslashes
                } else {
                    $a[$id] .= $str[$i]; //add to current element in array
                }
            }
        }

        reset($a);
        return $a;
    }

    public static function closingParenPos($str, $start)
    {
        $level = 0;
        $len = strlen($str);
        $in_quote = 0;
        for ($i = $start; $i < $len; $i++) {
            if ($str[$i] == "\"") {
                $in_quote = ($in_quote + 1) % 2;
            }
            if (!$in_quote) {
                if ($str[$i] == "(") {
                    $level++;
                } else if (($level > 0) && ($str[$i] == ")")) {
                    $level--;
                } else if (($level == 0) && ($str[$i] == ")")) {
                    return $i;
                }
            }
        }
    }

    public static function getRawStructureArray($str)
    {
        $line = substr($str, 1, strlen($str) - 2);
        $line = str_replace(")(", ") (", $line);
        $struct = self::parseBSString($line);
        if (is_string($struct[0]) && is_string($struct[1]) && (strcasecmp($struct[0], "message") == 0) && (strcasecmp($struct[1], "rfc822") == 0)) {
            $struct = array($struct);
        }
        return $struct;
    }

    public static function getPartTypeString($a, $part = null)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                $type_str = "MULTIPART/";
                reset($part_a);
                while (list($n, $element) = each($part_a)) {
                    if (!is_array($part_a[$n])) {
                        $type_str .= $part_a[$n];
                        break;
                    }
                }
                return $type_str;
            } else {
                return $part_a[0] . "/" . $part_a[1];
            }
        } else {
            return false;
        }
    }

    public static function getPartName($a, $part)
    {
        $part_a = self::getPartArray($a, $part);
        if ($part_a) {
            if (is_array($part_a[0])) {
                return -1;
            } else {
                $name = "";
                if (is_array($part_a[2])) {
                    //first look in content type
                    $name = "";
                    while (list($key, $val) = each($part_a[2])) {
                        if ((strcasecmp($val, "NAME") == 0) || (strcasecmp($val, "FILENAME") == 0)) {
                            $name = $part_a[2][$key + 1];
                        }
                    }
                }
                if (empty($name)) {
                    //check in content disposition
                    //$id = count($part_a) - 2;
                    $id = 8;
                    if ((is_array($part_a[$id])) && (is_array($part_a[$id][1]))) {
                        $array = $part_a[$id][1];
                        while (list($key, $val) = each($array)) {
                            if (is_string($val) && ((strcasecmp($val, "NAME") == 0) || (strcasecmp($val, "FILENAME") == 0))) {
                                $name = $array[$key + 1];
                            }
                        }
                    }
                }
                return $name;
            }
        } else {
            return "";
        }
    }

}
