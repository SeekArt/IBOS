<?php

namespace application\modules\recruit\core;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\recruit\model\ResumeDetail as RDModel;
use application\modules\user\model\User;

class RecruitInterview
{

    /**
     * 处理list页面显示数据
     * @param array $interviewList
     * @return array
     */
    public static function processListData($interviewList)
    {
        foreach ($interviewList as $k => $interview) {
            $interviewList[$k]['interviewtime'] = date('Y-m-d', $interview['interviewtime']);
            $interviewList[$k]['interviewer'] = User::model()->fetchRealnameByUid($interview['interviewer']);
            $interviewList[$k]['process'] = StringUtil::cutStr($interview['process'], 12);
            $interviewList[$k]['realname'] = RDModel::model()->fetchRealnameByResumeId($interview['resumeid']);
        }
        return $interviewList;
    }

    /**
     *  处理面试记录添加或编辑的数据
     * @param array $data 提交过来要添加或编辑的面试记录数组
     * @return array  返回处理过后的面试记录数组
     */
    public static function processAddOrEditData($data)
    {
        $inverviewArr = array(
            'interviewtime' => 0,
            'interviewer' => 0,
            'method' => '',
            'type' => '',
            'process' => ''
        );
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($inverviewArr))) {
                $inverviewArr[$k] = $k === 'process' ? \CHtml::encode($v) : $v;
            }
        }
        $interviewer = implode(',', StringUtil::getId($inverviewArr['interviewer']));
        $inverviewArr['interviewer'] = empty($interviewer) ? Ibos::app()->user->uid : $interviewer;
        if ($inverviewArr['interviewtime'] != 0) {
            $inverviewArr['interviewtime'] = strtotime($inverviewArr['interviewtime']);
        } else {
            $inverviewArr['interviewtime'] = TIMESTAMP;
        }
        return $inverviewArr;
    }

}
