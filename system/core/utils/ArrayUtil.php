<?php
/**
 * 数组工具类
 *
 * @namespace application\core\utils
 * @filename ArrayUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/19 11:59
 */

namespace application\core\utils;


class ArrayUtil
{

    /**
     * @param $array
     * @param $columnName
     * @return mixed
     */
    public static function getColumn($array, $columnName)
    {
        return array_map(function ($element) use ($columnName) {
            return $element[$columnName];
        }, $array);
    }
}
