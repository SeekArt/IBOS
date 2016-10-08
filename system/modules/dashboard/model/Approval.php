<?php

/**
 * Approval表的数据层操作文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  Approval表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id: Approval.php 575 2014-04-24 16:42:03Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\article\model\ArticleCategory;
use application\modules\officialdoc\model\OfficialdocCategory;
use application\modules\meeting\model\MeetingRoom;
use application\modules\car\model\Car;
use application\modules\assets\model\AssetsAudit;


class Approval extends Model {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{approval}}';
    }

    /**
     * 审批流程步骤审核人关联关系
     */
    public function relations() {
        return array(
            'step'  => array( self::HAS_MANY, 'application\modules\dashboard\model\ApprovalStep', '', 'on' => 't.id = step.aid' ),
        );
    }

    /**
     * 获得所有审批流程，按添加倒序
     */
    public function fetchAllApproval() {
        return $this->with( 'step' )->findAll( array( 'order' => 'addtime DESC' ) );
    }

    /**
     * 获取下一步骤审核人uid数组，若已经是最后一步，返回成功标识
     * @param integer $id 审批流程id
     * @param integer $step 步骤（1,2,3,4,5）
     * @return array
     */
    public function fetchNextApprovalUids($id, $step) {
        $result = array('step' => '', 'uids' => array());
        if (empty($id)) {
            return $result;
        }
        $approval = $this->with( 'step' )->findByPk( $id );
        $nextStep = $step + 1;
        if ( $nextStep > $approval['level'] ) {
            $result['step'] = 'publish';
        }
        else {
            foreach ( $approval->getRelated( 'step' ) as $step ) {
                if ( $step->step == $nextStep ) {
                    $result = array( 'step' => $nextStep, 'uids' => explode( ',', $step->uids ) );
                }
            }
        }
        return $result;
    }

    /**
     * 获得步骤对应字段名
     * @param integer $step 步骤（1,2,3,4,5）
     * @return string
     */
    // public function getLevelNameByStep($step) {
    //     $levels = array(
    //         '1' => 'level1',
    //         '2' => 'level2',
    //         '3' => 'level3',
    //         '4' => 'level4',
    //         '5' => 'level5'
    //     );
    //     if (in_array($step, array_keys($levels))) {
    //         return $levels[$step];
    //     } else {
    //         return $levels['1'];
    //     }
    // }

    /**
     * 根据主键ids获取所有步骤审核人uid数组
     * @param mix $ids 审批流程id数组或逗号隔开的字符串
     * @return array
     */
    public function fetchApprovalUidsByIds($ids) {
        $ids = is_array( $ids ) ? implode( ',', $ids ) : $ids;
        $uidStr = '';
        $approvals = $this->with( 'step' )->findAll( sprintf( "FIND_IN_SET(`t`.`id`, '%s')", $ids ) );
        foreach ( $approvals as $approval ) {
            foreach ( $approval->getRelated( 'step' ) as $step ) {
                if ( !empty( $step ) ) {
                    $uidStr .= $step->uids . ',';
                }
            }
        }
        $uidArrTemp = explode( ',', $uidStr );
        $uidArr = array_unique( $uidArrTemp );
        return array_values( array_filter( $uidArr ) );
    }

    /**
     * 删除审批流程，删除后更新指向该审批流程的所有分类
     * @param integer $id 审批流程id
     * @return boolean
     */
    public function deleteApproval($id) {
        if ( empty( $id ) ) {
            return FALSE;
        }
        else {
            // 启用事务进行数据更新，删除对应审批流程同时更新 新闻、公文、车辆、会议、资产 五个模块相关表的 aid 字段
            $flag = TRUE;
            $transaction = $this->dbConnection->beginTransaction();
            try {
                $this->deleteByPk( $id );
                $connection = $this->dbConnection;
                if ( Module::getIsEnabled( 'article' ) ) {
                    $connection->createCommand()->update( ArticleCategory::model()->tableName(), array( 'aid' => 0 ), "aid = " . $id );
                }
                if ( Module::getIsEnabled( 'officialdoc' ) ) {
                    $connection->createCommand()->update( OfficialdocCategory::model()->tableName(), array( 'aid' => 0 ), "aid = " . $id );
                }
                if ( Module::getIsEnabled( 'car' ) ) {
                    $connection->createCommand()->update( Car::model()->tableName(), array( 'aid' => 0 ), "aid = " . $id );
                }
                if ( Module::getIsEnabled( 'meeting' ) ) {
                    $connection->createCommand()->update( MeetingRoom::model()->tableName(), array( 'aid' => 0 ), "aid = " . $id );
                }
                if ( Module::getIsEnabled( 'assets' ) ) {
                    $connection->createCommand()->update( AssetsAudit::model()->tableName(), array( 'aid' => 0 ), "aid = " . $id );
                }
                $transaction->commit();
            }
            catch ( Exception $e ) {
                $transaction->rollBack();
                $flag = FALSE;
            }
            return $flag;
        }
    }

    /**
     * 根据审核ids获取每个审核流程的审核人uid数组，以审核id作为键值，该审核id下的所有审核者uid为键值返回
     * @param mixed $ids 审核流程ids
     * @return array
     */
    public function fetchAllUidsByIds($ids) {
        $result = array();
        $ids = is_array( $ids ) ? implode( ',', $ids ) : $ids;
        $approvals = $this->with( 'step' )->findAll( sprintf( "FIND_IN_SET(`t`.`id`, '%s')", $ids ) );
        foreach ( $approvals as $approval ) {
            foreach ( $approval->getRelated( 'step' ) as $step ) {
                if ( !empty( $step ) ) {
                    if ( !isset( $result[$approval['id']] ) ) {
                        $result[$approval['id']] = explode( ',', $step->uids );
                    }
                    else {
                        $result[$approval['id']] = array_unique( array_merge( $result[$approval['id']], explode( ',', $step->uids ) ) );
                    }
                }
            }
        }
        return $result;
    }

    // public function fetchApprovalidIndexByLevelNByUid($uid) {

    //     $rows = Ibos::app()->db->createCommand()
    //             ->select('level,level1,level2,level3,level4,level5,id')
    //             ->from($this->tableName())
    //             ->where(array(
    //                 'OR',
    //                 " FIND_IN_SET( '{$uid}', `level1` ) ",
    //                 " FIND_IN_SET( '{$uid}', `level2` ) ",
    //                 " FIND_IN_SET( '{$uid}', `level3` ) ",
    //                 " FIND_IN_SET( '{$uid}', `level4` ) ",
    //                 " FIND_IN_SET( '{$uid}', `level5` ) ",
    //             ))
    //             ->queryAll();
    //     $returnArray = array();
    //     foreach ($rows as $row) :
    //         for ($i = 1; $i <= $row['level']; $i++):
    //             if (in_array($uid, explode(',', $row['level' . $i]))):
    //                 $returnArray[$i][] = $row['id'];
    //             endif;
    //         endfor;
    //     endforeach;
    //     return $returnArray;
    // }

    /**
     * 根据审批流程id获取免审人员
     * @param string $id
     * @return string
     */
    public function getFreeUidById($id) {
        $uidString = Ibos::app()->db->createCommand()
                ->select('free')
                ->from($this->tableName())
                ->where(" `id` = '{$id}' ")
                ->queryScalar();
        return $uidString;
    }

}
