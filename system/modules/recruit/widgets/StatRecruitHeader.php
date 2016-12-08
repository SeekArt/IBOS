<?php

/**
 * IWStatRecruitHeader class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 总结 - 统计视图头部挂件
 * @package application.modules.recruit.widgets
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\widgets;

use application\core\utils\Ibos;
use application\modules\statistics\utils\StatCommon;

class StatRecruitHeader extends StatRecruitBase
{

    // 视图位置
    const VIEW = 'application.modules.recruit.views.widget.header';

    /**
     * 渲染统计头部视图
     * @return void
     */
    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $timeRoute = $this->getTimeRoute($module);
        $type = $this->getType();
        $timestr = $this->getTimestr();
        if (empty($type)) {
            $type = 'day';
        }
        if (empty($timestr)) {
            $timestr = 'thisweek';
        }
        $data = array(
            'module' => $module,
            'timeRoute' => $timeRoute,
            'lang' => Ibos::getLangSources(array('recruit.default')),
            'time' => StatCommon::getCommonTimeScope(),
            'type' => $type,
            'timestr' => $timestr
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
        if ($module == 'recruit') {
            $timeRoute = 'recruit/stats/index';
        } else {
            $timeRoute = 'statistics/module/index';
        }
        return $timeRoute;
    }

}
