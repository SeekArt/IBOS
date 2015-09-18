<?php

/**
 * 酷办公同步用户以及组织架构控制器
 * CosyncController.class.file
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 * @package application.modules.dashboard.controllers
 * @author Sam <gzxgs@ibos.com.cn>
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Api;
use application\core\utils\String;
use application\core\utils\Convert;
use application\core\utils\Cache as CacheUtil;
use application\modules\dashboard\model\Cache;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\model\UserCount;
use application\modules\user\utils\User as UserUtil;
use application\modules\department\utils\Department;
use application\modules\department\model\Department as DepartmentModel;
use CJSON;
use Yii;

class CosyncController extends CoController {

	protected $aeskey;
	protected $oaUrl;

	public function init() {
		parent::init();
		$this->aeskey = Yii::app()->setting->get( 'aeskey' );
		$this->oaUrl = rtrim( Yii::app()->setting->get( 'siteurl' ), '/' );
	}

	/**
	 * 同步首页视图
	 */
	public function actionIndex() {
		//检查是否绑定酷办公，如果没有绑定酷办公，则显示提示绑定
		if ( $this->isBinding === false ) {
			$this->render( 'unbindco' );
			exit();
		}
		$coUsers = $this->handleCoData( $op = 'getUser' );  //获取酷办公用户数据
		$ibosUsers = UserUtil::loadUser(); //获取IBOS所有用户信息
		$result = self::getLikeUsers( $ibosUsers, $coUsers );
		$ibosUnsync = $result['ibosUsers'];
		$coUnsync = $result['coUsers'];
		$params = array(
			'ibosUnsyncCount' => count( $ibosUnsync ),
			'ibosUnsync' => $ibosUnsync,
			'coUnsync' => $coUnsync,
			'coUnsyncCount' => count( $coUnsync ),
		);
		$this->render( 'index', $params );
	}

	/**
	 * 同步操作
	 */
	public function actionSync() {
		set_time_limit( 120 );
		$op = Env::getRequest( 'op' );
		if ( $op == 'init' ) {
			Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'codepts,codeptrelated,cosendinvite,codatum,cousers,couserfail,cousersuccess,cototal')" );
			$sendInvite = Env::getRequest( 'sendinvite' ); //是否发送邮件提醒
			$datum = Env::getRequest( 'datum' ); //以哪个组织架构为准
			$ibosUsers = UserUtil::loadUser();
			//获取酷办公人员信息
			$coUsers = $this->handleCoData( $op = 'getUser' );
			$res = self::getLikeUsers( $ibosUsers, $coUsers );
			$coUsers = $res['coUsers'];
			if ( $datum == 0 ) { //以IBOS组织架构为准
				//获取IBOS组织架构，向酷办公发送创建部门请求
				$allDepts = Department::loadDepartment();
				$this->handleCoData( $op = 'createDepartment', CJSON::encode( $allDepts ) );
				$data = array(
					'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'user' ) ),
					'isSuccess' => true,
					'msg' => '开始同步用户，请耐心等候...',
					'deptCount' => count( $allDepts ),
					'userCount' => count( $coUsers )
				);
			} else if ( $datum == 1 ) { //以酷办公组织架构为准
				//获取酷办公组织架构所有数据，执行同步部门操作
				$coDepts = $this->handleCoData( $op = 'getDepartment' );
				//删除原有组织架构所有数据，重新建立
				DepartmentModel::model()->deleteAll();
				$data = array(
					'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'dept' ) ),
					'isSuccess' => true,
					'msg' => '开始同步部门，请耐心等候...',
					'deptCount' => count( $coDepts ),
					'userCount' => count( $coUsers )
				);
				Cache::model()->add( array( 'cachekey' => 'codepts', 'cachevalue' => serialize( $coDepts ) ) );
			}
			//调用接口，向酷办公发送同步用户请求
			$this->handleCoData( $op = 'createUser', CJSON::encode( $ibosUsers ), $sendInvite, $datum );

			Cache::model()->add( array( 'cachekey' => 'cosendinvite', 'cachevalue' => serialize( $sendInvite ) ) ); //发送企业邀请
			Cache::model()->add( array( 'cachekey' => 'codatum', 'cachevalue' => serialize( $datum ) ) );
			Cache::model()->add( array( 'cachekey' => 'cousers', 'cachevalue' => serialize( $coUsers ) ) );
			Cache::model()->add( array( 'cachekey' => 'cototal', 'cachevalue' => serialize( $coUsers ) ) );
			Cache::model()->add( array( 'cachekey' => 'codeptrelated', 'cachevalue' => serialize( array() ) ) );
			Cache::model()->add( array( 'cachekey' => 'couserfail', 'cachevalue' => serialize( array() ) ) );
			Cache::model()->add( array( 'cachekey' => 'cousersuccess', 'cachevalue' => serialize( array() ) ) ); // 成功同步的用户
			$this->ajaxReturn( $data );
		} else {
			$count = 0;
			if ( $op == 'dept' ) {
				$depts = Cache::model()->fetchArrayByPk( 'codepts' );
				if ( empty( $depts ) ) {
					//更新部门缓存
					CacheUtil::update( array( 'department' ) );
					$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '同步部门完成。开始处理用户同步,请稍后...', 'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'user' ) ) ) );
				}
				$related = Cache::model()->fetchArrayByPk( 'codeptrelated' );
				foreach ( $depts as $key => $value ) {
					//创建部门
					$newId = DepartmentModel::model()->add( $value );
					if ( $newId ) {
						$related[$value['deptid']] = $newId;
						$count++;
					}
					unset( $depts[$key] );
					break;
				}
				if ( $count ) {
					Cache::model()->updateByPk( 'codepts', array( 'cachevalue' => serialize( $depts ) ) );
					Cache::model()->updateByPk( 'codeptrelated', array( 'cachevalue' => serialize( $related ) ) );
					$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '正在同步部门，请稍后..', 'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'dept' ) ) ) );
				}
			} else if ( $op == 'user' ) {
				$related = Cache::model()->fetchArrayByPk( 'codeptrelated' );
				$error = Cache::model()->fetchArrayByPk( 'couserfail' );
				$success = Cache::model()->fetchArrayByPk( 'cousersuccess' ); // 同步成功
				$total = count( Cache::model()->fetchArrayByPk( 'cototal' ) );
				$sendInvite = Cache::model()->fetchArrayByPk( 'cosendinvite' );
				$datum = Cache::model()->fetchArrayByPk( 'codatum' );

				if ( Env::getRequest( 'act' ) == 'reset' ) {
					$userdata = User::model()->fetchAllByUids( array_keys( $error ) );
				} else {
					$userdata = Cache::model()->fetchArrayByPk( 'cousers' );
				}
				if ( empty( $userdata ) ) {
					$downloadlink = $this->createUrl( 'cosync/downerror' );
					$errorCount = count( $error );
					$successCount = intval( $total - $errorCount );
					if ( $errorCount == $total and $total != 0 ) {
						$this->ajaxReturn( array( 'errorCount' => $errorCount, 'tpl' => 'error', 'msg' => $errorCount . '个联系人无法同步，请根据错误信息修正后，点击重新同步。 ', 'downUrl' => $downloadlink, 'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'user', 'act' => 'reset' ) ) ) );
					} else if ( $errorCount > 0 ) {
						$this->ajaxReturn( array( 'successCount' => $successCount, 'errorCount' => $errorCount, 'tpl' => 'half', 'msg' => $errorCount . '个联系人无法同步，请根据错误信息修正后，点击重新同步。 ', 'downUrl' => $downloadlink, 'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'user', 'act' => 'reset' ) ) ) );
					} else {
						Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'codepts,codeptrelated')" );
						$this->ajaxReturn( array( 'successCount' => $successCount, 'tpl' => 'success', 'isSuccess' => true, 'msg' => '成功全部完成！' ) );
						exit();
					}
				}
				//此处加载缓存以及更新用户缓存必须，要不然会出错或者会产生重复数据。
				CacheUtil::load( 'usergroup' ); // 要注意小写
				CacheUtil::update( 'users' ); // 用户缓存依赖usergroup缓存，单独更新
				foreach ( $userdata as $key => $param ) {
					$checkIsExist = User::model()->checkIsExistByMobile( $param['mobile'] );
					//判断手机号不存在,执行创建用户
					//从酷办公获取用户在本地创建新的用户，暂时是没有部门关联的
					if ( $checkIsExist === false ) {
						$param['salt'] = !empty( $param['salt'] ) ? $param['salt'] : String::random( 6 );
						$param['password'] = !empty( $param['password'] ) ? $param['password'] : md5( md5( $param['mobile'] ) . $param['salt'] );
						$param['groupid'] = !empty( $param['groupid'] ) ? $param['groupid'] : '2';
						$param['createtime'] = TIMESTAMP;
						$param['guid'] = String::createGuid();
						$data = User::model()->create( $param );
						unset( $data['uid'] );
						$newId = User::model()->add( $data, true );
						if ( $newId ) {
							UserCount::model()->add( array( 'uid' => $newId ) );
							$ip = Ibos::app()->setting->get( 'clientip' );
							UserStatus::model()->add( array( 'uid' => $newId, 'regip' => $ip, 'lastip' => $ip ) );
							//用户user_profile一定要有相关的用户数据，即使为空，要不然会出错
							UserProfile::model()->add( array( 'uid' => $newId ) );
							//创建用户绑定
							$binding = UserBinding::model()->add( array( 'uid' => $newId, 'bindvalue' => $param['guid'], 'app' => 'co' ) );
							if ( !$binding ) {
								$error[$key]['uid'] = $param['uid'];
								$error[$key]['realname'] = $param['realname'];
								$error[$key]['mobile'] = $param['mobile'];
								$error[$key]['errormsg'] = '绑定用户出错';
							}
							$success[$param['uid']] = $param['mobile'];
							/**
							 * 重新建立缓存
							 */
							$newUser = User::model()->fetchByPk( $newId );
							$newusers = UserUtil::loadUser();
							$newusers[$newId] = UserUtil::wrapUserInfo( $newUser );
							User::model()->makeCache( $newusers );
						} else {
							$error[$key]['uid'] = $param['uid'];
							$error[$key]['realname'] = $param['realname'];
							$error[$key]['mobile'] = $param['mobile'];
							$error[$key]['errormsg'] = '创建用户出错';
						}
					}
					unset( $userdata[$key] );
					break;
				}
				Cache::model()->updateByPk( 'couserfail', array( 'cachevalue' => serialize( $error ) ) );
				Cache::model()->updateByPk( 'cousers', array( 'cachevalue' => serialize( $userdata ) ) );
				Cache::model()->updateByPk( 'cousersuccess', array( 'cachevalue' => serialize( $success ) ) ); //成功同步用户
				$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '正在处理用户同步，请稍后...', 'url' => $this->createUrl( 'cosync/sync', array( 'op' => 'user' ) ) ) );
			}
		}
	}

	/**
	 * 调用接口，获取酷办公数据
	 * 外添加了 $datum 参数 ,代表是否发送企业邀请
	 */
	private function handleCoData( $op, $data = array(), $sendInvite = 1, $datum = 0 ) {
		$signature = self::getSignature( $this->aeskey, $this->oaUrl );
		$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
		$param = array(
			'code' => $unit['corpcode'],
			'url' => urlencode( $this->oaUrl ),
			'signature' => $signature,
			'op' => $op,
			'send' => $sendInvite,
			'datum' => $datum
		);
		$api = Api::getInstance();
		$url = $api->buildUrl( CoApi:: CO_URL . 'api/ibospublic', $param );
		$res = $api->fetchResult( $url, $data, 'post' );
		if ( is_array( $res ) ) {
			return array();
		}
		$return = CJSON::decode( $res, true );
		if ( $return['isSuccess'] === false ) {
			//如果请求接口返回false，为了容错处理，默认返回空数组
			return array();
		}
		return $return['data'];
	}

	/**
	 * 生成签名
	 * @param type $aeskey
	 * @param type $oaUrl
	 * @return type
	 */
	private static function getSignature( $aeskey, $oaUrl ) {
		$signature = md5( $aeskey . $oaUrl );
		return $signature;
	}

	/**
	 * 获取所有未同步人员
	 * 规则：手机号匹配
	 * @param array $allUsers ibos的所有的用户
	 * @param array $coUsers 酷办公所有的用户
	 * @return array
	 */
	private static function getLikeUsers( $ibosUsers, $coUsers ) {
		foreach ( $ibosUsers as $key => $value ) {
			foreach ( $coUsers as $k => $v ) {
				$isBindingMobile = isset( $v['mobile'] ) ? ($v['mobile'] == $value['mobile']) : false;
				if ( $isBindingMobile ) {
					$res = UserBinding::model()->find( sprintf( "`uid` = '%s' AND `bindvalue` = '%s' AND `app` = 'co'", $value['uid'], $v['guid'] ) );
					if ( empty( $res ) ) {
						UserBinding::model()->deleteAll( sprintf( "`uid` = %d AND `app` = 'co'", $value['uid'] ) );
						UserBinding::model()->add( array( 'uid' => $value['uid'], 'bindvalue' => $v['guid'], 'app' => 'co' ) );
					}
					unset( $ibosUsers[$key] );
					unset( $coUsers[$k] );
				}
			}
		}
		return array(
			'ibosUsers' => $ibosUsers,
			'coUsers' => $coUsers,
		);
	}

	/**
	 * 下载同步用户出错信息文件
	 */
	public function downError() {
		$error = Cache::model()->fetchArrayByPk( 'couserfail' );
		$header = array( 'uid', '真实姓名', '手机号', '同步错误原因' );
		$body = array();
		foreach ( $error as $value ) {
			$body[] = array( $value['uid'], iconv( 'utf-8', 'gbk', $value['realname'] ), $value['mobile'], iconv( 'utf-8', 'gbk', $value['errormsg'] ) );
		}
		Convert::exportCsv( '同步用户错误记录' . NOW, $header, $body );
	}

}
