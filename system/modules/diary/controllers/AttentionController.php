<?php

/**
 * 工作日志模块------关注控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 工作日志模块------关注控制器，继承ICController
 * @package application.modules.diary.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class AttentionController extends BaseController
{

    /**
     * 检查是否开启关注日志功能
     */
    public function init()
    {
        if (!$this->issetAttention()) {
            $this->error(Ibos::lang('Attention not open'), $this->createUrl('default/index'));
        }
        parent::init();
    }

    /**
     * 取得侧栏导航
     * @return string
     */
    protected function getSidebar()
    {
        $sidebarAlias = 'application.modules.diary.views.attention.sidebar';
        $aUids = DiaryAttention::model()->fetchAuidByUid(Ibos::app()->user->uid);
        $aUsers = array();
        if (!empty($aUids)) {
            $aUsers = User::model()->fetchAllByUids($aUids);
        }
        $params = array(
            'aUsers' => $aUsers,
            'statModule' => Ibos::app()->setting->get('setting/statmodules'),
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    /**
     * 列表页显示
     * @return void
     */
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        if (empty($op) || !in_array($op, array('default', 'personal'))) {
            $op = 'default';
        }
        if ($op == 'default') {
            //取得shareuid字段中包含作者的数据
            $date = 'yesterday';
            if (array_key_exists('date', $_GET)) {
                $date = $_GET['date'];
            }
            if ($date == 'today') {
                $time = strtotime(date('Y-m-d'));
                $date = date('Y-m-d');
            } else if ($date == 'yesterday') {
                $time = strtotime(date('Y-m-d')) - 24 * 60 * 60;
                $date = date('Y-m-d', $time);
            } else {
                $time = strtotime($date);
                $date = date('Y-m-d', $time);
            }

            $uid = Ibos::app()->user->uid;
            //关注了哪些人
            $attentions = DiaryAttention::model()->fetchAllByAttributes(array('uid' => $uid));
            $auidArr = Convert::getSubByKey($attentions, 'auid');
            $hanAuidArr = $this->handleAuid($uid, $auidArr);
            $subUidStr = implode(',', $hanAuidArr['subUid']);
            $auidStr = implode(',', $hanAuidArr['aUid']);
            // 下属日志的条件和非下属日志条件
            $condition = "(FIND_IN_SET(uid, '{$subUidStr}') OR (FIND_IN_SET('{$uid}', shareuid) AND FIND_IN_SET(uid, '{$auidStr}') ) ) AND diarytime=$time";
            $paginationData = Diary::model()->fetchAllByPage($condition);
            $params = array(
                'dateWeekDay' => DiaryUtil::getDateAndWeekDay(date('Y-m-d', strtotime($date))),
                'pagination' => $paginationData['pagination'],
                'data' => ICDiary::processShareListData($uid, $paginationData['data']),
                'shareCommentSwitch' => $this->issetSharecomment(),
                'attentionSwitch' => $this->issetAttention()
            );
            //上一天和下一天
            $params['prevAndNextDate'] = array(
                'prev' => date('Y-m-d', (strtotime($date) - 24 * 60 * 60)),
                'next' => date('Y-m-d', (strtotime($date) + 24 * 60 * 60)),
                'prevTime' => strtotime($date) - 24 * 60 * 60,
                'nextTime' => strtotime($date) + 24 * 60 * 60,
            );
            $this->setPageTitle(Ibos::lang('Attention diary'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Personal Office')),
                array('name' => Ibos::lang('Work diary'), 'url' => $this->createUrl('default/index')),
                array('name' => Ibos::lang('Attention diary'))
            ));
            $this->render('index', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 处理关注的uid中，下属uid和非下属uid分离开
     * @param integer $uid 登陆用户uid
     * @param mix $attentionUids 关注的uid
     * @return array
     */
    private function handleAuid($uid, $attentionUids)
    {
        $aUids = is_array($attentionUids) ? $attentionUids : implode(',', $attentionUids);
        $ret['subUid'] = array();
        $ret['aUid'] = array();
        if (!empty($aUids)) {
            foreach ($aUids as $aUid) {
                if (UserUtil::checkIsSub($uid, $aUid)) {
                    $ret['subUid'][] = $aUid;
                } else {
                    $ret['aUid'][] = $aUid;
                }
            }
        }
        return $ret;
    }

    /**
     * 取得某个uid关注并有权限工作日志
     * @return void
     */
    private function personal()
    {
        $uid = Ibos::app()->user->uid;
        $getUid = intval(Env::getRequest('uid'));
        $condition = "uid = '{$getUid}'";
        if (!UserUtil::checkIsSub($uid, $getUid)) {
            $condition .= " AND FIND_IN_SET('{$uid}', shareuid )";
        }
        //是否搜索
        if (Env::getRequest('param') == 'search') {
            $this->search();
        }
        $this->_condition = DiaryUtil::joinCondition($this->_condition, $condition);
        $paginationData = Diary::model()->fetchAllByPage($this->_condition);
        $data = array(
            'pagination' => $paginationData['pagination'],
            'data' => ICDiary::processDefaultListData($paginationData['data']),
            'diaryCount' => Diary::model()->count($this->_condition),
            'commentCount' => Diary::model()->countCommentByReview($getUid),
            'user' => User::model()->fetchByUid($getUid),
            'dashboardConfig' => Ibos::app()->setting->get('setting/diaryconfig')
        );
        $this->setPageTitle(Ibos::lang('Attention diary'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Work diary'), 'url' => $this->createUrl('default/index')),
            array('name' => Ibos::lang('Attention diary'))
        ));
        $this->render('personal', $data);
    }

    /**
     * 编辑关注
     * @return void
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'attention', 'unattention');
        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang('Can not find the path'), $this->createUrl('default/index'));
        }
        if ($option == 'default') {

        } else {
            $this->$option();
        }
    }

    /**
     * 设置关注工作日志
     * @return void
     */
    private function attention()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $auid = Env::getRequest('auid');
            $uid = Ibos::app()->user->uid;
            DiaryAttention::model()->addAttention($uid, $auid);
            $this->ajaxReturn(array('isSuccess' => true, 'info' => Ibos::lang('Attention succeed')));
        }
    }

    /**
     * 取消关注工作日志
     * @return void
     */
    private function unattention()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $auid = Env::getRequest('auid');
            $uid = Ibos::app()->user->uid;
            DiaryAttention::model()->removeAttention($uid, $auid);
            $this->ajaxReturn(array('isSuccess' => true, 'info' => Ibos::lang('Unattention succeed')));
        }
    }

    /**
     * 展示某篇共享日志
     */
    public function actionShow()
    {
        $diaryid = intval(Env::getRequest('diaryid'));
        $uid = Ibos::app()->user->uid;
        if (empty($diaryid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('attention/index'));
        }
        $diary = Diary::model()->fetchByPk($diaryid);
        if (empty($diary)) {
            $this->error(Ibos::lang('No data found'), $this->createUrl('attention/index'));
        }
        if (!ICDiary::checkScope($uid, $diary)) {
            $this->error(Ibos::lang('You do not have permission to view the log'), $this->createUrl('attention/index'));
        }
        //增加阅读记录
        Diary::model()->addReaderuidByPK($diary, $uid);
        //取得原计划和计划外内容,下一次计划内容
        $data = Diary::model()->fetchDiaryRecord($diary);
        $params = array(
            'diary' => ICDiary::processDefaultShowData($diary),
            'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK($diary['diaryid']),
            'data' => $data,
        );
        if (!empty($diary['attachmentid'])) {
            $params['attach'] = Attach::getAttach($diary['attachmentid'], true, true, false, false, true);
            $params['count'] = 0;
        }
        // 是否允许评论
        $params['allowComment'] = $this->issetSharecomment() || UserUtil::checkIsSub($uid, $diary['uid']) ? 1 : 0;
        //阅读人
        if (!empty($diary['readeruid'])) {
            $readerArr = explode(',', $diary['readeruid']);
            $params['readers'] = User::model()->fetchAllByPk($readerArr);
        } else {
            $params['readers'] = '';
        }
        if (!empty($diary['stamp'])) {
            $params['stampUrl'] = Stamp::model()->fetchStampById($diary['stamp']);
        }
        $this->setPageTitle(Ibos::lang('Show Attention diary'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Work diary'), 'url' => $this->createUrl('default/index')),
            array('name' => Ibos::lang('Show Attention diary'))
        ));
        $this->render('show', $params);
    }

}
