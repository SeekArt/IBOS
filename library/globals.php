<?php

/**
 * (PHP 5 >= 5.5.0, PHP 7)
 * Return the values from a single column in the input array
 */
if (!function_exists("array_column")) {
    function array_column($array, $column_name) {
        return array_map(function ($element) use ($column_name) {
            return $element[$column_name];
        }, $array);

    }
}

/**
 * 检查字符串是否以xxx开头
 * Example：
 * startsWith("abcdef", "ab") -> true
 * startsWith("abcdef", "cd") -> false
 */
if (!function_exists("startsWith")) {
    function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}

/**
 * 检查字符串是否以xxx结尾
 * Example:
 * endsWith("abcdef", "") -> true
 * endsWith("", "abcdef") -> false
 */
if (!function_exists("endsWith")) {
    function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}

/**
 * Filtering a array by its keys using a callback.
 *
 * @param $array array The array to filter
 * @param $callback Callback The filter callback, that will get the key as first argument.
 *
 * @return array The remaining key => value combinations from $array.
 */
if (!function_exists("array_filter_key")) {
    function array_filter_key(array $array, $callback) {
        $matchedKeys = array_filter(array_keys($array), $callback);
        return array_intersect_key($array, array_flip($matchedKeys));
    }
}

if (!function_exists('gzdecode')) {
    function gzdecode($data)
    {
        return gzinflate(substr($data,10,-8));
    }
}