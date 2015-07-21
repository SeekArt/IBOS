<?php

/**
 * 岗位模块函数库类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位模块函数库类
 *
 * @package application.modules.position.utils
 * @version $Id: Position.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\utils;

use application\core\utils\Convert;
use application\core\utils\IBOS;

class Position {

    /**
     * 加载岗位缓存
     * @return array
     */
    public static function loadPosition() {
        return IBOS::app()->setting->get( 'cache/position' );
    }

    /**
     * 加载岗位分类缓存
     * @return array
     */
    public static function loadPositionCategory() {
        return IBOS::app()->setting->get( 'cache/positioncategory' );
    }

    /**
     * 按拼音排序岗位
     * @return array
     */
    public static function getUserByPy() {
        $group = array();
        $list = self::loadPosition();
        foreach ( $list as $k => $v ) {
            $py = Convert::getPY( $v['posname'] );
            if ( !empty( $py ) ) {
                $group[strtoupper( $py[0] )][] = $k;
            }
        }
        ksort( $group );
        $data = array( 'datas' => $list, 'group' => $group );
        return $data;
    }

}
