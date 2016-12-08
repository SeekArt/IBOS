<?php

/**
 * IWStatDiarySummary class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 日志 - 摘要挂件
 * @package application.modules.diary.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\widgets;

use application\core\utils\Ibos;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryStats;
use application\modules\statistics\utils\StatCommon;

class StatDiarySummary extends StatDiaryBase
{

    // 个人视图
    const PERSONAL = 'application.modules.diary.views.widget.psummary';
    // 评阅视图
    const REVIEW = 'application.modules.diary.views.widget.rsummary';

    /**
     * 显示视图
     * @return void
     */
    public function run()
    {
        $time = StatCommon::getCommonTimeScope();
        if ($this->inPersonal()) {
            $this->renderPersonal($time);
        } else {
            $this->checkReviewAccess();
            $this->renderReview($time);
        }
    }

    /**
     * 渲染个人视图
     * @param array $time 时间范围
     */
    protected function renderPersonal($time)
    {
        $uid = Ibos::app()->user->uid;
        $data = array(
            'total' => Diary::model()->countDiaryTotalByUid($uid, $time['start'], $time['end']),
            'beingreviews' => Diary::model()->countReviewTotalByUid($uid, $time['start'], $time['end']),
            'ontimerate' => Diary::model()->countOnTimeRateByUid($uid, $time['start'], $time['end']),
            'score' => DiaryStats::model()->countScoreByUid($uid, $time['start'], $time['end'])
        );
        $this->render(self::PERSONAL, $data);
    }

    /**
     * 渲染评阅视图
     * @param array $time 时间范围
     */
    protected function renderReview($time)
    {
        $uid = $this->getUid();
        $data = array(
            'total' => Diary::model()->countDiaryTotalByUid($uid, $time['start'], $time['end']),
            'unreviews' => Diary::model()->countUnReviewByUids($uid, $time['start'], $time['end']),
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

}
