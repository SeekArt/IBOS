<?php

/**
 * IWStatReportSummary class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 摘要挂件
 * @package application.modules.report.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\widgets;

use application\core\utils\Ibos;
use application\modules\report\model\Report;
use application\modules\report\model\ReportStats;
use application\modules\statistics\utils\StatCommon;

class StatReportSummary extends StatReportBase
{

    // 个人视图
    const PERSONAL = 'application.modules.report.views.widget.psummary';
    // 评阅视图
    const REVIEW = 'application.modules.report.views.widget.rsummary';

    /**
     * 显示视图
     * @return void
     */
    public function run()
    {
        $time = StatCommon::getCommonTimeScope();
        $typeid = $this->getTypeid();
        if ($this->inPersonal()) {
            $this->renderPersonal($time, $typeid);
        } else {
            $this->checkReviewAccess();
            $this->renderReview($time, $typeid);
        }
    }

    /**
     * 渲染个人视图
     * @param integer $typeid 总结类型id
     * @param array $time 时间范围
     */
    protected function renderPersonal($time, $typeid)
    {
        $uid = Ibos::app()->user->uid;
        $data = array(
            'title' => $this->handleTitleByTypeid($typeid),
            'total' => Report::model()->countReportTotalByUid($uid, $time['start'], $time['end'], $typeid),
            'beingreviews' => Report::model()->countReviewTotalByUid($uid, $time['start'], $time['end'], $typeid),
            'score' => ReportStats::model()->countScoreByUid($uid, $time['start'], $time['end'], $typeid)
        );
        $this->render(self::PERSONAL, $data);
    }

    /**
     * 渲染评阅视图
     * @param integer $typeid 总结类型id
     * @param array $time 时间范围
     */
    protected function renderReview($time, $typeid)
    {
        $uid = $this->getUid();
        $data = array(
            'title' => $this->handleTitleByTypeid($typeid),
            'total' => Report::model()->countReportTotalByUid($uid, $time['start'], $time['end'], $typeid),
            'unreviews' => Report::model()->countUnReviewByUids($uid, $time['start'], $time['end'], $typeid),
        );
        $data['reviewrate'] = $this->calcReviewRate($data['unreviews'], $data['total']);
        $this->render(self::REVIEW, $data);
    }

    /**
     * 计算评阅率
     * @param integer $unreview 未评阅的
     * @param integer $total 总数
     * @return integer
     */
    private function calcReviewRate($unreview, $total)
    {
        if ($unreview == 0 && $total) {
            return 100;
        } elseif ($unreview && $total) {
            return round((1 - ($unreview / $total)) * 100);
        } else {
            return 0;
        }
    }

    /**
     * 处理输出标题
     * @param integer $typeid 总结类型id
     * @return string
     */
    protected function handleTitleByTypeid($typeid)
    {
        $title = array(
            1 => '周报',
            2 => '月报',
            3 => '季报',
            4 => '年报'
        );
        if (in_array($typeid, array_keys($title))) {
            return $title[$typeid];
        } else {
            return $title[1];
        }
    }

}
