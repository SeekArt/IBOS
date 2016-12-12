<?php

/**
 * IWStatRecruitSummary class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 总结 - 摘要挂件
 * @package application.modules.recruit.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\recruit\widgets;

use application\modules\recruit\model\Resume;
use application\modules\statistics\utils\StatCommon;

class StatRecruitSummary extends StatRecruitBase
{

    // 招聘概况视图
    const VIEW = 'application.modules.recruit.views.widget.summary';

    /**
     * 显示视图
     * @return void
     */
    public function run()
    {
        $time = StatCommon::getCommonTimeScope();
        $this->renderOverview($time);
    }

    /**
     * 渲染概况视图
     * @param array $time 时间范围
     */
    protected function renderOverview($time)
    {
        $data = array(
            'new' => Resume::model()->countByStatus(array(1, 2, 3, 4, 5), $time['start'], $time['end']),
            'pending' => Resume::model()->countByStatus(4, $time['start'], $time['end']),
            'interview' => Resume::model()->countByStatus(1, $time['start'], $time['end']),
            'employ' => Resume::model()->countByStatus(array(2, 3), $time['start'], $time['end']),
            'eliminate' => Resume::model()->countByStatus(5, $time['start'], $time['end'])
        );
        $this->render(self::VIEW, $data);
    }

}
