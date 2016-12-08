<?php

/**
 * main模块的Api控制器
 *
 * @version $Id$
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\utils\User;

class ApiController extends Controller
{

    /**
     * 初始化模块数据
     * @return void
     */
    public function actionLoadModule()
    {
        $moduleStr = Env::getRequest('module');
        $moduleStr = urldecode($moduleStr);
        $moduleArr = explode(',', $moduleStr);
        $data = MainUtil::execLoadSetting('renderIndex', $moduleArr);
        $this->ajaxReturn($data);
    }

    /**
     * 加载最新数据
     * @return void
     */
    public function actionLoadNew()
    {
        $moduleStr = Env::getRequest('module');
        $moduleStr = urldecode($moduleStr);
        $moduleArr = explode(',', $moduleStr);
        $data = MainUtil::execLoadSetting('loadNew', $moduleArr);
        $this->ajaxReturn($data);
    }

    public function actionOrgUser()
    {
        $uids = Env::getRequest('uids');
        $uidArray = StringUtil::getUidAByUDPX($uids);
        $userArray = User::wrapUserInfo($uidArray, false, false);
        $return = array();
        $index = 0;
	foreach ( $userArray as $user ) {
		$return[$index]['id'] = 'u_' . $user['uid'];
		$return[$index]['text'] = $user['realname'];
		$return[$index]['mobile'] = $user['mobile'];
		// 头像小尺寸
		$return[$index]['avatar_small'] = Org::getDataStatic( $user['uid'], 'avatar', 'small' );
		// 头像中尺寸
		$return[$index]['avatar_middle'] = Org::getDataStatic( $user['uid'], 'avatar', 'middle' );
		// 头像大尺寸
		$return[$index]['avatar_big'] = Org::getDataStatic( $user['uid'], 'avatar', 'big' );
		$return[$index]['spaceurl'] = '?r=user/home/index&uid=' . $user['uid'];
		$return[$index]['department'] = empty( $user['deptname'] ) ? '' : $user['deptname'];
		$return[$index]['position'] = empty( $user['posname'] ) ? '' : $user['posname'];
		$return[$index]['role'] = empty( $user['rolename'] ) ? '' : $user['rolename'];
		$return[$index]['deptid'] = empty( $user['deptid'] ) ? 'c_0' : 'd_' . $user['deptid'];
		$return[$index]['positionid'] = empty( $user['positionid'] ) ? '' : 'p_' . $user['positionid'];
		$return[$index]['roleid'] = empty( $user['roleid'] ) ? '' : 'r_' . $user['roleid'];
		$index++;
	}
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'data' => $return,
        ));
    }

    public function actionOrgRelatedDept()
    {
        $ids = Env::getRequest('deptids');
        $idArray = explode(',', $ids);
        $return = array();
        foreach ($idArray as $id) {
            $uidArray = StringUtil::getUidAByUDPX($id, true, false, false);
            $return[$id] = array_map(function ($temp) {
                return 'u_' . $temp;
            }, $uidArray);
        }
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => 'deptids里不要太多数据，尤其是存在c_0时',
            'data' => $return,
        ));
    }

    public function actionOrgRelatedRole()
    {
        $ids = Env::getRequest('roleids');
        $idArray = explode(',', $ids);
        $return = array();
        foreach ($idArray as $id) {
            $uidArray = StringUtil::getUidAByUDPX($id, true, false, false);
            $return[$id] = array_map(function ($temp) {
                return 'u_' . $temp;
            }, $uidArray);
        }
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => 'roleids里不要太多数据',
            'data' => $return,
        ));
    }

    public function actionOrgRelatedPosition()
    {
        $ids = Env::getRequest('positionids');
        $idArray = explode(',', $ids);
        $return = array();
        foreach ($idArray as $id) {
            $uidArray = StringUtil::getUidAByUDPX($id, true, false, false);
            $return[$id] = array_map(function ($temp) {
                return 'u_' . $temp;
            }, $uidArray);
        }
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => 'positionids里不要太多数据',
            'data' => $return,
        ));
    }

    public function actionJs($type)
    {
        $cacheTime = 3600 * 24 * 7;
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $browserCachedCopyTimestamp = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            if (($browserCachedCopyTimestamp + $cacheTime) > time()) {
                header("HTTP/1.1 304");
                exit(1);
            }
        }
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . " GMT");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTime) . " GMT");
        header("Cache-Control:max-age=3600");
        $cache = Cache::get($type . '_js');
        if (!$cache) {
            Org::update($type);
        }
        echo Cache::get($type . '_js');
    }

    public function actionIndex()
    {
        //do nothing
        return true;
    }
}
