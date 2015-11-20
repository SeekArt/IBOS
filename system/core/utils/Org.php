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
		Cache::load( array( 'department', 'position' ), true );
		$unit = IBOS::app()->setting->get( 'setting/unit' );
		$department = DepartmentUtil::loadDepartment();
		$users = UserUtil::loadUser();
		$position = PositionUtil::loadPosition();
		$positionCategory = PositionUtil::loadPositionCategory();
		$companyData = self::initCompany( $unit );
		$deptData = self::initDept( $department );
		$userData = self::initUser( $users );
		$posData = self::initPosition( $position );
		$posCatData = self::initPositionCategory( $positionCategory );
		$default = file_get_contents( PATH_ROOT . '/static/js/src/org.default.js' );
		if ( $default ) {
			$patterns = array(
				'/\{\{(company)\}\}/',
				'/\{\{(department)\}\}/',
				'/\{\{(position)\}\}/',
				'/\{\{(users)\}\}/',
				'/\{\{(positioncategory)\}\}/',
			);
			$replacements = array(
				$companyData,
				$deptData,
				$posData,
				$userData,
				$posCatData
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
				$catList .= "{id: 'f_{$catId}', text: '{$category['name']}', name: '{$category['name']}', type: 'positioncategory', pId: 'f_{$category['pid']}',open: 1,nocheck:true},\n";
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
		$comList = "{id: 'c_0', text: '{$unit['fullname']}', name: '{$unit['fullname']}', iconSkin: 'department', type: 'department', enable: 1, type: 0, open: 1},\n";
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
            $department = unserialize($department);
        }
        if (!empty($department) && is_array($department)) {
            foreach ($department as $deptId => $dept) {
                $deptList .= "{id: 'd_{$deptId}', text: '{$dept['deptname']}', name: '{$dept['deptname']}', iconSkin: 'department', type: 'department', pId: 'd_{$dept['pid']}', type: 3, enable: 1, open: 1},\n";
            }
        } else {
            //debug:这种情况是不正常的情况才会到这里，所以可以暂时留着
            /*
             * 
             * 这里发生错误导致的结果是：在新增用户时无法选择部门
             * 
             * 15-7-28 下午7:56
             * @author gzdzl
             * 目前发现出现问题出现的情况是：
             * 1.后台管理清空的数据缓存后再操作新增部门时（清理数量缓存->添加部门）
             * 
             * 读取过来的缓存数据有错误，不是得到一个数组
             * 成立的条件是$department是一个字符串（序列化后的数组），而不是数组
             * 
             * 针对情况1的解决办法：
             * 判断是否是字符串，是的话反序列化
             * 
             * 反序列化失败这里也会运行
             */
            file_put_contents('dep_' . time() . '.txt', var_export($department, true));
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
		if ( !empty( $users ) ) {
			foreach ( $users as $uid => $user ) {
                if ($user['status'] == 2)
                    continue; //过滤掉禁用的用户
				$deptStr = $posStr = '';
				if ( !empty( $user['alldeptid'] ) ) {
					$deptStr = String::wrapId( $user['alldeptid'], 'd' );
				}
				if ( !empty( $user['allposid'] ) ) {
					$posStr = String::wrapId( $user['allposid'], 'p' );
				}
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
                position: '{$posStr}'},\n";
			}
		}
		return $userList;
	}

	/**
	 * 初始化岗位信息数据
	 * @param array $position 岗位信息数组
	 * @return array
	 */
	private static function initPosition( $position ) {
		$posList = '';
        /**
         * 15-8-3 下午2:01 gzdzl
         * 清空缓存的数据后，缓存的数据还没有添加回来之前
         * 再添加岗位时，这里的position是一个字符串
         * 需要反序列化回来变成数组
         * unserialize发生错误返回false，并产生E_NOTICE
         */
        if (!is_array($position)) {
            $position = @unserialize($position);
        }
        if (!empty($position) && is_array($position)) {
			foreach ( $position as $posId => $position ) {
				$posList .= "{id: 'p_{$posId}', text: '{$position['posname']}', name: '{$position['posname']}', iconSkin: 'position', type: 'position', pId:'f_{$position['catid']}', enable: 1, open: 0},\n";
			}
		}
		return $posList;
	}

}
