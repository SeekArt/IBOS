<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\utils\Dashboard;
use application\modules\user\model\User;
use application\modules\user\model\UserGroup;

class UsergroupController extends BaseController
{

    /**
     * 用户组设置
     * @return void
     */
    public function actionIndex()
    {
        $formSubmit = Env::submitCheck('userGroupSubmit');
        if ($formSubmit) {

            $pre = -9999999999;
            foreach ($_POST['groups'] as $k => $group) {
                if (intval($group['creditshigher']) <= $pre) {
                    //TODO 这里修复的应该更加友好的提示
                    die('错误：' . $group['title'] . '=>' . $group['creditshigher']);
                } else {
                    $pre = intval($group['creditshigher']);
                }
            }

            // 更新与添加操作
            $groups = $_POST['groups'];
            $newGroups = isset($_POST['newgroups']) ? $_POST['newgroups'] : array();
            $groupNewAdd = Dashboard::arrayFlipKeys($newGroups);
            foreach ($groupNewAdd as $k => $v) {
                if (!$v['title']) {
                    unset($groupNewAdd[$k]);
                } elseif (!$v['creditshigher']) {
                    $this->error(Ibos::lang('Usergroups update creditshigher invalid'));
                } else {
                    $groupNewAdd[$k]['title'] = \CHtml::encode($v['title']);
                }
            }
            $groupNewKeys = array_keys($groups);
            $maxGroupId = max($groupNewKeys);
            foreach ($groupNewAdd as $k => $v) {
                $groups[$k + $maxGroupId + 1] = $v;
            }
            $orderArray = array();
            if (is_array($groups)) {
                foreach ($groups as $id => $group) {
                    if (($id == 0 && (!$group['title'] || $group['creditshigher'] == ''))) {
                        unset($groups[$id]);
                    } else {
                        $orderArray[$group['creditshigher']] = $id;
                    }
                }
            }
            if (empty($orderArray) || min(array_flip($orderArray)) >= 0) {
                $this->error(Ibos::lang('Usergroups update credits invalid'));
            }

            ksort($orderArray);
            $rangeArray = array();
            $lowerLimit = array_keys($orderArray);
            for ($i = 0; $i < count($lowerLimit); $i++) {
                $rangeArray[$orderArray[$lowerLimit[$i]]] = array(
                    'creditshigher' => isset($lowerLimit[$i - 1]) ? $lowerLimit[$i] : -999999999,
                    'creditslower' => isset($lowerLimit[$i + 1]) ? $lowerLimit[$i + 1] : 999999999
                );
            }
            foreach ($groups as $id => $group) {
                $creditshigherNew = $rangeArray[$id]['creditshigher'];
                $creditslowerNew = $rangeArray[$id]['creditslower'];
                if ($creditshigherNew == $creditslowerNew) {
                    $this->error(Ibos::lang('Usergroups update credits duplicate'));
                }
                if (in_array($id, $groupNewKeys)) {
                    UserGroup::model()->modify($id, array(
                            'title' => $group['title'],
                            'creditshigher' => $creditshigherNew,
                            'creditslower' => $creditslowerNew)
                    );
                } elseif ($group['title'] && $group['creditshigher'] != '') {
                    $data = array(
                        'title' => $group['title'],
                        'creditshigher' => $creditshigherNew,
                        'creditslower' => $creditslowerNew,
                    );
                    UserGroup::model()->add($data);
                }
                //更新所有用户的用户组
                $userAlls = User::model()->fetchAll();
                foreach ($userAlls as $userAll){
                    $groupId = UserGroup::model()->fetchByCredits($userAll['credits']);
                    if (!empty($groupId)){
                        User::model()->modify($userAll['uid'], array(
                            'groupid' => $groupId['gid']
                        ));
                    }
                }
            }
            // 删除操作
            $removeId = $_POST['removeId'];
            if (!empty($removeId)) {
                UserGroup::model()->deleteById($removeId);
            }
            Cache::update(array('UserGroup'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $groups = UserGroup::model()->fetchAll(array('order' => 'creditshigher'));
            $data = array('data' => $groups);
            $this->render('index', $data);
        }
    }

}
