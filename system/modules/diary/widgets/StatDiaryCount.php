<?php

/**
 * IWStatDiaryCount class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 日志 - 统计图表widget
 * @package application.modules.diary.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\widgets;

use application\core\utils\Ibos;
use application\modules\statistics\core\ChartFactory;
use application\modules\statistics\utils\StatCommon;

class StatDiaryCount extends StatDiaryBase
{

    // widget视图
    const VIEW = 'application.modules.diary.views.widget.count';

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
        $properties = array('uid' => $this->getUid(), 'timeScope' => StatCommon::getCommonTimeScope());
        $timeCounter = $this->createComponent('application\modules\diary\components\SubmitTimeCounter', $properties);
        $scoreCounter = $this->createComponent('application\modules\diary\components\ScoreTimeCounter', $properties);
        $stampCounter = $this->createComponent('application\modules\diary\components\StampCounter', $properties);
        $data = array(
            'statAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('statistics'),
            'time' => $factory->createChart($timeCounter, 'application\modules\diary\components\LineChart'),
            'score' => $factory->createChart($scoreCounter, 'application\modules\diary\components\LineChart'),
            'stamp' => $factory->createChart($stampCounter, 'application\modules\diary\components\BarChart')
        );
        $this->render(self::VIEW, $data);
    }

}
