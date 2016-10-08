<?php

/**
 * main模块的默认控制器
 *
 * @version $Id: DefaultController.php 8018 2016-08-25 06:43:45Z tanghang $
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\model\Module;
use application\core\utils\Attach;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module as ModuleUtil;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\department\model\Department;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\model\MenuCommon;
use application\modules\main\model\MenuPersonal;
use application\modules\main\model\ModuleGuide;
use application\modules\main\model\Setting;
use application\modules\main\utils\Main;
use application\modules\main\utils\Update;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class DefaultController extends Controller {

	/**
	 * 办公首页
	 * @return void
	 */
	public function actionIndex() {
		// 所有安装模块
		$modules = Module::model()->fetchAllEnabledModule();
		$widgetModule = $modules;
		foreach ( $widgetModule as $index => $module ) {
			$conf = CJSON::decode( $module['config'] );
			$param = $conf['param'];
			if ( !isset( $param['indexShow'] ) || !isset( $param['indexShow']['widget'] ) ) {
				unset( $widgetModule[$index] );
			}
		}
		// 分别执行每个模块api的loadSetting方法
		$widgets = $this->getWidgets( $modules );
		$moduleSetting = Main::execLoadSetting( 'loadSetting', $widgets );
		$data = array(
			'modules' => $modules,
			'widgetModule' => $moduleSetting,
			'moduleSetting' => CJSON::encode( $moduleSetting ),
			'menus' => MenuPersonal::model()->fetchMenuByUid( Ibos::app()->user->uid )
		);
		$this->setPageTitle( Ibos::lang( 'Home office' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => Ibos::lang( 'Home office' ) )
		) );
		$this->render( 'index', $data );
	}

	/**
	 * 获取widget数组
	 * @param array $modules
	 * @return array
	 */
	private function getWidgets( $modules ) {
		$widgets = array();
		foreach ( $modules as $m ) {
			$config = CJSON::decode( $m['config'] );
			if ( isset( $config['param']['indexShow']['widget'] ) && is_array( $config['param']['indexShow']['widget'] ) ) {
				$widgets = array_merge( $widgets, $config['param']['indexShow']['widget'] );
			}
		}
		return $widgets;
	}

	/**
	 * // todo :: 不支持浏览器的提示界面
	 */
	public function actionUnsupportedBrowser() {
		$alias = 'application.views.browserUpgrade';
		$this->renderPartial( $alias );
	}

	/**
	 * 快捷方式到更新缓存
	 */
	public function actionUpdate() {
		if ( Ibos::app()->getRequest()->getIsPostRequest() ) {
			if ( LOCAL ) {
				@set_time_limit( 0 );
			}
			$op = Env::getRequest( 'op' );
			if ( !in_array( $op, array( 'data', 'static', 'module' ) ) ) {
				return $this->ajaxReturn( array(
							'isSuccess' => false,
							'data' => array(),
							'msg' => '错误的op参数，确定你是正常操作？',
						) );
			}
			$offset = Env::getRequest( 'offset' );
			$isGuest = Ibos::app()->user->isGuest;
			$uid = $isGuest ? 0 : Ibos::app()->user->uid;
			$update = Main::getCookie( $uid . '_update_lock' );
			if ( $offset == '0' && empty( $update ) ) {
				Main::setCookie( $uid . '_update_lock', 1 );
				Cache::update();
			}
			$method = $op . 's';
			if ( $op == 'data' && $offset == '0' ) {
				//这里操作的时候，会强制把用户数据状态改成需要更新
				Ibos::app()->db->createCommand()
						->update( '{{setting}}', array(
							'svalue' => '1',
								), " `skey` = 'cacheuserstatus' " );
			}
			return $this->ajaxReturn( Update::$method( $offset ) );
		} else {
			return $this->renderPartial( 'update' );
		}
	}

	/*
	 * 初始化引导入口
	 */

	public function actionGuide() {
		$operation = Env::getRequest( 'op' );
		if ( !in_array( $operation, array( 'neverGuideAgain', 'checkIsGuided', 'companyInit', 'addUser', 'modifyPassword', 'modifyProfile', 'uploadAvatar' ) ) ) {
			$res['isSuccess'] = false;
			$res['msg'] = Ibos::lang( 'Parameters error', 'error' );
			$this->ajaxReturn( $res );
		} else {
			$this->$operation();
		}
	}

	/**
	 * 不再提醒
	 */
	private function neverGuideAgain() {
		$uid = Ibos::app()->user->uid;
		User::model()->modify( $uid, array( 'newcomer' => 0 ) );
	}

	/**
	 * 检查用户是否引导过
	 */
	private function checkIsGuided() {
		if ( Ibos::app()->request->isAjaxRequest ) {
			// 检查该uid是否引导过
			$uid = Ibos::app()->user->uid;
			$isadministrator = $uid == 1 ? true : false;
			$user = User::model()->fetchByAttributes( array( 'uid' => $uid ) );
			$newcomer = $user['newcomer'];
			if ( !$newcomer ) {
				$this->ajaxReturn( array( 'isNewcommer' => false ) );
			} else {
				if ( $uid == 1 ) {
					// 如果是管理员,返回管理员的初始化引导视图
					$guideAlias = 'application.modules.main.views.default.adminGuide';
					$unit = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
					$data['fullname'] = $unit['fullname'];
					$data['shortname'] = $unit['shortname'];
					$data['pageUrl'] = $unit['systemurl'];
				} else {
					$data['swfConfig'] = Attach::getUploadConfig( $uid );
					$data['uid'] = $uid;
					// 返回一般用户的初始化引导视图
					$guideAlias = 'application.modules.main.views.default.initGuide';
				}
				$account = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'account' ) );
				$data['account'] = $account;
				if ( $account['mixed'] ) {
					$data['preg'] = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
				} else {
					$data['preg'] = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
				}
				$data['lang'] = Ibos::getLangSource( 'main.default' );
				$data['assetUrl'] = $this->getAssetUrl();
				$guideView = $this->renderPartial( $guideAlias, $data, true );
				$this->ajaxReturn( array( 'isNewcommer' => true, 'guideView' => $guideView, 'isadministrator' => $isadministrator ) );
			}
		}
	}

	/**
	 * 填写公司资料
	 */
	private function companyInit() {
		if ( Ibos::app()->request->isAjaxRequest ) {
			// 添加公司资料
			$postData = array();
			$keys = array(
				'logourl', 'phone', 'fullname',
				'shortname', 'fax', 'zipcode',
				'address', 'adminemail', 'systemurl', 'corpcode'
			);
			$unit = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
			foreach ( $keys as $key ) {
				if ( isset( $_POST[$key] ) ) {
					$postData[$key] = StringUtil::filterCleanHtml( $_POST[$key] );
				} else {
					$postData[$key] = '';
				}
			}
			//企业代码是不能修改的
			$postData['corpcode'] = $unit['corpcode'];

			Setting::model()->updateSettingValueByKey( 'unit', $postData );
			Cache::update( array( 'setting' ) );
			// 添加部门
//			Department::model()->deleteAll(); // 由于可能有演示数据,暂时屏蔽
			$depts = Env::getRequest( 'depts' );
			$isSuccess = $this->handleDept( $depts );
			if ( $isSuccess ) {
				$uid = Ibos::app()->user->uid;
				User::model()->modify( $uid, array( 'newcomer' => 0 ) ); // 改成非新人，表示引导过
				$deptCache = DepartmentUtil::loadDepartment();
				$posCache = Ibos::app()->setting->get( 'cache/position' );
				$selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";
				$res['isSuccess'] = true;
				$res['depts'] = StringUtil::getTree( $deptCache, $selectFormat );
				$res['positions'] = $posCache;
			} else {
				$res['isSuccess'] = false;
				$res['msg'] = Ibos::lang( 'Add department fail' );
			}
			$this->ajaxReturn( $res );
		}
	}

	/**
	 * 插入部门数据
	 * @param string $depts 用户输入的部门字符串
	 */
	private function handleDept( $depts ) {
		$depts = trim( StringUtil::filterCleanHtml( $depts ) ); // 安全过滤
		$deptArr = preg_split( "/\n/", $depts ); // 换行符分割部门字符串
		if ( !empty( $deptArr ) ) {
			// 组合带有空格数的部门数组
			foreach ( $deptArr as $index => $deptName ) {
				$deptName = rtrim( $deptName ); // 去掉部门字符后面的空白字符
				if ( empty( $deptName ) ) {
					// 去掉空项
					unset( $deptArr[$index] );
					continue;
				}
				$deptArr[$index] = $deptName;
			}
		}
		if ( !empty( $deptArr ) ) {
			$deptArr = array_values( $deptArr ); // 格式化键名，从0开始
			foreach ( $deptArr as $index => $deptName ) {
				$dept = array();
				$dept['deptname'] = trim( $deptName ); // 最终插入数据库的部门名称
				$indentBlank = strspn( $deptName, " " ); // 缩进空格数
				$indentTab = strspn( $deptName, "	" ); // 缩进的tab
				$indentAll = $indentBlank + $indentTab;
				if ( $indentAll == 0 ) { // 如果没空格，是一级部门
					$dept['pid'] = 0;
					$newId = Department::model()->add( $dept, true );
					if ( $newId ) {
						Department::model()->modify( $newId, array( 'sort' => $newId ), '', array(), false );
					}
				} else { // 否则有空格，则需要查找刚才添加的父级部门
					$accordItem = array();
					foreach ( $deptArr as $k => $v ) {
						$indentBlank2 = strspn( $v, " " ); // 缩进空格数
						$indentTab2 = strspn( $v, "	" ); //缩进的tab
						$indentAll2 = $indentBlank2 + $indentTab2;
						if ( $k < $index && $indentAll2 < $indentAll ) { // 找键值小过它的和空格少于它的，符合条件的再找键值最大的一个
							$accordItem[$k] = $v; // 符合的项
						}
					}
					$upDeptName = '';
					if ( count( $accordItem ) == 1 ) { // 只有一个的话直接拿他的值
						$upDeptName = array_shift( $accordItem );
					} elseif ( count( $accordItem ) > 1 ) { // 多余一个的话就拿键值最大的那个
						$maxKey = max( array_keys( $accordItem ) );
						$upDeptName = $deptArr[$maxKey];
					}
					// 根据父级部门名称查找父级部门deptid
					$upDept = Department::model()->fetchByAttributes( array( 'deptname' => trim( $upDeptName ) ) );
					if ( !empty( $upDept ) ) {
						$dept['pid'] = $upDept['deptid'];
						$newId = Department::model()->add( $dept, true );
						if ( $newId ) {
							Department::model()->modify( $newId, array( 'sort' => $newId ), '', array(), false );
						}
					}
				}
			}
		}
		$newId && Org::update();
		return !!$newId;
	}

	/**
	 * 添加用户
	 */
	private function addUser() {
		if ( Ibos::app()->request->isAjaxRequest ) {
			$fields = array( 'username', 'password', 'realname', 'mobile', 'deptid', 'positionid', 'email' );
			if ( empty( $_POST['username'] ) || empty( $_POST['password'] ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Username or password not empty' ) ) );
			}
			foreach ( $fields as $field ) {
				if ( isset( $_POST[$field] ) && !empty( $_POST[$field] ) ) {
					$_POST[$field] = StringUtil::filterDangerTag( $_POST[$field] ); // 安全过滤
				}
			}
			$salt = StringUtil::random( 6 );
			$userData = array(
				'salt' => $salt,
				'username' => $_POST['username'],
				'password' => !empty( $_POST['password'] ) ? md5( md5( $_POST['password'] ) . $salt ) : '',
				'realname' => $_POST['realname'],
				'mobile' => $_POST['mobile'],
				'createtime' => TIMESTAMP,
				'deptid' => intval( $_POST['deptid'] ),
				'positionid' => intval( $_POST['positionid'] ),
				'email' => $_POST['email']
			);
			$newId = User::model()->add( $userData, true );
			if ( $newId ) {
				UserCount::model()->add( array( 'uid' => $newId ) );
				$ip = Ibos::app()->setting->get( 'clientip' );
				UserStatus::model()->add(
						array(
							'uid' => $newId,
							'regip' => $ip,
							'lastip' => $ip
						)
				);
				UserProfile::model()->add( array( 'uid' => $newId ) );
				// 更新组织架构js调用接口
				Org::update();
				$res['isSuccess'] = true;
			} else {
				$res['isSuccess'] = false;
				$res['msg'] = Ibos::lang( 'Add user failed' );
			}
			$this->ajaxReturn( $res );
		}
	}

	/**
	 * 修改密码
	 */
	private function modifyPassword() {
		if ( Ibos::app()->request->isAjaxRequest ) {
			$uid = Ibos::app()->user->uid;
			$user = User::model()->fetchByAttributes( array( 'uid' => $uid ) );
			if ( Env::getRequest( 'checkOrgPass' ) ) {
				$originalpass = Env::getRequest( 'originalpass' );
				$isSuccess = strcasecmp( md5( md5( $originalpass ) . $user['salt'] ), $user['password'] ) == 0 ? true : false;
				$this->ajaxReturn( array( 'isSuccess' => $isSuccess ) );
			}
			$data = $_POST;
			if ( $data['originalpass'] == '' ) {
				// 没有填写原来的密码
				$res['isSuccess'] = false;
				$res['msg'] = Ibos::lang( 'Original password require' );
			} else if ( strcasecmp( md5( md5( $data['originalpass'] ) . $user['salt'] ), $user['password'] ) !== 0 ) {
				// 密码跟原来的对不上
				$res['isSuccess'] = false;
				$res['msg'] = Ibos::lang( 'Password is not correct' );
			} else if ( !empty( $data['newpass'] ) && strcasecmp( $data['newpass'], $data['newpass_confirm'] ) !== 0 ) {
				// 两次密码不一致
				$res['isSuccess'] = false;
				$res['msg'] = Ibos::lang( 'Confirm password is not correct' );
			} else {
				$password = md5( md5( $data['newpass'] ) . $user['salt'] );
				User::model()->updateByUid( $uid, array( 'password' => $password, 'lastchangepass' => TIMESTAMP ) );
				$res['realname'] = $user['realname'];
				$res['mobile'] = $user['mobile'];
				$res['email'] = $user['email'];
				$res['isSuccess'] = true;
			}
			$this->ajaxReturn( $res );
		}
	}

	/**
	 * 上传头像操作
	 */
	private function uploadAvatar() {
		return false; //使用user模块下的代码了
	}

	/**
	 * 填写个人资料
	 */
	private function modifyProfile() {
		if ( Ibos::app()->request->isAjaxRequest ) {
			$uid = Ibos::app()->user->uid;
			// 生成头像
			if ( !empty( $_POST['src'] ) ) {
				$this->cropImg();
			}
			$profileField = array( 'birthday' );
			$userField = array( 'mobile', 'email' );
			$model = array();
			// 确定更新所使用MODEL
			foreach ( $_POST as $key => $value ) {
				if ( in_array( $key, $profileField ) && !empty( $value ) ) {
					// 生日字段的转换处理
					if ( $key == 'birthday' ) {
						$value = strtotime( $value );
						$model['application\modules\user\model\UserProfile'][$key] = StringUtil::filterCleanHtml( $value );
					}
				} else if ( in_array( $key, $userField ) ) {
					$model['application\modules\user\model\User'][$key] = StringUtil::filterCleanHtml( $value );
				}
			}
			// 更新操作
			foreach ( $model as $modelObject => $value ) {
				$modelObject::model()->modify( $uid, $value );
			}
			// 提交完资料后就改成已引导过
			User::model()->modify( $uid, array( 'newcomer' => 0 ) );
			$isInstallWeibo = ModuleUtil::getIsEnabled( 'weibo' );
			$this->ajaxReturn( array( 'isSuccess' => true, 'isInstallWeibo' => !!$isInstallWeibo ) );
		}
	}

	/**
	 * 生成头像
	 */
	private function cropImg() {
		$uid = Ibos::app()->user->uid;
		// 临时头像地址
		$params = $_POST;
		$params['w'] = 0;
		$params['h'] = 0;
		$params['x'] = 0;
		$params['y'] = 0;
		$params['uid'] = $uid;
		$avatarArray = Ibos::engine()->io()->file()->createAvatar( $params['src'], $params );
		UserProfile::model()->updateAll( $avatarArray, "uid = {$uid}" );
		UserUtil::wrapUserInfo( $uid, true, true, true );
		Ibos::app()->user->setState( 'avatar_big', $avatarArray['avatar_big'] );
		Ibos::app()->user->setState( 'avatar_middle', $avatarArray['avatar_middle'] );
		Ibos::app()->user->setState( 'avatar_small', $avatarArray['avatar_small'] );
	}

	/**
	 * 模块引导
	 */
	public function actionModuleGuide() {
		$uid = Ibos::app()->user->uid;
		$id = StringUtil::filterCleanHtml( Env::getRequest( 'id' ) );
		$op = Env::getRequest( 'op' );
		if ( $op == 'checkHasGuide' ) {
			// 返回用户是否已经引导过
			$guide = ModuleGuide::model()->fetchGuide( $id, $uid );
			$hasGuide = empty( $guide ) ? false : true;
			$this->ajaxReturn( array( 'hasGuide' => $hasGuide ) );
		} elseif ( $op == 'finishGuide' ) {
			// 完成引导
			ModuleGuide::model()->add( array(
				'route' => $id,
				'uid' => $uid
			) );
		}
	}

	/**
	 * 前台授权证书
	 * @return type
	 */
	public function actionGetCert() {
		$certAlias = 'application.modules.main.views.default.cert';
		$params = array(
			'lang' => Ibos::getLangSource( 'main.default' )
		);
		$certView = $this->renderPartial( $certAlias, $params, true );
		echo $certView;
	}

	/**
	 * 前台未授权证书
	 * @return type
	 */
	public function actionUnAuthorized() {
		$certAlias = 'application.modules.main.views.default.unauthorized';
		$params = array(
			'lang' => Ibos::getLangSource( 'main.default' )
		);
		$certView = $this->renderPartial( $certAlias, $params, true );
		echo $certView;
	}

	/**
	 * 设置个人常用菜单
	 */
	public function actionPersonalMenu() {
		if ( Env::submitCheck( 'personalMenu' ) ) {
			$ids = Env::getRequest( 'mod' );
			$uid = Ibos::app()->user->uid;
			MenuPersonal::model()->deleteAll( "uid = {$uid}" );
			if ( !empty( $ids ) ) {
				$common = implode( ',', $ids );
			} else {
				$common = '';
			}
			$data = array(
				'uid' => $uid,
				'common' => $common
			);
			MenuPersonal::model()->add( $data );
			$this->ajaxReturn( array( 'isSuccess' => true ) );
		}
	}

	/**
	 * 设置默认常用菜单
	 */
	public function actionCommonMenu() {
		if ( Env::submitCheck( 'commonMenu' ) ) {
			$ids = Env::getRequest( 'mod' );
			MenuCommon::model()->updateAll( array( 'sort' => 0, 'iscommon' => 0 ) );
			if ( !empty( $ids ) ) {
				foreach ( $ids as $index => $id ) {
					MenuCommon::model()->updateAll( array( 'sort' => intval( $index ) + 1, 'iscommon' => 1 ), "id='{$id}'" );
				}
			}
			$this->ajaxReturn( array( 'isSuccess' => true ) );
		}
	}

	/**
	 * 恢复默认菜单设置
	 */
	public function actionRestoreMenu() {
		if ( Env::submitCheck( 'restoreMenu' ) ) {
			$uid = Ibos::app()->user->uid;
			MenuPersonal::model()->deleteAll( "uid = {$uid}" );
			$this->ajaxReturn( array( 'isSuccess' => true ) );
		}
	}

	/**
	 *  获取通用文件上传对话框
	 */
	public function actionGetUploadDlg() {
		$alias = 'application.views.upload';
		$view = $this->renderPartial( $alias, array(), true );
		echo $view;
	}

}
