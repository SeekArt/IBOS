<?php

namespace application\modules\email\utils;

class RyosImap
{

    public static function encodeHTML($str)
    {
        $result = $str;
        $result = str_replace("&", "&amp;", $result);
        $result = str_replace("<", "&lt;", $result);
        $result = str_replace(">", "&gt;", $result);
        return $result;
    }

    public static function encodeUTFSafeHTML($str)
    {
        $result = $str;
        $result = str_replace("\"", "&quot;", $result);
        $result = str_replace("<", "&lt;", $result);
        $result = str_replace(">", "&gt;", $result);

        return $result;
    }

    public static function sanitizeHTML(&$html)
    {
        /*
          Strip tags and scriptable attributes from HTML
          that might be used in a XSS attack.
          Return true if modified, false otherwise.
         */
        $orig_len = strlen($html);
        $tags = array("script" => 't', 'javascript' => 't',
            "object" => 't', "iframe" => 't', "applet" => 't', "meta" => 't', "form" => 't',
            "onMouseOver" => 'a', 'onMove' => 'a', 'onMouseOut' => 'a', 'onFocus' => 'a', 'onUnload' => 'a',
            'onLoad' => 'a', 'onClick' => 'a', 'onSelect' => 'a', 'onBlur' => 'a', 'onClick' => 'a',
            'onError' => 'a', 'style' => 'a');

        $patterns = array();
        $replace = array();
        foreach ($tags as $tag => $type) {
            if ($type == 'a') {
                $patterns[] = '/(<[^>]*)[\s]+(' . $tag . '[=\s]+)([^>]*>)/i';
                $replace[] = '\1 no_\2\3';
            } else if ($type == 't') {
                $patterns[] = '/(<[\s\/]*)(' . $tag . ')([^>]*>)/i';
                $replace[] = '\1no_\2\3';
            }
        }

        $html = preg_replace($patterns, $replace, $html);

        return (strlen($html) != $orig_len);
    }

}
