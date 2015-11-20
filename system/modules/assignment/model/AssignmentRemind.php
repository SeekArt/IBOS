<?php

/**
 * 任务指派模块------ assignment_remind表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------  assignment_remind表的数据层操作类，继承ICModel
 * @package application.modules.assignments.model
 * @version $Id: AssignmentRemind.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\model;

use application\core\model\Model;
use application\core\utils\Convert;

class AssignmentRemind extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{assignment_remind}}';
	}

	/**
	 * 获取某个uid所有提醒设置,返回格式:任务id=>提醒时间
	 * @param integer $uid 用户id
	 * @return array
	 */
	public function fetchAllByUid( $uid ) {
		$record = $this->fetchAll( "uid = {$uid}" );
		$res = array();
		foreach ( $record as $remind ) {
			$res[$remind['assignmentid']] = $remind['remindtime'];
		}
		return $res;
	}
	
	/**
	 * 获得某个用户的某个任务提醒的日程id
	 * @param integer $assignmentId 任务id
	 * @param integer $uid 用户id
	 * @return array
	 */
	public function fetchCalendarids( $assignmentId, $uid ){
		$records = $this->fetchAll( "assignmentid = {$assignmentId} AND uid = {$uid}" );
		return Convert::getSubByKey($records, 'calendarid');
	}

}
