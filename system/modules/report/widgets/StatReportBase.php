<?php

/**
 * IWStatReportBase class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - widget base
 * @package application.modules.report.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\widgets;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use CWidget;

class StatReportBase extends CWidget
{

    /**
     * 统计的类型
     * @var string
     */
    private $_type;

    /**
     * 总结的类型id（1周、2月、3季、4年）
     * @var type
     */
    private $_typeid = 1;

    /**
     * 设置统计类型
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * 返回统计类型
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 设置总结的类型id
     * @param string $type
     */
    public function setTypeid($typeid)
    {
        $this->_typeid = $typeid;
    }

    /**
     * 返回总结的类型id
     * @return string
     */
    public function getTypeid()
    {
        return $this->_typeid;
    }

    /**
     * 获取是否在个人统计
     * @return boolean
     */
    protected function inPersonal()
    {
        return $this->getType() === 'personal';
    }

    /**
     * 通用获取统计的用户ID方法，在个人统计界面返回自己的ID，在评阅界面返回传入的ID或
     * 下属ID
     * @return array
     */
    protected function getUid()
    {
        if ($this->inPersonal()) {
            $uid = array(Ibos::app()->user->uid);
        } else {
            $id = Env::getRequest('uid');
            $uids = StringUtil::filterCleanHtml(StringUtil::filterStr($id));
            if (empty($uids)) {
                $uid = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);
                if (empty($uid)) {
                    return array();
                }
            } else {
                $uid = explode(',', $uids);
            }
        }
        return $uid;
    }

    /**
     * 通过总结类型获取统计时间
     * @param integer $time 当前时间
     * @return array
     */
    public function getTimeScope($time = TIMESTAMP)
    {
        static $timeScope = array();
        if (empty($timeScope)) {
            $start = Env::getRequest('start');
            $end = Env::getRequest('end');
            if (!empty($start) && !empty($end)) { // 自定义时间范围
                $start = strtotime($start);
                $end = strtotime($end);
                if ($start && $end) {
                    $timeScope = array(
                        'start' => $start,
                        'end' => $end
                    );
                }
            }
            if (empty($timeScope)) {
                $typeid = $this->getTypeid();
                $currentY = date('Y', $time);
                switch ($typeid) {
                    // 周，取一个月4周
                    case '1':
                        $start = strtotime("first day of this month 00:00:00", $time);
                        $end = strtotime("last day of this month 23:59:59", $time);
                        break;
                    // 月、季，取一年12个月
                    case '2':
                    case '3':
                        $start = strtotime(($currentY) . '-01-01 00:00:00');
                        $end = strtotime(($currentY + 1) . '-01-01 00:00:00') - 1;
                        break;
                    // 年，取包括今年在内的前5年
                    case '4':
                        $start = strtotime(($currentY - 4) . '-01-01 00:00:00');
                        $end = strtotime(($currentY + 1) . '-01-01 00:00:00') - 1;
                        break;
                    default:
                        $start = $end = null;
                        break;
                }
            }
        }
        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * 检查评阅进入权限。如果在评阅界面通过getUid()方法确没有返回任何ID，是没有意义的
     * @return void
     */
    protected function checkReviewAccess()
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            $this->getController()->redirect('stats/personal');
        }
    }

    /**
     *
     * @param type $class
     * @param type $properties
     * @return type
     */
    protected function createComponent($class, $properties = array())
    {
        return Ibos::createComponent(array_merge(array('class' => $class), $properties));
    }

}
