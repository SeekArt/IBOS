<?php

/**
 * nav表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  nav表的数据层操作
 * 
 * @package application.modules.dashboard.model
 * @version $Id: Nav.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class Nav extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{nav}}';
    }

    /**
     * 查找所有的导航设置并以父子形式返回数组
     * @return array
     */
    public function fetchAllByAllPid() {
        $return = array();
        $roots = $this->fetchAll( '`pid` = 0 ORDER BY `sort` ASC' );
        foreach ( $roots as $root ) {
            $root['child'] = $this->fetchAll( "`pid` = {$root['id']} ORDER BY `sort` ASC" );
            $return[$root['id']] = $root;
        }
        return $return;
    }

    /**
     * 根据Id字符串删除非系统导航记录
     * @param string $ids
     * @return integer 删除的条数
     */
    public function deleteById( $ids ) {
        $id = explode( ',', trim( $ids, ',' ) );
        $affecteds = $this->deleteByPk( $id, "`system` = '0'" );
        $affecteds += $this->deleteAll( "FIND_IN_SET(pid,'" . implode( ',', $id ) . "')" );
        return $affecteds;
    }

}
