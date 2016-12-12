<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User as UserModel;
use application\modules\user\utils\User;

class RolesuperController extends BaseController
{

    private $userA = array();

    public function init()
    {
        parent::init();
        $isadministrator = Ibos::app()->user->isadministrator;
        if (empty($isadministrator)) {
            $this->error(Ibos::lang('Valid access', 'error'), '', $this->errorParam);
        }
        $adminUidArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from(UserModel::model()->tableName())
            ->where(" `isadministrator` = '1' ")
            ->queryColumn();
        $this->userA = UserModel::model()->fetchAllByUids($adminUidArray);
    }

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex()
    {
        $param = array(
            'userA' => $this->userA,
            'count' => count($this->userA),
        );
        $this->render('index', $param);
    }

    /**
     * 角色编辑
     * @return void
     */
    public function actionEdit()
    {
        $u = Env::getRequest('uid');
        $uidATemp = StringUtil::getUidAByUDPX($u);
        $uidA = array_unique($uidATemp);
        if (count($uidA) > '3') {
            $this->error(Ibos::lang('superadmin cannot beyond 3'));
        }
        if (count($uidA) == '0') {
            $this->error(Ibos::lang('superadmin must set at least one'));
        }
        $uid = Ibos::app()->user->uid;
        if (!in_array($uid, $uidA)) {
            $this->error(Ibos::lang('superadmin setting must contain yourself'));
        }
        $uidS = implode(',', $uidA);
        $where = sprintf(" FIND_IN_SET( `uid`, '%s' ) ", $uidS);
        UserModel::model()->updateAll(array('isadministrator' => 0));
        $counter = UserModel::model()->updateAll(array('isadministrator' => 1,), $where);
        User::wrapUserInfo($uidS, true, true);
        $this->ajaxReturn(array(
            'isSuccess' => !empty($counter),
            'msg' => !empty($counter) ?
                Ibos::lang('Edit success') :
                Ibos::lang('Edit failed'),
        ));
    }

}
