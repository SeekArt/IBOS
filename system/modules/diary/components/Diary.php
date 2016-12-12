<?php

/**
 * 工作日志模块------Diary核心类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 工作日志模块------Diary类
 * @package application.modules.diary.components
 * @version $Id: Diary.php 2509 2014-02-21 12:51:20Z gzhzh $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\components;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Stamp;
use application\modules\department\model\Department;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Diary
{

    /**
     * 处理default/list页面要显示的数据
     * @param array $data
     * @return array
     */
    public static function processDefaultListData($data)
    {
        $dashboardConfig = Ibos::app()->setting->get('setting/diaryconfig');
        //是否有锁定多少天前的日志
        $lockday = $dashboardConfig['lockday'] ? intval($dashboardConfig['lockday']) : 0;
        $return = array();
        foreach ($data as $value) {
            $readeruid = $value['readeruid'];
            if (empty($readeruid)) {
                $value['readercount'] = 0;
            } else {
                $value['readercount'] = count(explode(',', trim($readeruid, ',')));
            }
            $todayTime = (int)strtotime(date('Y-m-d', time()));  //今天的开始时间，即00:00
            $diaryTime = (int)$value['diarytime'];
            $diffDay = ($todayTime - $diaryTime) / (24 * 60 * 60);  //相差多少天
            if ($lockday > 0 && $diffDay > $lockday) {
                $value['editIsLock'] = 1;
            } else {
                $value['editIsLock'] = 0;
            }
            //取得点评数量
            $value['content'] = StringUtil::cutStr(strip_tags($value['content']), 255);
            $value['diarytime'] = DiaryUtil::getDateAndWeekDay(date('Y-m-d', $value['diarytime']));
            $value['addtime'] = Convert::formatDate($value['addtime'], 'u');
            //图章
            if ($value['stamp'] != 0) {
                $path = Stamp::model()->fetchIconById($value['stamp']);
                $value['stampPath'] = $path;
            }
            $return[] = $value;
        }
        return $return;
    }

    /**
     * 处理default/show页面要显示的数据
     * @param array $data
     * @return array
     */
    public static function processDefaultShowData($diary)
    {
        //是否有锁定多少天前的日志
        $dashboardConfig = Ibos::app()->setting->get('setting/diaryconfig');
        $lockday = $dashboardConfig['lockday'] ? intval($dashboardConfig['lockday']) : 0;
        $todayTime = (int)strtotime(date('Y-m-d', time()));  //今天的开始时间，即00:00
        $diaryTime = (int)$diary['diarytime'];
        $diffDay = ($todayTime - $diaryTime) / (24 * 60 * 60);  //相差多少天
        if ($lockday > 0 && $diffDay > $lockday) {
            $diary['editIsLock'] = 1;
        } else {
            $diary['editIsLock'] = 0;
        }
        $diary['addtime'] = date('Y-m-d H:i:s', $diary['addtime']);
        $diary['originalDiarytime'] = $diary['diarytime'];
        $diary['diarytime'] = DiaryUtil::getDateAndWeekDay(date('Y-m-d', $diary['diarytime']));
        $diary['nextDiarytime'] = DiaryUtil::getDateAndWeekDay(date('Y-m-d', $diary['nextdiarytime']));
        $diary['realname'] = User::model()->fetchRealnameByUid($diary['uid']);
        $diary['departmentName'] = Department::model()->fetchDeptNameByUid($diary['uid']);
        $diary['shareuid'] = StringUtil::wrapId($diary['shareuid']);
        return $diary;
    }

    /**
     * review/list页面要显示的数据
     * @param integer $uid
     * @param type $data
     * @return array
     */
    public static function processReviewListData($uid, $data)
    {
        $result = array();
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array('uid' => $uid));
        $auidArr = Convert::getSubByKey($attentions, 'auid');
        foreach ($data as $diary) {
            $diary['content'] = StringUtil::cutStr(strip_tags($diary['content']), 255);
            $diary['realname'] = User::model()->fetchRealnameByUid($diary['uid']);
            $diary['addtime'] = Convert::formatDate($diary['addtime'], 'u');
            $isattention = in_array($diary['uid'], $auidArr);
            $diary['isattention'] = $isattention ? 1 : 0;
            if (empty($diary['readeruid'])) {
                $diary['readercount'] = 0;
            } else {
                $diary['readercount'] = count(explode(',', trim($diary['readeruid'], ',')));
            }
            $result[] = $diary;
        }
        return $result;
    }

    /**
     * share/list页面要显示的数据
     * @param array $data
     * @return array
     */
    public static function processShareListData($uid, $data)
    {
        $result = array();
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array('uid' => $uid));
        $auidArr = Convert::getSubByKey($attentions, 'auid');
        foreach ($data as $diary) {
            $diary['content'] = StringUtil::cutStr(strip_tags($diary['content']), 255);
            $diary['realname'] = User::model()->fetchRealnameByUid($diary['uid']);
            $diary['addtime'] = Convert::formatDate($diary['addtime'], 'u');
            $isattention = in_array($diary['uid'], $auidArr);
            $diary['isattention'] = $isattention ? 1 : 0;
            $diary['user'] = User::model()->fetchByUid($diary['uid']);
            $result[] = $diary;
        }
        return $result;
    }

    /**
     * 判断是否有权限阅读个人日志
     * @param integer $uid 访问者uid
     * @param array $diary 访问的日志
     * @return boolean
     */
    public static function checkReadScope($uid, $diary)
    {
        if (isset($diary['uid']) && $uid == $diary['uid']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断是否有权限评阅某篇日志
     * @param integer $uid 访问者uid
     * @param array $diary 访问的日志
     * @return boolean
     */
    public static function checkReviewScope($uid, $diary)
    {
        if (isset($diary['uid']) && UserUtil::checkIsSub($uid, $diary['uid'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查是否有该日志的查看权限(关注和共享)
     * @param integer $uid 访问者uid
     * @param integer $diary 访问的日志
     * @return boolean
     */
    public static function checkScope($uid, $diary)
    {
        if (!isset($diary['uid'])) {
            return false;
        }
        if (isset($diary['shareuid']) && in_array($uid, explode(',', $diary['shareuid']))) {
            // 访问者是这边日志的共享人
            return true;
        } elseif ($uid == $diary['uid']) {
            // 自己的日志也能访问
            return true;
        } elseif (self::checkReviewScope($uid, $diary)) {
            // 上司也有权限
            return true;
        } else {
            return false;
        }
    }

}
