<?php

/**
 * IWStatReportCount class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 统计图表widget
 * @package application.modules.report.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\widgets;

use application\core\utils\Ibos;
use application\modules\statistics\core\ChartFactory;
use application\modules\statistics\utils\StatCommon;

class StatReportCount extends StatReportBase
{

    // widget视图
    const VIEW = 'application.modules.report.views.widget.count';

    public function init()
    {
        $this->checkReviewAccess();
    }

    /**
     * 渲染图表视图
     * @return void
     */
    public function run()
    {
        $factory = new ChartFactory();
        $properties = array('uid' => $this->getUid(), 'typeid' => $this->getTypeid(), 'timeScope' => StatCommon::getCommonTimeScope());
        $scoreCounter = $this->createComponent('application\modules\report\components\ReportScoreTimeCounter', $properties);
        $stampCounter = $this->createComponent('application\modules\report\components\ReportStampCounter', $properties);
        $data = array(
            'statAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('statistics'),
            'score' => $factory->createChart($scoreCounter, 'application\modules\report\components\ReportLineChart'),
            'stamp' => $factory->createChart($stampCounter, 'application\modules\report\components\ReportBarChart')
        );
        $this->render(self::VIEW, $data);
    }

}
