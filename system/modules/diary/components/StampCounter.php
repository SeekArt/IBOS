<?php

namespace application\modules\diary\components;

use application\core\utils\Convert;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\user\model\User;

class StampCounter extends TimeCounter
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
            $enableStamp = DiaryUtil::getEnableStamp();
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
            foreach ($this->getUid() as $uid) {
                $user = User::model()->fetchByUid($uid);
                $list = DiaryStats::model()->fetchAllStampByUid($uid, $time['start'], $time['end']);
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
