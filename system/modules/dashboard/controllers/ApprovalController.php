<?php

/**
 * 后台模块 审批流程控制器文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台审批流程控制器
 *
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: PageController.php 2052 2014-04-24 10:05:11Z gzhzh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;
use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalStep;

class ApprovalController extends BaseController
{

    /**
     * 首页
     */
    public function actionIndex()
    {
        $approvals = Approval::model()->fetchAllApproval();
        foreach ($approvals as &$approval) {
            $approval = $approval->getAttributes();
            $approval['levels'] = array();
        }
        $params = array(
            'approvals' => $this->handleShowData($approvals)
        );
        $this->render('index', $params);
    }

    /**
     * 添加
     */
    public function actionAdd()
    {
        $formSubmit = Env::submitCheck('approvalSubmit');
        if ($formSubmit) {
            $data = $this->handleSaveData($_POST);
            $data['addtime'] = TIMESTAMP;
            $id = Approval::model()->add($data, true);
            $newData = array();
            for ($level = 1; $level <= $data['level']; $level++) {
                $newData['aid'] = $id;
                $newData['step'] = $level;
                $newData['uids'] = $data["level{$level}"];
                ApprovalStep::model()->add($newData);
            }
            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('approval/index'));
        } else {
            $this->render('add');
        }
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $formSubmit = Env::submitCheck('approvalSubmit');
        if ($formSubmit) {
            $id = intval(Env::getRequest('id'));
            $data = $this->handleSaveData($_POST);
            $attributes = array('name' => $data['name'], 'level' => $data['level'], 'free' => $data['free'], 'desc' => $data['desc']);
            $condition = 'id=:id';
            $params = array(':id' => $id);
            Approval::model()->updateAll($attributes, $condition, $params);
            ApprovalStep::model()->deleteAll(array("condition" => "aid='$id'"));
            $newData = array();
            for ($level = 1; $level <= $data['level']; $level++) {
                $newData['aid'] = $id;
                $newData['step'] = $level;
                $newData['uids'] = $data["level{$level}"];
                ApprovalStep::model()->add($newData);
            }
            $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('approval/index'));
        } else {
            $id = Env::getRequest('id');
            $approval = Approval::model()->fetchByPk($id);
            $approval['level1'] = '';
            $approval['level2'] = '';
            $approval['level3'] = '';
            $approval['level4'] = '';
            $approval['level5'] = '';
            $approval['free'] = '';
            for ($level = 1; $level <= $approval['level']; $level++) {
                $lev = ApprovalStep::model()->getApprovalerStr($id, $level);
                $approval["level{$level}"] = StringUtil::wrapId($lev);
            }
            $approval['free'] = StringUtil::wrapId($approval['free']);
            $params = array(
                'approval' => $approval
            );
            $this->render('edit', $params);
        }
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $id = Env::getRequest('id');
            $delRet = Approval::model()->deleteApproval($id);
            if ($delRet) {
                $ret['isSuccess'] = true;
                $ret['msg'] = Ibos::lang('Del succeed', 'message');
            } else {
                $ret['isSuccess'] = false;
                $ret['msg'] = Ibos::lang('Del failed', 'message');
            }
            $this->ajaxReturn($ret);
        }
    }

    /**
     * 处理页面输出数据
     * @param type $data
     * @return type
     */
    protected function handleShowData($data)
    {
        foreach ($data as $k => $approval) {
            for ($level = 1; $level <= $approval['level']; $level++) {
                $lev = ApprovalStep::model()->getApprovalerStr($approval['id'], $level);
                $field = "level{$level}";
                $data[$k]['levels'][$level] = $this->getShowNames($lev);
                $data[$k]['levels'][$level]['levelClass'] = $this->getShowLevelClass($field);
            }
            $data[$k]['free'] = $this->getShowNames($approval['free']);
            $data[$k]['free']['levelClass'] = $this->getShowLevelClass('free');
        }
        return $data;
    }

    /**
     * 处理页面显示的审批人
     * @param mix $uids 数组或逗号隔开的字符串
     * @return array
     */
    protected function getShowNames($uids)
    {
        $uids = is_array($uids) ? $uids : explode(',', $uids);
        $names = User::model()->fetchRealnamesByUids($uids);
        $nums = count($uids);
        if ($nums >= 4) {
            $show = StringUtil::cutStr($names, 30) . " 等{$nums}人";
        } else {
            $show = $names;
        }
        $ret = array(
            'show' => $show, // 页面显示
            'title' => $names // 鼠标移上去显示 
        );
        return $ret;
    }

    /**
     * 处理审批步骤的css样式class
     * @param string $level 审批等级（'level1', 'level2', 'level3', 'level4', 'level5', 'free'）
     * @return string
     */
    protected function getShowLevelClass($level)
    {
        $allLevel = array(
            'level1' => 'o-step-1',
            'level2' => 'o-step-2',
            'level3' => 'o-step-3',
            'level4' => 'o-step-4',
            'level5' => 'o-step-5',
            'free' => 'o-step-escape',
        );
        return $allLevel[$level];
    }

    /**
     * 处理审批流程添加/修改的数据
     * @param array $post 前端post过来的数据
     * @return array 返回处理过后写入数据库的数组
     */
    protected function handleSaveData($post)
    {
        $ret = array(
            'name' => \CHtml::encode($post['name']),
            'level' => $post['level'],
            'level1' => implode(',', StringUtil::getId($post['level1'])),
            'level2' => implode(',', StringUtil::getId($post['level2'])),
            'level3' => implode(',', StringUtil::getId($post['level3'])),
            'level4' => implode(',', StringUtil::getId($post['level4'])),
            'level5' => implode(',', StringUtil::getId($post['level5'])),
            'free' => implode(',', StringUtil::getId($post['free'])),
            'desc' => \CHtml::encode($post['desc']),
        );
        return $ret;
    }

}
