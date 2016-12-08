<?php

namespace application\modules\diary\components;

use application\core\utils\Convert;
use application\core\utils\StringUtil;
use application\modules\statistics\core\Chart as ICChart;
use application\modules\user\model\User;

class Chart extends ICChart
{

    /**
     * 获取是否在个人统计页
     * @return boolean
     */
    public function getIsPersonal()
    {
        $uids = $this->getCounter()->getUid();
        return count($uids) == 1;
    }

    /**
     * 获取用户ID对应的真实姓名，返回适合前端显示的字符串（增加引号）
     * @return string
     */
    public function getUserName()
    {
        $users = User::model()->fetchAllByUids($this->getCounter()->getUid());
        return StringUtil::iImplode(Convert::getSubByKey($users, 'realname'));
    }

    public function getStampName()
    {
        $name = StringUtil::iImplode($this->getCounter()->getStampName());
        return $name;
    }

    /**
     * 获取图表数据序列
     * @return type
     */
    public function getSeries()
    {
        ;
    }

    /**
     * 获取图表Y轴数据
     */
    public function getYaxis()
    {
        ;
    }

    /**
     * 获取图表X轴数据
     */
    public function getXaxis()
    {
        ;
    }

}
