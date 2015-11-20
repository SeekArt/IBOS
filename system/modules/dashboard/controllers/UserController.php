<?php

/**
 * 组织架构模块用户控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块用户控制器类
 * 
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: UserController.php 4321 2014-10-09 07:42:26Z gzpjh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Attach;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\Page;
use application\core\utils\String;
use application\core\utils\PHPExcel;
use application\extensions\ExcelReader\Spreadsheet_Excel_Reader;
use application\modules\dashboard\model\Cache;
use application\modules\department\components\DepartmentCategory as ICDepartmentCategory;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\main\utils\Main;
use application\modules\position\model\PositionRelated;
use application\modules\role\model\Role;
use application\modules\role\model\RoleRelated;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User as UserUtil;

class UserController extends OrganizationBaseController {

	const IMPORT_TPL = '/data/tpl/user_import.xls';

	/*
	 * 员工上下级排列数据
	 */

	static public $userList = array();

	/**
	 *
	 * @var string 下拉列表中的<option>格式字符串 
	 */
	public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

	/**
	 * 浏览操作
	 * @return void 
	 */
	public function actionIndex() {
		$deptId = intval( Env::getRequest( 'deptid' ) );
		if ( Env::getRequest( 'op' ) == 'tree' ) {
			return $this->getDeptTree();
		}
		$type = Env::getRequest( 'type' );
		if ( !in_array( $type, array( 'enabled', 'lock', 'disabled', 'all' ) ) ) {
			$type = 'enabled';
		}

		$data = Array();
		if ( Env::submitCheck( 'search' ) && IBOS::app()->request->isPostRequest ) {
			//添加转义
			//15-7-31 下午3:10 gzdzl
			//这里存在有keyword单引号SQL错误
			$key = addslashes( $_POST['keyword'] );
			$condition = User::model()->getConditionByDeptIdType( false, $type );
			$list = User::model()->fetchAll( "( `username` LIKE '%{$key}%' OR `realname` LIKE '%{$key}%' OR `mobile` LIKE '%{$key}%' ) AND " . $condition );
		} else {
			$count = User::model()->countByDeptIdType( $deptId, $type );
			$pages = Page::create( $count );
			$list = User::model()->fetchAllByDeptIdType( $deptId, $type, $pages->getLimit(), $pages->getOffset() );
			$data['pages'] = $pages;
		}

		$data['list'] = $this->addRelatedRoleid( $list );
		$data['deptId'] = $deptId;
		$data['type'] = $type;
		$data['unit'] = IBOS::app()->setting->get( 'setting/unit' );
		$data['unit']['fullname'] = isset( $data['unit']['fullname'] ) ? $data['unit']['fullname'] : '';
		// 获取分支部门的deptid
		$deptList = Department::model()->fetchAll( 'isbranch = 1' );
		$deptArr = Convert::getSubByKey( $deptList, 'deptid' );
		$data['deptStr'] = implode( ',', $deptArr );

		$this->render( 'index', $data );
	}

	/**
	 * 新增操作
	 * @return void
	 */
	public function actionAdd() {
		if ( Env::submitCheck( 'userSubmit' ) ) {
			$origPass = filter_input( INPUT_POST, 'password', FILTER_SANITIZE_STRING );
			$_POST['salt'] = String::random( 6 );
			$_POST['password'] = !empty( $origPass ) ? md5( md5( $origPass ) . $_POST['salt'] ) : '';
			$_POST['createtime'] = TIMESTAMP;
			$_POST['guid'] = String::createGuid();
			$this->dealWithSpecialParams();
			$data = User::model()->create();
			User::model()->checkUnique( $data );
			$newId = User::model()->add( $data, true );
			if ( $newId ) {
				UserCount::model()->add( array( 'uid' => $newId ) );
				$ip = IBOS::app()->setting->get( 'clientip' );
				UserStatus::model()->add(
						array(
							'uid' => $newId,
							'regip' => $ip,
							'lastip' => $ip
						)
				);
				UserProfile::model()->add( array( 'uid' => $newId ) );
				// 辅助部门
				if ( !empty( $_POST['auxiliarydept'] ) ) {
					$deptIds = String::getId( $_POST['auxiliarydept'] );
					$this->handleAuxiliaryDept( $newId, $deptIds, $_POST['deptid'] );
				}
				// 辅助岗位
				if ( !empty( $_POST['auxiliarypos'] ) ) {
					$posIds = String::getId( $_POST['auxiliarypos'] );
					$this->handleAuxiliaryPosition( $newId, $posIds, $_POST['positionid'] );
				}
				// 辅助角色
				if ( !empty( $_POST['auxiliaryrole'] ) ) {
					$roleIds = explode( ',', $_POST['auxiliaryrole'] );
					$this->handleAuxiliaryRole( $newId, $roleIds, $_POST['roleid'] );
				}
				// 直属下属
				$subUids = String::getId( $_POST['subordinate'] );
				User::model()->updateAll( array( 'upuid' => $newId ), sprintf( "FIND_IN_SET(`uid`,'%s')", implode( ',', $subUids ) ) );
				// 重建缓存，给新加的用户生成缓存
				$newUser = User::model()->fetchByPk( $newId );
				$users = UserUtil::loadUser();
				$users[$newId] = UserUtil::wrapUserInfo( $newUser );
				User::model()->makeCache( $users );
				// 更新组织架构js调用接口
				Org::update();
				// 同步用户钩子
				Org::hookSyncUser( $newId, $origPass, 1 );
				CacheUtil::update();
				$this->success( IBOS::lang( 'Save succeed', 'message' ), $this->createUrl( 'user/index' ) );
			} else {
				$this->error( IBOS::lang( 'Add user failed' ), $this->createUrl( 'user/index' ) );
			}
		} else {
			$deptid = "";
			$manager = "";
			$account = IBOS::app()->setting->get( 'setting/account' );
			if ( $account['mixed'] ) {
				$preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
			} else {
				$preg = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
			}
			if ( $deptid = Env::getRequest( 'deptid' ) ) {
				$deptid = String::wrapId( Env::getRequest( 'deptid' ), 'd' );
				$manager = String::wrapId( Department::model()->fetchManagerByDeptid( Env::getRequest( 'deptid' ) ), 'u' );
			}
			$this->render( 'add', array(
				'deptid' => $deptid,
				'manager' => $manager,
				'passwordLength' => $account['minlength'],
				'preg' => $preg,
				'roles' => Role::model()->fetchAll()
			) );
		}
	}

	/**
	 * 
	 */
	public function actionGetavailable() {
		$limit = LICENCE_LIMIT;
		$users = UserUtil::loadUser();
		$count = count( $users );
		$this->ajaxReturn( array( 'isSuccess' => true, 'current' => $count, 'remain' => $limit - $count ) );
	}

	/**
	 * 编辑操作
	 * @return void
	 */
	public function actionEdit() {
		$op = Env::getRequest( 'op' );
		if ( $op && in_array( $op, array( 'enabled', 'disabled', 'lock' ) ) ) {
			$ids = Env::getRequest( 'uid' );
			return $this->setStatus( $op, $ids );
		}
		$uid = Env::getRequest( 'uid' );
		$user = User::model()->fetchByUid( $uid );
		if ( Env::submitCheck( 'userSubmit' ) ) {
			$this->dealWithSpecialParams();
			// 为空不修改密码
			if ( empty( $_POST['password'] ) ) {
				unset( $_POST['password'] );
			} else {
				$_POST['password'] = md5( md5( $_POST['password'] ) . $user['salt'] );
				$_POST['lastchangepass'] = TIMESTAMP;
			}
			// 辅助部门
			if ( isset( $_POST['auxiliarydept'] ) ) {
				$deptIds = String::getId( $_POST['auxiliarydept'] );
				$this->handleAuxiliaryDept( $uid, $deptIds, $_POST['deptid'] );
			}
			// 辅助岗位
			if ( isset( $_POST['auxiliarypos'] ) ) {
				$posIds = String::getId( $_POST['auxiliarypos'] );
				$this->handleAuxiliaryPosition( $uid, $posIds, $_POST['positionid'] );
			}
			// 辅助角色
			if ( isset( $_POST['auxiliaryrole'] ) ) {
				$roleIds = explode( ',', $_POST['auxiliaryrole'] );
				$this->handleAuxiliaryRole( $uid, $roleIds, $_POST['roleid'] );
			}
			$data = User::model()->create();
			User::model()->checkUnique( $data );
			User::model()->updateByUid( $uid, $data );
			// 直属下属
			User::model()->updateAll( array( 'upuid' => 0 ), "`upuid`={$uid}" ); // 先把旧的下属upuid清0
			$subUids = String::getId( $_POST['subordinate'] );
			User::model()->updateAll( array( 'upuid' => $uid ), sprintf( "FIND_IN_SET(`uid`,'%s')", implode( ',', $subUids ) ) );
			Org::update();
			CacheUtil::update();
			$this->success( IBOS::lang( 'Save succeed', 'message' ), $this->createUrl( 'user/index' ) );
		} else {
			if ( empty( $user ) ) {
				$this->error( IBOS::lang( 'Request param' ), $this->createUrl( 'user/index' ) );
			}
			$user["auxiliarydept"] = DepartmentRelated::model()->fetchAllDeptIdByUid( $user['uid'] );
			$user["auxiliarypos"] = PositionRelated::model()->fetchAllPositionIdByUid( $user['uid'] );
			$user["auxiliaryrole"] = RoleRelated::model()->fetchAllRoleIdByUid( $user['uid'] );
			$user['subordinate'] = User::model()->fetchSubUidByUid( $user['uid'] ); // 获取所有直属下属uid
			$account = IBOS::app()->setting->get( 'setting/account' );
			if ( $account['mixed'] ) {
				$preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
			} else {
				$preg = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
			}
			$param = array(
				'user' => $user,
				'passwordLength' => $account['minlength'],
				'preg' => $preg,
				'roles' => Role::model()->fetchAll()
			);
			$this->render( 'edit', $param );
		}
	}

	/**
	 * 导出操作
	 * @return void
	 */
	public function actionExport() {
		$uid = urldecode( Env::getRequest( 'uid' ) );
		return UserUtil::exportUser( explode( ',', trim( $uid, ',' ) ) );
	}

	/**
	 * 导入用户一系列操作入口
	 */
	public function actionImport() {
		$op = Env::getRequest( 'op' );
		if ( in_array( $op, array( 'downloadTpl', 'import', 'downError' ) ) ) {
			$this->$op();
		}
	}

	/**
	 * 用户上下级关系
	 */
	public function actionRelation() {
		$users = UserUtil::loadUser();
		$upUsers = array(); // 最顶级人员(没上司的人)
		foreach ( $users as $user ) {
			$subordinate = User::model()->fetchSubUidByUid( $user['uid'] );
			if ( $user['upuid'] == 0 && empty( $subordinate ) ) {
				$upUsers[] = array(
					'uid' => $user['uid'],
					'name' => $user['realname'],
					'position' => $user['posname']
				);
			}
		}
		$param = array(
			'upUsers' => $upUsers,
			'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'dashboard' )
		);
		$op = Env::getRequest( 'op' );
		if ( in_array( $op, array( 'getUsers', 'setUpuid' ) ) ) {
			$this->$op();
		} else {
			$alias = "application.modules.dashboard.views.user.relation";
			$html = $this->renderPartial( $alias, $param, true );
			$this->ajaxReturn( array( 'isSuccess' => true, 'html' => $html ) );
		}
	}

	/**
	 * 获取上下级关系用户数据
	 */
	protected function getUsers() {
		$users = UserUtil::loadUser();
		$res = array();
		foreach ( $users as $user ) {
			$subordinate = User::model()->fetchSubUidByUid( $user['uid'] );
			if ( $user['upuid'] != 0 || !empty( $subordinate ) ) {
				$res[] = array(
					'id' => $user['uid'],
					'uid' => $user['uid'],
					'name' => $user['realname'],
					'pid' => $user['upuid'],
					'pId' => $user['upuid']
				);
			}
		}
		$this->ajaxReturn( $res );
	}

	/**
	 * 移动上下级关系
	 */
	protected function setUpuid() {
		$uid = Env::getRequest( 'id' );
		$upuid = Env::getRequest( 'pid' );
		if ( !empty( $uid ) ) {
			User::model()->modify( $uid, array( 'upuid' => $upuid ) );
			Org::update();
			CacheUtil::update();
		}
		$this->ajaxReturn( array( 'isSuccess' => true ) );
	}

	/**
	 * 下载模板文件
	 */
	protected function downloadTpl() {
		$file = PATH_ROOT . self::IMPORT_TPL;
		$fileName = iconv( 'utf-8', 'gbk', '用户导入数据.' . pathinfo( $file, PATHINFO_EXTENSION ) );
		if ( is_file( $file ) ) {
			header( "Content-Type: application/force-download" );
			header( "Content-Disposition: attachment; filename=" . $fileName );
			readfile( $file );
			exit;
		} else {
			$this->error( "抱歉，找不到模板文件！" );
		}
	}

	/**
	 * 导入操作
	 */
	protected function import() {
		Cache::model()->deleteAll( "`cachekey` = 'userimportfail'" );
		set_time_limit( 0 ); //避免php脚本超时
		$attachId = intval( Env::getRequest( 'aid' ) );
		$attachs = Attach::getAttachData( $attachId, false );
		$attach = array_shift( $attachs ); // 附件
		$file = File::getAttachUrl() . '/' . $attach['attachment'];
		$data = PHPExcel::excelToArray( $file );
		$err = array();
		$successCount = 0;
		if ( !empty( $data ) && is_array( $data ) ) {
			$count = count( $data );

			$users = UserUtil::loadUser();
			$allUsers = User::model()->fetchAllSortByPk( 'uid' ); // 全部用户，包括锁定、禁用等
			$convert = array();
			foreach ( $allUsers as $user ) {
				$convert['username'][] = $user['username']; // 已存在的用户名
				$convert['mobile'][] = $user['mobile']; // 已存在的手机号
				$convert['email'][] = $user['email']; // 已存在的邮箱
				$convert['jobnumber'][] = $user['jobnumber']; // 已存在的工号
			}
			// 邮件格式
			$ip = IBOS::app()->setting->get( 'clientip' );
			foreach ( $data as $k => $row ) {
				//以下数组下标跟导入表的每一列位置对应，如导入出现问题，请检查位置与格式！
				$salt = String::random( 6 );
				$origPass = !empty( $row[3] ) ? $row[3] : '123456'; //默认密码为123456
				$data = array(
					'salt' => $salt,
					'username' => !empty( $row[0] ) ? trim( $row[0] ) : '', // 用户名
					'password' => !empty( $origPass ) ? md5( md5( trim( $origPass ) ) . $salt ) : '', // 密码
					'realname' => !empty( $row[2] ) ? trim( $row[2] ) : '', // 真实姓名
					'gender' => !empty( $row[4] ) && trim( $row[4] ) == '女' ? 0 : 1, // 性别
					'mobile' => !empty( $row[6] ) ? trim( $row[6] ) : '', // 手机
					'email' => !empty( $row[7] ) ? trim( $row[7] ) : '', // 邮箱
					'weixin' => !empty( $row[5] ) ? trim( $row[5] ) : '', // 微信
					'jobnumber' => !empty( $row[1] ) ? trim( $row[1] ) : '', // 工号
				);
				$result = self::checkUserData( $data, $convert );
				if ( !empty( $result ) ) {
					$err[$k]['reason'] = $result;
				}
				if ( isset( $err[$k]['reason'] ) ) {
					$err[$k]['username'] = $data['username'];
					$err[$k]['realname'] = $data['realname'];
				} else {
					$newId = User::model()->add( $data, true );
					UserCount::model()->add( array( 'uid' => $newId ) );
					UserStatus::model()->add(
							array(
								'uid' => $newId,
								'regip' => $ip,
								'lastip' => $ip
							)
					);
					$profile_data = array(
						'uid' => $newId,
						'birthday' => !empty( $row[8] ) ? ( $row[8] != 0 ? strtotime( $row[8] ) : 0 ) : 0,
						'telephone' => !empty( $row[9] ) ? $row[9] : '',
						'address' => !empty( $row[10] ) ? $row[10] : '',
						'qq' => !empty( $row[11] ) ? $row[11] : '',
						'bio' => !empty( $row[12] ) ? $row[12] : '',
					);
					//往user_profile添加相关数据，即使为空，要不然会报错
					UserProfile::model()->add( $profile_data );
					$newUser = User::model()->fetchByPk( $newId );
					$users[$newId] = UserUtil::wrapUserInfo( $newUser );
					// 同步用户钩子
					Org::hookSyncUser( $newId, $origPass, 1 );
					$successCount++;
				}
			}
			if ( $successCount > 0 ) {
				User::model()->makeCache( $users );
				// 更新组织架构js调用接口
				Org::update();
				CacheUtil::update();
			}
			if ( !empty( $err ) ) {
				Cache::model()->add( array( 'cachekey' => 'userimportfail', 'cachevalue' => serialize( $err ) ) );
			}
			@unlink( $file ); // 删除文件
			$this->ajaxReturn( array( 'isSuccess' => true, 'successCount' => $successCount, 'errorCount' => count( $err ), 'url' => $this->createUrl( 'user/import', array( 'op' => 'downError' ) ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => true, 'successCount' => 0, 'errorCount' => 0, 'url' => '' ) );
		}
	}

	/**
	 * 检查提交用户数据是否完整、或者有冲突
	 * @param type $data 需要检查的数组
	 * @param type $convert 检查标准数组
	 * @return string 返回错误信息
	 */
	protected static function checkUserData( $data, $convert ) {
		// 邮件格式匹配正则
		$emailPreg = "/^[_.0-9a-z-a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,4}$/";
		$err = '';
		if ( empty( $data['username'] ) || empty( $data['password'] ) || empty( $data['realname'] ) || empty( $data['mobile'] ) || empty( $data['email'] ) ) {
			$err = '用户名、密码、真实姓名、手机、邮箱不能为空';
		} else if ( in_array( $data['username'], $convert['username'] ) ) {
			$err = $data['username'] . '用户名已存在';
		} else if ( in_array( $data['mobile'], $convert['mobile'] ) ) {
			$err = $data['mobile'] . '手机号码已存在';
		} else if ( in_array( $data['email'], $convert['email'] ) ) {
			$err = $data['email'] . '邮箱已存在';
		} else if ( !empty( $data['jobnumber'] ) && in_array( $data['jobnumber'], $convert['jobnumber'] ) ) {
			$err = $data['jobnumber'] . '工号已存在';
		} else if ( !preg_match( $emailPreg, $data['email'] ) ) {
			$err = $data['email'] . '邮件格式错误';
		}
		return $err;
	}

	/**
	 * 下载导入错误文件
	 * 导出CSV格式
	 */
//	protected function downError() {
//		$error = Cache::model()->fetchArrayByPk( 'userimportfail' );
//		Cache::model()->delete( "`cachekey` = 'userimportfail'" );
//		$fieldArr = array(
//			IBOS::lang( 'Line' ),
//			IBOS::lang( 'Username' ),
//			IBOS::lang( 'Realname' ),
//			IBOS::lang( 'Error reason' ),
//		);
//		$str = implode( ',', $fieldArr ) . "\n";
//		foreach ( $error as $line => $row ) {
//			$param = array( $line, $row['username'], $row['realname'], $row['reason'] );
//			$str .= implode( ',', $param ) . "\n"; //用引文逗号分开 
//		}
//		$outputStr = iconv( 'utf-8', 'gbk//ignore', $str );
//		$filename = IBOS::lang( 'Import error record' ) . '.csv';
//		File::exportCsv( $filename, $outputStr );
//	}
	/**
	 * 下载导入用户错误文件
	 * 导出Excel格式
	 */
	protected function downError() {
		$error = Cache::model()->fetchArrayByPk( 'userimportfail' );
		Cache::model()->delete( "`cachekey` = 'userimportfail'" );
		$return = array();
		foreach ( $error as $key => $row ) {
			$return[$key]['line'] = $key;
			$return[$key]['username'] = $row['username'];
			$return[$key]['realname'] = $row['realname'];
			$return[$key]['reason'] = $row['reason'];
		}
		$filename = $filename = date( 'Y-m-d' ) . '用户导入错误信息.xls';
		$fieldArr = array(
			IBOS::lang( 'Line' ),
			IBOS::lang( 'Username' ),
			IBOS::lang( 'Realname' ),
			IBOS::lang( 'Error reason' ),
		);
		PHPExcel::exportToExcel( $filename, $fieldArr, $return );
	}

	/**
	 * 编辑动作: 设置用户状态
	 * @param string $status 状态标识
	 * @param string $uids 用户id
	 * @return void
	 */
	protected function setStatus( $status, $uids ) {
		$uidArr = explode( ',', trim( $uids, ',' ) );
		$attributes = array();
		switch ( $status ) {
			case 'lock':
				$attributes['status'] = 1;
				break;
			case 'disabled':
				$attributes['status'] = 2;
				Org::hookSyncUser( $uids, '', 0 );
				break;
			case 'enabled':
			default:
				$attributes['status'] = 0;
				Org::hookSyncUser( $uids, '', 2 );
				break;
		}
		$return = User::model()->updateByUids( $uidArr, $attributes );
		Org::update();
		CacheUtil::update( 'users' );
		return $this->ajaxReturn( array( 'isSuccess' => !!$return ), 'json' );
	}

	/**
	 * 辅助部门插入数据处理
	 * @param integer $uid 用户ID
	 * @param array $deptIds 辅助部门ID
	 * @param string $except 主部门id
	 */
	protected function handleAuxiliaryDept( $uid, $deptIds, $except = '' ) {
		DepartmentRelated::model()->deleteAll( '`uid` = :uid', array( ':uid' => $uid ) );
		foreach ( $deptIds as $deptId ) {
			if ( strcmp( $deptId, $except ) !== 0 ) {
				DepartmentRelated::model()->add( array( 'uid' => $uid, 'deptid' => $deptId ) );
			}
		}
	}

	/**
	 * 辅助岗位插入数据处理
	 * @param integer $uid 用户ID
	 * @param array $posIds
	 * @param string $except 主岗位ID
	 */
	protected function handleAuxiliaryPosition( $uid, $posIds, $except = '' ) {
		PositionRelated::model()->deleteAll( '`uid` = :uid', array( ':uid' => $uid ) );
		foreach ( $posIds as $posId ) {
			if ( strcmp( $posId, $except ) !== 0 ) {
				PositionRelated::model()->add( array( 'uid' => $uid, 'positionid' => $posId ) );
			}
		}
	}

	/**
	 * 辅助角色插入数据处理
	 * @param integer $uid 用户ID
	 * @param array $roleIds 副角色ids
	 * @param string $except 主角色ID
	 */
	protected function handleAuxiliaryRole( $uid, $roleIds, $except = '' ) {
		RoleRelated::model()->deleteAll( '`uid` = :uid', array( ':uid' => $uid ) );
		foreach ( $roleIds as $roleId ) {
			if ( strcmp( $roleId, $except ) != 0 && !empty( $roleId ) ) {
				RoleRelated::model()->add( array( 'uid' => $uid, 'roleid' => $roleId ) );
			}
		}
	}

	/**
	 * 特别参数再处理
	 */
	protected function dealWithSpecialParams() {
		$_POST['upuid'] = implode( ',', String::getUid( $_POST['upuid'] ) );
		$_POST['deptid'] = implode( ',', String::getId( $_POST['deptid'] ) );
		$_POST['positionid'] = implode( ',', String::getId( $_POST['positionid'] ) );
	}

	/**
	 * 获取左侧分类树
	 */
	protected function getDeptTree() {
		$component = new ICDepartmentCategory( 'application\modules\department\model\Department', '', array( 'index' => 'deptid', 'name' => 'deptname' ) );
		$this->ajaxReturn( $component->getAjaxCategory( $component->getData() ), 'json' );
	}

	/**
	 * 用formValidator异步检查数据是否已被注册
	 */
	public function actionIsRegistered() {
		//$fieldName获取要检查的字段名
		$fieldName = Env::getRequest( 'clientid' );
		//$fieldValue获取此字段用户输入的值
		$fieldValue = Env::getRequest( $fieldName );
		//如果有传递uid，是用户编辑资料，没有uid，是新注册资料
		$uid = Env::getRequest( 'uid' );
		if ( $uid ) {
			$userInfo = User::model()->findByPk( $uid );
			$fieldExists = User::model()->fetch( "$fieldName = '{$fieldValue}' and $fieldName != '{$userInfo[$fieldName]}'" );
		} else {
			if ( $fieldValue == '' || $fieldValue == null ) {
				//若用户输入为空，则判断通过
				return $this->ajaxReturn( array( 'isSuccess' => true ), 'json' );
			} else {
				//查找数据库的$fieldName字段是否有$fieldValue这个值
				$fieldExists = User::model()->find( "$fieldName = :getValue", array( ":getValue" => $fieldValue ) );
			}
		}
		//有数据则表示已经注册，返回true，没数据表示没注册，返回false
		$isRegistered = $fieldExists ? true : false;
		return $this->ajaxReturn( array( 'isSuccess' => !$isRegistered ), 'json' );
	}

	/**
	 * 添加辅助角色信息
	 * @param array $list 数据列表
	 * @return array
	 */
	protected function addRelatedRoleid( $list ) {
		$relatedRoleid = array();
		$uids = Convert::getSubByKey( $list, 'uid' );
		foreach ( $uids as $uid ) {
			$relatedRoleid[$uid] = implode( ',', RoleRelated::model()->fetchAllRoleIdByUid( $uid ) );
		}
		foreach ( $list as $key => $value ) {
			$list[$key]['relatedRoleid'] = $relatedRoleid[$value['uid']];
		}
		return $list;
	}

}
