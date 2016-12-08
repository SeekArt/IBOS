<?php

/**
 * IWReportSublist class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 侧栏挂件
 * @package application.modules.report.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\widgets;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\report\utils\Report;
use application\modules\user\utils\User;
use CWidget;

class ReportSublist extends CWidget
{

    // 视图
    const VIEW = 'application.modules.report.views.widget.sublist';

    // 是否在统计页面
    private $_instats;

    /**
     * 渲染侧栏挂件视图
     * @return void
     */
    public function run()
    {
        $data = array(
            'typeid' => Env::getRequest('typeid'),
            'lang' => Ibos::getLangSource('report.default'),
            'deptArr' => User::getManagerDeptSubUserByUid(Ibos::app()->user->uid),
            'dashboardConfig' => Report::getSetting(),
            'deptRoute' => $this->inStats() ? 'stats/review' : 'review/index',
            'userRoute' => $this->inStats() ? 'stats/review' : 'review/personal'
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
