<?php

/**
 * ApprovalStep表的数据层操作文件
 *
 * @author gzhyj <gzhyj@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  ApprovalStep表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id: ApprovalStep.php 575 2014-04-24 16:42:03Z gzhyj $
 * @author gzhyj <gzhyj@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\IBOS;
use application\core\utils\Module;

class ApprovalStep extends Model {

	public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{approval_step}}';
    }


    public function relations() {
    	return array(
    		'approval' => array( self::BELONGS_TO, 'Approval', 'aid' ),
    	);
    }

    /**
     * 获取所有审核人里面有某个用户的步骤数据
     * @param  integer $uid 用户 ID
     * @return array      	步骤数据
     */
    public function getAllApprovalidByUid( $uid ) {
    	$stepArr = $this->findAll( sprintf( "FIND_IN_SET( '%s', `uids` )", $uid ) );
    	foreach ( $stepArr as $step ) {
    		if ( !isset( $result[$step->step] ) ) {
    			$reuslt[$step->step] = explode( ',', $step->uids );
    		}
    		else {
    			$result[$step->step] = array_merge( $result[$step->step], explode( ',', $step->uids ) );
    		}
    	}
    	return isset( $result ) ? $result : array();
    }

    /**
     * 获取审批流程某一步的审批人员列表字符串
     * @param  integer $aid  审批流程 ID
     * @param  integer $step 步骤
     * @return string        审批人员字符串
     */
    public function getApprovalerStr( $aid, $step ) {
        $approvalStep = $this->fetch( sprintf( "`aid` = %d AND `step` = %d", $aid, $step ) );
        return !empty( $approvalStep ) ? $approvalStep['uids'] : '';
    }
}