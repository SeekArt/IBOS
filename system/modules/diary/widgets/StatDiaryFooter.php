<?php

/**
 * IWStatDiarySummary class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 日志 - 评阅底部挂件
 * @package application.modules.diary.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\widgets;

use application\modules\diary\model\Diary;
use application\modules\statistics\utils\StatCommon;
use application\modules\user\model\User;

class StatDiaryFooter extends StatDiaryBase
{

    const VIEW = 'application.modules.diary.views.widget.footer';

    /**
     *
     * @return type
     */
    public function run()
    {
        $this->checkReviewAccess();
        $uid = $this->getUid();
        $time = StatCommon::getCommonTimeScope();
        $list = Diary::model()->fetchAddTimeByUid($uid, $time['start'], $time['end']);
        $data = array(
            'delay' => $this->getDelay($list),
            'nums' => $this->getDiaryNums($list)
        );
        $this->render(self::VIEW, $data);
    }

    /**
     * 统计迟交日志的人员
     * @param array $uid
     * @param array $time
     * @return array
     */
    protected function getDelay($list)
    {
        $res = array();
        foreach ($list as $rec) {
            if ($rec['addtime'] - $rec['diarytime'] > 86400) {
                !isset($res[$rec['uid']]) && $res[$rec['uid']] = array('user' => User::model()->fetchByUid($rec['uid']), 'count' => 0);
                $res[$rec['uid']]['count']++;
            }
        }
        return $res;
    }

    /**
     * 统计写了多少篇
     * @param array $uid
     */
    protected function getDiaryNums($list)
    {
        $res = array();
        foreach ($list as $rec) {
            !isset($res[$rec['uid']]) && $res[$rec['uid']] = array('user' => User::model()->fetchByUid($rec['uid']), 'count' => 0);
            $res[$rec['uid']]['count']++;
        }
        return $res;
    }

}
