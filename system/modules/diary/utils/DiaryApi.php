<?php

namespace application\modules\diary\utils;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\model\Diary;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\message\utils\MessageApi;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class DiaryApi extends MessageApi
{

    private $_indexTab = array('diaryPersonal', 'diaryAppraise');

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        $subUidArr = UserUtil::getAllSubs(Ibos::app()->user->uid, '', true);
        if (count($subUidArr) > 0) {
            return array(
                'name' => 'diary/diary',
                'title' => '工作日志',
                'style' => 'in-diary',
                'tab' => array(
                    array(
                        'name' => 'diaryPersonal',
                        'title' => '个人',
                        'icon' => 'o-da-personal'
                    ),
                    array(
                        'name' => 'diaryAppraise',
                        'title' => '评阅',
                        'icon' => 'o-da-appraise'
                    )
                )
            );
        } else {
            return array(
                'name' => 'diary/diary',
                'title' => '工作日志',
                'style' => 'in-diary',
                'tab' => array(
                    array(
                        'name' => 'diaryPersonal',
                        'title' => '个人',
                        'icon' => 'o-da-personal'
                    )
                )
            );
        }
    }

    /**
     * 渲染首页视图
     * @return type
     */
    public function renderIndex()
    {
        $return = array();
        $viewAlias = 'application.modules.diary.views.indexapi.diary';
        $today = date('Y-m-d');
        $uid = Ibos::app()->user->uid;
        $data = array(
            'diary' => Diary::model()->fetch('diarytime = :diarytime AND uid = :uid', array(':diarytime' => strtotime($today), ':uid' => $uid)),
            'calendar' => $this->loadCalendar(),
            'dateWeekDay' => DiaryUtil::getDateAndWeekDay($today),
            'lang' => Ibos::getLangSource('diary.default'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('diary')
        );
        // 判断是否已被评阅，拿取图章
        if (!empty($data['diary']) && $data['diary']['stamp'] != 0) {
            $data['stampUrl'] = Stamp::model()->fetchStampById($data['diary']['stamp']);
        }
        // 获取前一篇日志
        $data['preDiary'] = Diary::model()->fetchPreDiary(strtotime($today), $uid);
        if (!empty($data['preDiary']) && $data['preDiary']['stamp'] != 0) {
            $iconUrl = Stamp::model()->fetchIconById($data['preDiary']['stamp']);
            $data['preStampIcon'] = $iconUrl;
        }

        //已评阅数
        $subUidArr = User::model()->fetchSubUidByUid($uid);
        $data['subUids'] = implode(',', $subUidArr);
        //是否存在下属
        if (!empty($subUidArr)) {
            $uids = implode(',', $subUidArr);
            // 判断昨天的是否已评阅完，没评阅完就显示昨天的，评阅完就显示今天的
            $yesterday = strtotime(date('Y-m-d', strtotime("-1 day")));
            $yestUnReviewCount = Diary::model()->count("uid IN($uids)" . " AND diarytime=$yesterday" . " AND isreview='0'");
            if ($yestUnReviewCount > 0) {
                $time = $yesterday;
            } else {
                $time = strtotime(date('Y-m-d'));
            }
            $data['reviewInfo'] = array(
                'reviewedCount' => Diary::model()->count("uid IN($uids)" . " AND diarytime=$time" . " AND isreview='1'"),
                'count' => Diary::model()->count("uid IN($uids)" . " AND diarytime=$time")
            );
            $paginationData = Diary::model()->fetchAllByPage("uid IN($uids)" . " AND diarytime=$time");
            //得到该天没有工作日志的uid --取得该天有记录的uid，总下属uid-有记录的uid
            $recordUidArr = $noRecordUidArr = $noRecordUserList = array();
            foreach ($paginationData['data'] as $diary) {
                $recordUidArr[] = $diary['uid'];
            }
            if (count($recordUidArr) > 0) {
                foreach ($subUidArr as $subUid) {
                    if (!in_array($subUid, $recordUidArr)) {
                        $noRecordUidArr[] = $subUid;
                    }
                }
            } else {
                $noRecordUidArr = $subUidArr;
            }
            if (count($noRecordUidArr) > 0) {
                $newUidArr = array_slice($noRecordUidArr, 0, 3);
                $noRecordUserList = User::model()->fetchAllByUids($newUidArr);
            }
            $data['noRecordUserList'] = $noRecordUserList;

            //取得所有已经评阅的日志
            $reviewData = array();
            $noReviewData = array();
            foreach ($paginationData['data'] as $record) {
                $record['user'] = User::model()->fetchByUid($record['uid']);
                $record['diarytime'] = Convert::formatDate($record['diarytime'], 'd');
                if ($record['isreview'] == '1') {
                    $reviewData[] = $record;
                } else {
                    $noReviewData[] = $record;
                }
            }
            $data['reviewRecordList'] = $reviewData;
            $data['noReviewRecordList'] = $noReviewData;
        }

        foreach ($this->_indexTab as $tab) {
            $data['tab'] = $tab;
            if ($tab == 'diaryPersonal') {
                $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
            } else if ($tab == 'diaryAppraise' && count($subUidArr) > 0) {
                $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
            }
        }
        return $return;
    }

    /**
     * 获取最新日志
     * @return integer
     */
    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        //获取所有直属下属id
        $uidArr = User::model()->fetchSubUidByUid($uid);
        if (!empty($uidArr)) {
            $uidStr = implode(',', $uidArr);
            $sql = "SELECT COUNT(diaryid) AS number FROM {{diary}} WHERE FIND_IN_SET( `uid`, '{$uidStr}' ) AND isreview = 0";
            $record = Diary::model()->getDbConnection()->createCommand($sql)->queryAll();
            return intval($record[0]['number']);
        } else {
            return 0;
        }
    }

    private function loadCalendar()
    {
        //取出某个月的所有日志记录，得到每篇日志的有日志，已点评状态
        list($year, $month, $day) = explode('-', date('Y-m-d'));
        $diaryList = Diary::model()->fetchAllByUidAndDiarytime($year . $month, Ibos::app()->user->uid);
        return DiaryUtil::getCalendar($year . $month, $diaryList, $day);
    }

}
