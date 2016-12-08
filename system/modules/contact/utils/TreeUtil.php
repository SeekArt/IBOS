<?php
/**
 * @namespace application\modules\contact\utils
 * @filename TreeUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/11 16:39
 */

namespace application\modules\contact\utils;

use application\core\utils\System;
use application\modules\contact\extensions\Tree\lib\BlueM\Tree;

/**
 * Class TreeUtil
 *
 * @package application\modules\contact\utils
 */
class TreeUtil extends System
{
    /**
     * @param string $className
     * @return TreeUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }
    
    /**
     * @param $data
     * @param array $options
     * @return Tree
     */
    public function create($data, $options = array())
    {
        $tree = new Tree($data, $options);
        
        return $tree;
    }
}
