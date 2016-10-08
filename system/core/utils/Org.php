<?php

/**
 * 组织架构模块函数库
 *
 * @package application.app.user.utils
 * @version $Id: org.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\modules\department\model\Department as DepartmentModel;
use application\modules\main\utils\Main as MainUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model\UserProfile;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class Org {

	/**
	 * 更新组织架构js调用接口
	 * @param mixed $type NULL表示所有，数组表示对应的类型
	 * @staticvar boolean $execute 执行标识，确保一个进程只执行一次更新操作
	 * @return boolean 执行成功标识
	 */
	public static function update( $type = NULL ) {
		static $execute = false;
		if ( !$execute ) {
			self::createStaticJs( $type );
			$execute = true;
		}
		return $execute;
	}

	public static function hookSyncUser( $uid, $pwd = '', $syncFlag = 1 ) {
		$type = '';
		$imCfg = array();
		foreach ( Ibos::app()->setting->get( 'setting/im' ) as $imType => $config ) {
			if ( $config['open'] == '1' ) {
				$type = $imType;
				$imCfg = $config;
				break;
			}
		}
		if ( !empty( $type ) && !empty( $imCfg ) && $imCfg['syncuser'] == '1' ) {
			MainUtil::setCookie( 'hooksyncuser', 1, 30 );
			MainUtil::setCookie( 'syncurl', Ibos::app()->createUrl( 'dashboard/organizationApi/syncUser', array( 'type' => $type, 'uid' => $uid, 'pwd' => $pwd, 'flag' => $syncFlag ) ), 30 );
		}
	}

	private static function createStaticJs( $type = NULL ) {
		if ( NULL !== $type ) {
			$type = is_array( $type ) ? $type : explode( ',', $type );
		}
		if ( NULL === $type || is_array( $type ) && in_array( 'user', $type ) ) {
			//生成用户文件
			$users = UserUtil::wrapUserInfo( NULL, false, false );
			$userArray = array();
			foreach ( $users as $user ) {
				$userArray['u_' . $user['uid']] = array(
					'id' => 'u_' . $user['uid'],
					'text' => $user['realname'],
					'phone' => $user['mobile'],
					'avatar' => $user['avatar_small'],
					'department' => $user['deptname'],
					'position' => $user['posname'],
					'role' => $user['rolename'],
					'spaceurl' => $user['space_url'],
				);
			}
			$userString = "var Ibos = Ibos || {}; Ibos.data = Ibos.data || {};\nIbos.data.user = " . CJSON::encode( $userArray ) . ';';
			File::setOrgJs( 'user', $userString );
		}
		if ( NULL === $type || is_array( $type ) && in_array( 'department', $type ) ) {
			//生成部门文件
			$departments = DepartmentModel::model()->findDeptmentIndexByDeptid( NULL , array( 'order' => 'pid ASC, sort ASC' ) );
			$departmentArray = array();
			$unit = Ibos::app()->setting->get( 'setting/unit' );
			$departmentArray['c_0'] = array( 'id' => 'c_0', 'text' => $unit['fullname'], 'type' => 'department', );
			if ( !empty( $departments ) ) {
				foreach ( $departments as $department ) {
					$departmentArray['d_' . $department['deptid']] = array(
						'id' => 'd_' . $department['deptid'],
						'text' => $department['deptname'],
						'pid' => 'd_' . $department['pid'],
					);
				}
			}

			$departmentString = "var Ibos = Ibos || {}; Ibos.data = Ibos.data || {};\nIbos.data.department = " . CJSON::encode( $departmentArray ) . ';';
			File::setOrgJs( 'department', $departmentString );
		}

		if ( NULL === $type || is_array( $type ) && in_array( 'role', $type ) ) {
			//生成角色数据
			$roles = RoleUtil::loadRole();
			$roleArray = array();
			if ( !empty( $roles ) ) {
				foreach ( $roles as $role ) {
					$roleArray['r_' . $role['roleid']] = array(
						'id' => 'r_' . $role['roleid'],
						'text' => $role['rolename'],
					);
				}
			}

			$roleString = "var Ibos = Ibos || {}; Ibos.data = Ibos.data || {};\nIbos.data.role = " . CJSON::encode( $roleArray ) . ';';
			File::setOrgJs( 'role', $roleString );
		}

		if ( NULL === $type || is_array( $type ) && in_array( 'position', $type ) ) {
			//生成岗位数据
			$positions = PositionUtil::loadPosition();
			$positionArray = array();
			if ( !empty( $positions ) ) {
				foreach ( $positions as $position ) {
					$positionArray['p_' . $position['positionid']] = array(
						'id' => 'p_' . $position['positionid'],
						'text' => $position['posname'],
						'pid' => 'f_' . $position['catid'],
					);
				}
			}

			$positionString = "var Ibos = Ibos || {}; Ibos.data = Ibos.data || {};\nIbos.data.position = " . CJSON::encode( $positionArray ) . ';';
			File::setOrgJs( 'position', $positionString );
		}

		if ( NULL === $type || is_array( $type ) && in_array( 'positioncategory', $type ) ) {
			//生成岗位分类数据
			$positionCategorys = PositionUtil::loadPositionCategory();
			$positionCategoryArray = array();
			if ( !empty( $positionCategorys ) ) {
				foreach ( $positionCategorys as $positionCategory ) {
					$positionCategoryArray['f_' . $positionCategory['catid']] = array(
						'id' => 'f_' . $positionCategory['catid'],
						'text' => $positionCategory['name'],
						'nocheck' => true,
					);
				}
			}

			$positionCategoryString = "var Ibos = Ibos || {}; Ibos.data = Ibos.data || {};\nIbos.data.positioncategory = " . CJSON::encode( $positionCategoryArray ) . ';';
			File::setOrgJs( 'positioncategory', $positionCategoryString );
		}
	}

	/**
	 * 获取静态资源
	 * @param string $uid
	 * @param string $type
	 * @param string $size
	 * @return string
	 */
	public static function getDataStatic( $uid, $type, $size = 'small' ) {
		if ( $type == 'avatar' ) {
			$path = 'data/avatar/';
		} else {
			$path = 'data/home/';
		}
		$userProfile = UserProfile::model()->findByPk( $uid );
		$fieldName = $type . '_' . $size;
		$return = !empty( $userProfile[$fieldName] ) ?
				$userProfile[$fieldName] :
				$path . 'no' . $type . '_' . $size . '.jpg';
		return $return;
	}

}
