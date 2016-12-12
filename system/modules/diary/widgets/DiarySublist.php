<?php

/**
 * IWDiarySublist class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 日志 - 侧栏挂件
 * @package application.modules.diary.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\widgets;

use application\core\utils\Ibos;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\user\utils\User as UserUtil;
use CWidget;

class DiarySublist extends CWidget
{

    // 视图
    const VIEW = 'application.modules.diary.views.widget.sublist';

    // 是否在统计页面
    private $_instats;
    private $_fromController = 'review';

    public function setFromController($fromController)
    {
        $this->_fromController = $fromController;
    }

    public function getFromController()
    {
        return $this->_fromController;
    }

    /**
     * 渲染侧栏挂件视图
     * @return void
     */
    public function run()
    {
        $data = array(
            'deptArr' => UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid),
            'dashboardConfig' => DiaryUtil::getSetting(),
            'deptRoute' => $this->inStats() ? 'stats/review' : 'review/index',
            'userRoute' => $this->inStats() ? 'stats/review' : 'review/personal',
            'fromController' => $this->getController()->getId()
        );
        $this->render(self::VIEW, $data);
    }

    /**
     * 判断是否在统计视图
     * @return boolean
     */
    public function inStats()
    {
        return $this->getStats() === true;
    }

    /**
     * 设置是否在统计视图变量
     * @param boolean $stats
     */
    public function setStats($stats)
    {
        $this->_instats = $stats;
    }

    /**
     * 获取是否在统计视图变量
     * @return boolean
     */
    public function getStats()
    {
        return (bool)$this->_instats;
    }

}
