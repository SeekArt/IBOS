<?php

/**
 * 岗位关联表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位关联表的数据层操作
 *
 * @package application.modules.position.model
 * @version $Id: PositionRelated.php 6779 2016-04-07 10:04:59Z tanghang $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\IBOS;
use application\modules\user\model\User;

class PositionRelated extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{position_related}}';
    }

    public function countByPositionId( $positionId ) {
        return $this->count( '`positionid` = :positionid', array( ':positionid' => $positionId ) );
    }

    /**
     * 根据uid查找赋值岗位ID
     * @staticvar array $uids 用户数组缓存
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllPositionIdByUid( $uid ) {
        static $uids = array();
        if ( !isset( $uids[$uid] ) ) {
            $posids = $this->fetchAll( array( 'select' => 'positionid', 'condition' => '`uid` = :uid', 'params' => array( ':uid' => $uid ) ) );
            $uids[$uid] = Convert::getSubByKey( $posids, "positionid" );
        }
        return $uids[$uid];
    }

    public function findPositionidIndexByUidX( $uidX = NULL ) {
        $condition = 1;
        if ( NULL === $uidX ) {
            $condition = User::model()->uid_find_in_set( $uidX );
        }
        $related = IBOS::app()->db->createCommand()
                ->select( 'uid,positionid' )
                ->from( $this->tableName() )
                ->where( $condition )
                ->queryAll();
        $return = array();
        if ( !empty( $related ) ) {
            foreach ( $related as $row ) {
                $return[$row['uid']][] = $row['positionid'];
            }
        }
        return $return;
    }

}
