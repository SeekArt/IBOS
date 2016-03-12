<?php

/**
 * WxBindingController.class.file
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 微信企业号设置控制器
 * 
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: WxBindingController.php 2052 2014-09-22 10:05:11Z gzhzh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\WebSite;
use application\modules\dashboard\model\Cache;
use application\modules\dashboard\utils\Wx;
use application\modules\department\utils\Department;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\utils\User as UserUtil;

class WxsyncController extends WxController {

	/**
	 * 获取企业号绑定视图
	 */
	public function actionIndex() {
		if ( $this->isBinding === false ) {
			$this->render( 'unbindtip', array( 'msg' => $this->msg ) );
			exit();
		}
		$oaUsers = UserUtil::loadUser();
		//获取已经同步的人员
		$wxUsers = $this->getDeptUser();
		$res = $this->getLikeUsers( $oaUsers, $wxUsers );
		$oaUnbind = $res['allUsers'];
		$wxUnbind = $res['wxUsers'];
		$params = array(
			'bindingCount' => count( $oaUsers ) - count( $oaUnbind ),
			'oaUnbind' => $oaUnbind,
			'wxUnbind' => $wxUnbind
		);
		$this->render( 'index', $params );
	}

	public function actionSync() {
		set_time_limit( 120 );
		$op = Env::getRequest( 'op' );
		if ( $op == 'init' ) {
			Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'depts,deptrelated,sendmail,users,userfail,usersuccess,total')" );
			$sendMail = Env::getRequest( 'status' );
			$allDepts = Department::loadDepartment();

			$oaUsers = UserUtil::loadUser();
			//获取已经同步的人员
			$wxUsers = $this->getDeptUser();
			$res = $this->getLikeUsers( $oaUsers, $wxUsers );
			$allUsers = $res['allUsers'];
			Cache::model()->add( array( 'cachekey' => 'sendmail', 'cachevalue' => $sendMail ) );
			Cache::model()->add( array( 'cachekey' => 'users', 'cachevalue' => serialize( $allUsers ) ) );
			Cache::model()->add( array( 'cachekey' => 'total', 'cachevalue' => serialize( $allUsers ) ) );
			Cache::model()->add( array( 'cachekey' => 'depts', 'cachevalue' => serialize( $allDepts ) ) );
			Cache::model()->add( array( 'cachekey' => 'deptrelated', 'cachevalue' => serialize( array() ) ) );
			Cache::model()->add( array( 'cachekey' => 'userfail', 'cachevalue' => serialize( array() ) ) );
			Cache::model()->add( array( 'cachekey' => 'usersuccess', 'cachevalue' => serialize( array() ) ) ); // 成功同步的用户，记录邮箱，用以邮件通知
			$data = array(
				'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'dept' ) ),
				'isSuccess' => true,
				'msg' => '开始同步部门，请耐心等候...',
				'deptCount' => count( $allDepts ),
				'userCount' => count( $allUsers )
			);
			$this->ajaxReturn( $data );
		} else {
			$count = 0;
			if ( $op == 'dept' ) {
				$depts = Cache::model()->fetchArrayByPk( 'depts' );
				if ( empty( $depts ) ) {
					$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '同步部门完成。开始处理用户,请稍后..', 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'user' ) ) ) );
				}
				$related = Cache::model()->fetchArrayByPk( 'deptrelated' );
				$url = $this->createUrlByType( 'syncDept' );
				foreach ( $depts as $key => $value ) {
					$deptName = $value['deptname'];
					$rest = count( $depts );
					if ( $value['pid'] == 0 || isset( $related[$value['pid']] ) ) {
						//todo::如果选择的部门不是顶级的，就不能设置成1了
						$pid = $value['pid'] == 0 ? 1 : $related[$value['pid']];
						$res = WxApi::getInstance()->createDept( $value['deptname'], $pid, $value['sort'], $url );
                        $newId = 0;
                        if ( $res['isSuccess'] && isset( $res['data']['id'] ) ) {
							$newId = $res['data']['id'];
                        } else if ( !$res['isSuccess'] ) {
							$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => '部门同步失败，错误代码：' . $res['data']['errcode'] . '，错误原因：' . Code::getErrmsg( $res['data']['errcode'] ) ) );
						}
                        if ( $newId >= 0 ) {
							$related[$value['deptid']] = $newId;
							$count++;
						}
						unset( $depts[$key] );
						break;
					} else {
						continue;
					}
				}
				if ( $count ) {
					Cache::model()->updateByPk( 'depts', array( 'cachevalue' => serialize( $depts ) ) );
					Cache::model()->updateByPk( 'deptrelated', array( 'cachevalue' => serialize( $related ) ) );
					$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '正在同步部门【' . $deptName . '】，还剩下' . $rest . '个 ，请稍后..', 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'dept' ) ) ) );
				}
			} else if ( $op == 'user' ) {
				$related = Cache::model()->fetchArrayByPk( 'deptrelated' );
				$error = Cache::model()->fetchArrayByPk( 'userfail' );
				$success = Cache::model()->fetchArrayByPk( 'usersuccess' ); // 成功绑定的用户邮箱，用以发邮件通知
				$total = count( Cache::model()->fetchArrayByPk( 'total' ) );

				if ( Env::getRequest( 'act' ) == 'reset' ) {
					$users = User::model()->fetchAllByUids( array_keys( $error ) );
				} else {
					$users = Cache::model()->fetchArrayByPk( 'users' );
				}
				if ( empty( $users ) ) {
					$downloadlink = $this->createUrl( 'wxsync/downerror' );
					$errorCount = count( $error );
					$successCount = intval( $total - $errorCount );
					if ( $errorCount == $total and $total != 0 ) {
						$this->ajaxReturn( array( 'errorCount' => $errorCount, 'tpl' => 'error', 'msg' => $errorCount . '个联系人无法同步，请根据错误信息修正后，点击重新同步。 ', 'downUrl' => $downloadlink, 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'user', 'act' => 'reset' ) ) ) );
					} else if ( $errorCount > 0 ) {
						$this->ajaxReturn( array( 'successCount' => $successCount, 'errorCount' => $errorCount, 'tpl' => 'half', 'msg' => count( $error ) . '个联系人无法同步，请根据错误信息修正后，点击重新同步。 ', 'downUrl' => $downloadlink, 'url' => $this->createUrl( 'Wxbinding/sync', array( 'op' => 'user', 'act' => 'reset' ) ) ) );
					} else {
						Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'depts,deptrelated')" );

						//发送关注邀请
						$send = Cache::model()->fetchByPk( 'sendmail' ); // 是否发送邮件通知
						if ( $send['cachevalue'] == 1 && !empty( $success ) && $successCount > 0 ) {
							foreach ( $success as $k => $email ) {
								if ( empty( $email ) ) {
									unset( $success[$k] );
								}
							}
							Cache::model()->updateByPk( 'usersuccess', array( 'cachevalue' => serialize( $success ) ) );
							$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '同步人员完成。开始邀请,请稍后..', 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'sending' ) ) ) );
						} else {
							$this->ajaxReturn( array( 'successCount' => $successCount, 'tpl' => 'success', 'isSuccess' => true, 'msg' => '成功全部完成！' ) );
						}
						exit();
					}
				}
				foreach ( $users as $user ) {
					$deptIds = array();
					foreach ( explode( ',', $user['alldeptid'] ) as $deptId ) {
						if ( isset( $related[$deptId] ) ) {
							$deptIds[] = $related[$deptId];
						}
					}
					$user['deptid'] = implode( ',', $deptIds );
					$user['userid'] = $user['mobile'];
					$user['gender'] = $user['gender'] == 1 ? 0 : 1;

					//创建链接
					$url = $this->createUrlByType( 'syncUser' );
					$res = WxApi::getInstance()->createUser( $user, $url );

					unset( $users[$user['uid']] );
					if ( $res !== '' ) {
						$error[$user['uid']] = $res;
					} else {
						$record = UserBinding::model()->fetch( sprintf( "`uid`=%d AND `app`='%s'", $user['uid'], 'wxqy' ) );
						if ( empty( $record ) ) {
							UserBinding::model()->add( array( 'uid' => $user['uid'], 'bindvalue' => $user['userid'], 'app' => 'wxqy' ) );
							$success[$user['uid']] = $user['mobile'];
						}
					}
					break;
				}
				Cache::model()->updateByPk( 'userfail', array( 'cachevalue' => serialize( $error ) ) );
				Cache::model()->updateByPk( 'users', array( 'cachevalue' => serialize( $users ) ) );
				Cache::model()->updateByPk( 'usersuccess', array( 'cachevalue' => serialize( $success ) ) );
				$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '正在处理用户，请稍后...', 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'user' ) ) ) );
			} else if ( $op == 'sending' ) {
				$success = Cache::model()->fetchArrayByPk( 'usersuccess' ); // 成功绑定的用户邮箱，用以发邮件通知
				if ( empty( $success ) ) {
					$this->ajaxReturn( array( 'tpl' => 'sending', 'isSuccess' => true, 'msg' => '成功全部完成！' ) );
				}
				$userid = array_shift( $success );
				$url = $this->createUrlByType( 'sendInvition' );
				$res = WxApi::getInstance()->sendInvition( $url, $userid );
				Cache::model()->updateByPk( 'usersuccess', array( 'cachevalue' => serialize( $success ) ) );
				$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => '正在发送邀请，请稍后..', 'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'sending' ) ) ) );
			}
		}
	}

	/**
	 * 通过类型创建访问官网的url，以便官网调用微信接口
	 * @param string $type 对应官网访问微信接口的方法名
	 * @return string url
	 */
	private function createUrlByType( $type ) {
		$aeskey = Wx::getInstance()->getAeskey();
		$url = WebSite::getInstance()->build( 'Api/Wxsync/' . $type, array( 'aeskey' => $aeskey ) );
		return $url;
	}

	/**
	 * 获取部门成员列表
	 * @return array 成员列表
	 */
	private function getDeptUser() {
		$url = $this->createUrlByType( 'syncDeptUser' );
		$wxUsers = WxApi::getInstance()->getDeptUser( $url );
		return $wxUsers;
	}

	/**
	 * 获取所有未同步人员
	 * 规则：手机号作为微信的userid
	 * 匹配顺序：微信号，手机号，邮箱，userid
	 * @param array $allUsers ibos的所有的用户
	 * @param array $wxUsers 微信端所有的用户
	 * @return array
	 */
	private function getLikeUsers( $allUsers, $wxUsers ) {
		foreach ( $allUsers as $key => $value ) {
			foreach ( $wxUsers as $k => $v ) {
				$isBindingWx = isset( $v['weixinid'] ) ? ($v['weixinid'] == $value['weixin']) : false;
				$isBindingSj = isset( $v['mobile'] ) ? ($v['mobile'] == $value['mobile']) : false;
				$isBindingYx = isset( $v['email'] ) ? ($v['email'] == $value['email']) : false;
				$isBindingUser = $v['userid'] == $value['mobile'];
				if ( $isBindingWx or $isBindingSj or $isBindingYx or $isBindingUser ) {
					$res = UserBinding::model()->find( sprintf( "`uid` = '%s' AND `bindvalue` = '%s' AND `app` = 'wxqy'", $value['uid'], $v['userid'] ) );
					if ( empty( $res ) ) {
						UserBinding::model()->deleteAll( sprintf( "`uid` = %d AND `app` = 'wxqy'", $value['uid'] ) );
						UserBinding::model()->add( array( 'uid' => $value['uid'], 'bindvalue' => $v['userid'], 'app' => 'wxqy' ) );
					}
					unset( $allUsers[$key] );
					unset( $wxUsers[$k] );
				}
			}
		}
		return array(
			'allUsers' => $allUsers,
			'wxUsers' => $wxUsers,
		);
	}

	/**
	 * 下载导入错误文件
	 */
	public function actionDownerror() {
		$error = Cache::model()->fetchArrayByPk( 'userfail' );
		$header = array( 'uid', '真实姓名', '错误原因' );
		$body = array();
		foreach ( $error as $uid => $msg ) {
			$name = User::model()->fetchRealnameByUid( $uid );
			$body[] = array( $uid, iconv( 'utf-8', 'gbk', $name ), iconv( 'utf-8', 'gbk', $msg ) );
		}
		Convert::exportCsv( '导入用户错误记录' . TIMESTAMP, $header, $body );
	}

}
