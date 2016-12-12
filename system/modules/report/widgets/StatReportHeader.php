<?php

/**
 * IWStatReportHeader class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 统计视图头部挂件
 * @package application.modules.report.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\widgets;

use application\core\utils\Ibos;
use application\modules\statistics\utils\StatCommon;

class StatReportHeader extends StatReportBase
{

    // 视图位置
    const VIEW = 'application.modules.report.views.widget.header';

    /**
     * 渲染统计头部视图
     * @return void
     */
    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $timeRoute = $this->getTimeRoute($module);
        $data = array(
            'module' => $module,
            'timeRoute' => $timeRoute,
            'lang' => Ibos::getLangSources(array('report.default')),
            'time' => StatCommon::getCommonTimeScope()
        );
        $this->render(self::VIEW, $data);
    }

    /**
     * 获取时间点击路由,会根据所在模块的不同而变更
     * @param string $module 当前所在模块
     * @return string
     */
    protected function getTimeRoute($module)
    {
        if ($module == 'report') {
            $timeRoute = 'report/stats/' . $this->getType();
        } else {
            $timeRoute = 'statistics/module/index';
        }
        return $timeRoute;
    }

}
