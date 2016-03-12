<?php

/**
 * 组织架构模块函数库
 *
 * @package application.app.user.utils
 * @version $Id: org.php -1   $ 
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\modules\main\utils\Main as MainUtil;
use application\modules\user\utils\User as UserUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\role\utils\Role as RoleUtil;

class Org {

	/**
	 * 更新组织架构js调用接口
	 * @staticvar boolean $execute 执行标识，确保一个进程只执行一次更新操作
	 * @return boolean 执行成功标识
	 */
	public static function update() {
		static $execute = false;
		if ( !$execute ) {
			self::createStaticJs();
			$execute = true;
		}
		return $execute;
	}

	public static function hookSyncUser( $uid, $pwd = '', $syncFlag = 1 ) {
		$type = '';
		$imCfg = array();
		foreach ( IBOS::app()->setting->get( 'setting/im' ) as $imType => $config ) {
			if ( $config['open'] == '1' ) {
				$type = $imType;
				$imCfg = $config;
				break;
			}
		}
		if ( !empty( $type ) && !empty( $imCfg ) && $imCfg['syncuser'] == '1' ) {
			MainUtil::setCookie( 'hooksyncuser', 1, 30 );
			MainUtil::setCookie( 'syncurl', IBOS::app()->createUrl( 'dashboard/organizationApi/syncUser', array( 'type' => $type, 'uid' => $uid, 'pwd' => $pwd, 'flag' => $syncFlag ) ), 30 );
		}
	}

	/**
	 * 生成组织架构静态文件JS
	 * @return void 
	 */
	private static function createStaticJs() {
		//更新最新缓存到全局
        Cache::load(array('users', 'department', 'position'), true);
		$unit = IBOS::app()->setting->get( 'setting/unit' );
		$department = DepartmentUtil::loadDepartment();
		$users = UserUtil::loadUser();
		$position = PositionUtil::loadPosition();
		$positionCategory = PositionUtil::loadPositionCategory();
        $role = RoleUtil::loadRole();
		$companyData = self::initCompany( $unit );
		$deptData = self::initDept( $department );
		$userData = self::initUser( $users );
		$posData = self::initPosition( $position );
		$posCatData = self::initPositionCategory( $positionCategory );
        $roleData = self::initRole($role);
		$default = file_get_contents( PATH_ROOT . '/static/js/src/org.default.js' );
		if ( $default ) {
			$patterns = array(
				'/\{\{(company)\}\}/',
				'/\{\{(department)\}\}/',
				'/\{\{(position)\}\}/',
				'/\{\{(users)\}\}/',
				'/\{\{(positioncategory)\}\}/',
                '/\{\{(role)\}\}/',
			);
			$replacements = array(
				$companyData,
				$deptData,
				$posData,
				$userData,
                $posCatData,
                $roleData
			);
			$new = preg_replace( $patterns, $replacements, $default );
			File::createFile( PATH_ROOT . '/data/org.js', $new );
			// 更新VERHASH
			Cache::update( 'setting' );
		}
	}

	/**
	 * 初始化岗位分类数据
	 * @return string
	 */
	private static function initPositionCategory( $categorys ) {
		$catList = '';
		if ( !empty( $categorys ) ) {
			foreach ( $categorys as $catId => $category ) {
                $catList .= "{id: 'f_{$catId}',"
                        . " text: '{$category['name']}',"
                        . " name: '{$category['name']}',"
                        . " type: 'positioncategory',"
                        . " pId: 'f_{$category['pid']}',"
                        . " open: 1,"
                        . " nocheck:true},\n";
			}
		}
		return $catList;
	}

	/**
	 * 初始化公司数据
	 * @param array $unit 单位信息
	 * @return string
	 */
	private static function initCompany( $unit ) {
        $comList = "{id: 'c_0',"
                . " text: '{$unit['fullname']}',"
                . " name: '{$unit['fullname']}',"
                . " iconSkin: 'department',"
                . " type: 'department',"
                . " enable: 1,"
                . " open: 1},\n";
		return $comList;
	}

	/**
	 * 初始化部门静态文件
	 * @param array $department 部门信息数组
	 * @return string
	 */
	private static function initDept( $department ) {
		$deptList = '';
        //15-7-28 下午7:39 gzdzl
        //针对情况1的解决办法：
        //判断是否是字符串，是的话反序列化
        if (!is_array($department)) {
            //反序列化失败返回false
            $department = String::utf8Unserialize($department);
        }
        if (!empty($department) && is_array($department)) {
            foreach ($department as $deptId => $dept) {
                $deptList .= "{id: 'd_{$deptId}',"
                        . " text: '{$dept['deptname']}',"
                        . " name: '{$dept['deptname']}',"
                        . " iconSkin: 'department',"
                        . " type: 'department',"
                        . " pId: 'd_{$dept['pid']}',"
                        . " enable: 1,"
                        . " open: 1},\n";
            }
        } else {
            //do nothing
		}
		return $deptList;
	}

	/**
	 * 初始用户静态文件
	 * @param array $users 用户信息数组
	 * @return string
	 */
	private static function initUser( $users ) {
		$userList = '';
        if (!empty($users)) :
            foreach ($users as $uid => $user) :
                if ($user['status'] == 2):
                    continue; //过滤掉禁用的用户
                endif;
                $deptStr = !empty($user['alldeptid']) ? String::wrapId($user['alldeptid'], 'd') : '';
                $posStr = !empty($user['allposid']) ? String::wrapId($user['allposid'], 'p') : '';
                $roleStr = !empty($user['allroleid']) ? String::wrapId($user['allroleid'], 'r') : '';
                $userList .= "{id: 'u_{$uid}',
                text: '{$user['realname']}', 
                name: '{$user['realname']}', 
                phone: '{$user['mobile']}', 
                iconSkin: 'user', 
                type: 'user', 
                enable: 1, 
                imgUrl:'{$user['avatar_small']}',
                avatar_small:'{$user['avatar_small']}',
                avatar_middle:'{$user['avatar_middle']}',
                avatar_big:'{$user['avatar_big']}',
                spaceurl:'{$user['space_url']}',
                department:'{$deptStr}',
                role:'{$roleStr}',
                position: '{$posStr}'},\n";
            endforeach;
        endif;
		return $userList;
	}

	/**
	 * 初始化岗位信息数据
	 * @param array $position 岗位信息数组
	 * @return array
	 */
	private static function initPosition( $position ) {
		$posList = '';
        if (!is_array($position)) {
            $position = String::utf8Unserialize($position);
        }
        if (!empty($position) && is_array($position)) {
            foreach ($position as $posId => $pos) {
                $posList .= "{id: 'p_{$posId}',"
                        . " text: '{$pos['posname']}',"
                        . " name: '{$pos['posname']}', "
                        . " iconSkin: 'position', "
                        . " type: 'position', "
                        . " pId:'f_{$pos['catid']}', "
                        . " enable: 1},\n ";
			}
		}
		return $posList;
	}

    private static function initRole($role) {
        $roleList = '';
        $role = !is_array($role) ? String::utf8Unserialize($role) : $role;
        if (!empty($role) && is_array($role)):
            foreach ($role as $roleid => $row):
                $roleList .= "{id: 'r_{$roleid}',"
                        . " text: '{$row['rolename']}',"
                        . " name: '{$row['rolename']}', "
                        . " roletype: '{$row['roletype']}', "
                        . " iconSkin: 'role', "
                        . " type: 'role', "
                        . " enable: 1, "
                        . " open: 1},\n ";
            endforeach;
        endif;
        return $roleList;
    }
}
