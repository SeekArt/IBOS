<?php

/**
 * 日程安排模快------CalendarSetup表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模快------CalendarSetup表操作类，继承ICModel
 * @package application.modules.calendar.model
 * @version $Id: CalendarSetup.php 1425 2013-10-29 16:16:43Z gzhzh $
 * @author gzhzh <gzhzh.com.cn>
 */

namespace application\modules\calendar\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

class CalendarSetup extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{calendar_setup}}';
    }

    /**
     * 通过uid查找用户的日程设置
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchSetupByUid($uid)
    {
        $setupData = $this->fetch(array(
            'condition' => 'uid=:uid',
            'params' => array(':uid' => $uid)
        ));
        return $setupData;
    }

    /**
     * 根据用户uid获取日程设置的开始和结束时间
     * @param integer $uid 用户id
     * @return array
     */
    public function getWorkTimeByUid($uid)
    {
        $setupData = $this->fetchSetupByUid($uid);
        $workTime = explode(',', Ibos::app()->setting->get("setting/calendarworkingtime"));
        if (empty($setupData)) { // 没有设置的用户读取后台的设置
            $data['startTime'] = isset($workTime[0]) ? $workTime[0] : '8';
            $data['endTime'] = isset($workTime[1]) ? $workTime[1] : '18';
        } else {
            $data['startTime'] = $setupData['mintime'];
            $data['endTime'] = $setupData['maxtime'];
        }
        return $data;
    }

    /**
     * 根据用户uid获取日程设置隐藏的日期
     * @param integer $uid 用户id
     * @return array
     */
    public function getHiddenDaysByUid($uid)
    {
        $hiddenDays = array();
        $setupData = $this->fetchSetupByUid($uid);
        if (!empty($setupData) && !empty($setupData['hiddendays'])) {
            $hiddenDays = StringUtil::utf8Unserialize($setupData['hiddendays']);
        }
        return $hiddenDays;
    }

    /**
     * 根据用户 uid 获取日程设置中的分享人员
     * 分享人员包括：日程分享查看权限人员(readsharing)、日程分享编辑权限人员(editsharing)
     * @param integer $uid
     * @return array
     */
    public function getSharingPersonnelByUid($uid)
    {
        $sharingPersonnel = array(
            'viewSharing' => '',
            'editSharing' => '',
        );
        $setupData = $this->fetchSetupByUid($uid);
        if (!empty($setupData) && !empty($setupData['viewsharing'])) {
            $sharingPersonnel['viewSharing'] = StringUtil::wrapId($setupData['viewsharing']);
        }
        if (!empty($setupData) && !empty($setupData['editsharing'])) {
            $sharingPersonnel['editSharing'] = StringUtil::wrapId($setupData['editsharing']);
        }
        return $sharingPersonnel;
    }

    /**
     * 修改某个用户的日程设置
     * @param type $uid 用户uid
     * @param type $minTime
     * @param type $maxTime
     * @param type $hiddenDays
     * @param string $viewSharing 日程阅读权限用户 uid 字符串
     * @param string $editSharing 日程编辑权限用户 uid 字符串
     * @param return void
     */
    public function updataSetup($uid, $minTime, $maxTime, $hiddenDays, $viewSharing, $editSharing)
    {
        $hiddenDays = empty($hiddenDays) ? '' : serialize($hiddenDays);
        // 将形如 u_1,u_2... 的字符串转换成 1,2... 的字符串形式
        $viewSharing = empty($viewSharing) ? '' : implode(',', StringUtil::getUidAByUDPX($viewSharing));
        $editSharing = empty($editSharing) ? '' : implode(',', StringUtil::getUidAByUDPX($editSharing));
        $newSetup = array(
            'mintime' => $minTime,
            'maxtime' => $maxTime,
            'hiddendays' => $hiddenDays,
            'viewsharing' => $viewSharing,
            'editsharing' => $editSharing,
        );
        $setupData = $this->fetchSetupByUid($uid);
        if (empty($setupData)) { // 没有就插入新设置
            $newSetup['uid'] = $uid;
            $this->add($newSetup);
        } else { // 否则就修改
            $this->updateAll($newSetup, "uid=:uid", array(':uid' => $uid));
        }
    }

    /**
     * 根据用户 uid 获取对应分享日程给我的 uid 数组
     * @param integer $uid 用户 uid
     * @return array 分享给我的用户的 uid 数组
     */
    public function getShareUidsByUid($uid)
    {
        $condition = 'FIND_IN_SET(:uid, `viewsharing`) OR FIND_IN_SET(:uid, `editsharing`)';
        $params = array(':uid' => $uid);
        $uids = $this->fetchAll($condition, $params);
        $uidArr = array_map(function ($user) {
            return $user['uid'];
        }, $uids);
        return !empty($uidArr) ? $uidArr : array();
    }

}
