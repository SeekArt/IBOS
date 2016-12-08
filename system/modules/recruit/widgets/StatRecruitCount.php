<?php

/**
 * IWStatRecruitCount class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 总结 - 统计图表widget
 * @package application.modules.recruit.widgets
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\widgets;

use application\core\utils\Ibos;
use application\modules\statistics\core\ChartFactory;
use application\modules\statistics\utils\StatCommon;

class StatRecruitCount extends StatRecruitBase
{

    // widget视图
    const VIEW = 'application.modules.recruit.views.widget.count';

    /**
     * 渲染图表视图
     * @return void
     */
    public function run()
    {
        $factory = new ChartFactory();
        $properties = array('timeScope' => StatCommon::getCommonTimeScope(), 'type' => $this->getType(), 'timestr' => $this->getTimestr());
        $flowCounter = $this->createComponent('application\modules\recruit\components\TalentFlowCounter', $properties); // 人才流动统计器
        $sexRatioCounter = $this->createComponent('application\modules\recruit\components\SexCounter', $properties); // 性别比例统计器
        $ageCounter = $this->createComponent('application\modules\recruit\components\AgeCounter', $properties); // 年龄结构统计器
        $degreeCounter = $this->createComponent('application\modules\recruit\components\DegreeCounter', $properties); // 学历分布统计器
        $workYearsCounter = $this->createComponent('application\modules\recruit\components\WorkYearsCounter', $properties); // 工作年限统计器
        $data = array(
            'statAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('statistics'),
            'talentFlow' => $factory->createChart($flowCounter, 'application\modules\recruit\components\RecruitLineChart'),
            'sexRatio' => $factory->createChart($sexRatioCounter, 'application\modules\recruit\components\RecruitPieChart'),
            'age' => $factory->createChart($ageCounter, 'application\modules\recruit\components\RecruitPieChart'),
            'degree' => $factory->createChart($degreeCounter, 'application\modules\recruit\components\RecruitPieChart'),
            'workYears' => $factory->createChart($workYearsCounter, 'application\modules\recruit\components\RecruitPieChart'),
        );
        $this->render(self::VIEW, $data);
    }

}
