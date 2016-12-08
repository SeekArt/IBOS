<?php

namespace application\modules\report\components;

use application\core\utils\Convert;
use application\modules\dashboard\model\Stamp;
use application\modules\report\model\ReportStats;
use application\modules\report\utils\Report;
use application\modules\user\model\User;

class ReportStampCounter extends ReportTimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'stamp';
    }

    public function getStamp()
    {
        static $stamp = array();
        if (empty($stamp)) {
            $enableStamp = Report::getEnableStamp();
            $stampIds = implode(',', array_keys($enableStamp));
            $stamp = Stamp::model()->fetchAllSortByPk('id', array('condition' => "FIND_IN_SET(id,'{$stampIds}')", 'order' => 'sort ASC'));
        }
        return $stamp;
    }

    public function getStampName()
    {
        $stamp = $this->getStamp();
        return Convert::getSubByKey($stamp, 'code');
    }

    public function getCount()
    {
        static $return = array();
        if (empty($return)) {
            $return = array();
            $time = $this->getTimeScope();
            $typeid = $this->getTypeid();
            foreach ($this->getUid() as $uid) {
                $user = User::model()->fetchByUid($uid);
                $list = ReportStats::model()->fetchAllStampByUid($uid, $time['start'], $time['end'], $typeid);
                $list = $this->handleStamp($list);
                $return[$uid]['name'] = $user['realname'];
                $return[$uid]['list'] = $list;
            }
        }
        return $return;
    }

    protected function handleStamp($list)
    {
        $count = array_count_values($list);
        $list = array_fill_keys($list, 0);
        $ret = array();
        foreach ($this->getStamp() as $stampId => $stamp) {
            if (isset($list[$stampId])) {
                $ret[$stampId] = array('name' => $stamp['code'], 'count' => isset($count[$stampId]) ? $count[$stampId] : 0);
            } else {
                $ret[$stampId] = array('name' => $stamp['code'], 'count' => 0);
            }
        }
        return $ret;
    }

}
