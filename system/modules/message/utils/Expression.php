<?php

/**
 * ExpressionUtil class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 表情工具类
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.utils
 * $Id$
 */

namespace application\modules\message\utils;

use application\core\utils\Cache;
use application\extensions\Dir;

class Expression
{

    /**
     * 获取当前所有的表情
     * @param boolean $flush 是否更新缓存，默认为false
     * @return array 返回表情数据
     */
    public static function getAllExpression($flush = false)
    {
        $cacheId = 'expression';
        if (($res = Cache::get($cacheId)) === false || $flush === true) {
            $filepath = 'static/image/expression/';
            $expression = new Dir($filepath);
            $expression_pkg = $expression->toArray();
            $res = array();
            // TODO:：临时，后期是否可改为数据表格式	@banyan
            $typeMap = array(
                'df' => '默认',
                'bm' => '暴漫',
                'other' => '其他'
            );
            foreach ($expression_pkg as $index => $value) {
                list ($file) = explode(".", $value['filename']);
                list($type) = explode('_', $file);
                $temp['value'] = $file;
                $temp['phrase'] = '[' . $file . ']';
                $temp['icon'] = $value['filename'];
                $temp['type'] = $type;
                $temp['category'] = isset($typeMap[$type]) ? $typeMap[$type] : $typeMap['other'];
                $res[$temp['phrase']] = $temp;
            }
            Cache::set($cacheId, $res);
        }
        return $res;
    }

    /**
     * 将表情格式化成HTML形式
     * @param string $data 内容数据
     * @return string 转换为表情链接的内容
     */
    public static function parse($data)
    {
        $data = preg_replace("/img{data=([^}]*)}/", "<img src='$1'  data='$1' >", $data);
        return $data;
    }

}
