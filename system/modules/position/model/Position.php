<?php

/**
 * 岗位表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位表的数据层操作
 * 
 * @package application.modules.position.model
 * @version $Id: Position.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\String;
use application\modules\position\utils\Position as PositionUtil;

class Position extends Model {

    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{position}}';
    }

    /**
     * 新增或保存单条记录后更新缓存
     * @return void 
     */
    public function afterSave() {
        CacheUtil::update( 'position' );
        CacheUtil::load( 'position' );
        parent::afterSave();
    }

    /**
     * 删除后单条记录后更新缓存
     * @return void 
     */
    public function afterDelete() {
        CacheUtil::update( 'position' );
        CacheUtil::load( 'position' );
        parent::afterDelete();
    }

    /**
     * 根据catId(如果有)查找列表页数据
     * @param integer $catId 岗位分类ID
     * @param integer $limit 每页数据
     * @param integer $offset 偏移量
     * @return array
     */
    public function fetchAllByCatId( $catId, $limit, $offset ) {
        $criteria = array(
            'order' => 'sort DESC',
            'limit' => $limit,
            'offset' => $offset
        );
        if ( $catId ) {
            $criteria['condition'] = "`catid` = {$catId}";
        }
        return $this->fetchAll( $criteria );
    }

    /**
     * 根据岗位ID查找岗位名称，返回$glue分隔的岗位名称字符串
     * @param mixed $ids 岗位ID数组或逗号分隔字符串
     * @param string $glue 分隔符
     * @param boolean $returnFirst 是否返回第一个
     * @return string
     */
    public function fetchPosNameByPosId( $id, $glue = ',', $returnFirst = false ) {
        $posArr = PositionUtil::loadPosition();
        $posIds = is_array( $id ) ? $id : explode( ',', String::filterStr( $id ) );
        $name = array();
        if ( $returnFirst ) {
            if ( isset( $posArr[$posIds[0]] ) ) {
                $name[] = $posArr[$posIds[0]]['posname'];
            }
        } else {
            foreach ( $posIds as $posId ) {
                $name[] = isset( $posArr[$posId] ) ? $posArr[$posId]['posname'] : null;
            }
        }
        return implode( $glue, $name );
    }

    /**
     * 根据用户 uid 获取对应的职位名称
     * @param  integer $uid 用户 uid
     * @return string      职位名，不存在返回空字符串
     */
    public function fetchPosNameByUid( $uid ) {
        $posid = PositionRelated::model()->fetchAllPositionIdByUid( $uid );
        $position = $this->fetchByPk( $posid );
        if ( empty( $position ) ) {
            return '';
        }
        return $position['posname'];
    }
}
