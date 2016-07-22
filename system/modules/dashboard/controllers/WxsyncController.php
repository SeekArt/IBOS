<?php

/**
 * 
 * 微信绑定控制器2.0
 * 取消actionSync1.0（请找到过去的代码查看，这里已经删除）里先获取数据对比，然后同步的方式
 * 改换成每次ajax从数据库取若干条记录，“尝试”创建数据到企业号的方法
 * 支持超级~~~~~大的数据同步
 * 实际测试100条一批的一个ajax请求在微信那边会卡上10多秒，本身处理只要3秒，服务器配置好的话消耗都在微信了
 * 感谢Mr.Z的1.0版本，以及Mr.C的突发奇想，不然我也不会脑洞大开用这种奇葩方式重写2.0
 * 这里重点在于“尝试”：
 * 1、如果成功，然后没有然后了
 * 2、如果失败，返回信息是存在的话，那么本地直接绑定
 * 3、如果失败，其他不可容错的问题，如手机号12位，就在最后提示打印错误
 * 就是把本地对比过程交给微信（接口端），大数据化成小数据。把对比过程放在进度条的每次请求中，用户不会感到慢
 * 2.0的改进主要是为了针对性能而做出的改动，所以，这里所有的查询都不使用AR的方式
 * 暂时不封装这些DAO语句，尽管它们并没有在循环里，但它们本身带的数据比较大
 * 放在函数里，把大的数据传进传出反而不好
 * 
 * @namespace application\modules\dashboard\controllers
 * @filename WxsyncController.php
 * @encoding UTF-8
 * @author 1.0 banyanCheung <banyan@ibos.com.cn>
 * 				forsona 
 *          2.0 forsona <2317216477@qq.com>
 * @link https://github.com/forsona
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016-6-4 14:13:41
 * @version $Id$
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\WebSite;
use application\modules\dashboard\utils\Wx;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;
use application\modules\user\utils\User as UserUtil;
use Exception;

class WxsyncController extends WxController {

	/**
	 * 获取企业号绑定视图
	 */
	public function actionIndex() {
		if ( false === $this->isBinding ) {
			return $this->unbindRender();
		}
		//获取已经同步的人员
		$wxUsers = $this->getDeptUser();
		$userCount = IBOS::app()->db->createCommand()
				->select( 'count(uid)' )
				->from( '{{user}}' )
				->where( " `status` = '0' " )
				->queryScalar();
		$total = IBOS::app()->db->createCommand()
				->select( 'count(uid)' )
				->from( '{{user_binding}}' )
				->where( " `app` = 'wxqy' " )
				->queryScalar();
		$params = array(
			'bindCount' => $total,
			'localCount' => $userCount - $total,
			'wxCount' => max( count( $wxUsers ) - $total, 0 ),
			'unit' => IBOS::app()->setting->get( 'setting/unit' ),
			'aeskey' => IBOS::app()->setting->get( 'setting/aeskey' ),
			'wxqy' => array(
				'name' => $this->wxqyInfo['name'],
				'logo' => $this->wxqyInfo['logo'],
				'corpid' => $this->wxqyInfo['corpid'],
			),
		);

		$this->render( 'index', $params );
	}

	const DEPT_NUM_PER = 100; //每次从数据库里取的部门数目，默认100
	const USER_NUM_PER = 100; //每次从数据库里取的用户数目，默认100

	/**
	 * 同步部门人员2.0
	 * @return ajaxReturn
	 */

	public function actionSync() {
		set_time_limit( 120 );
		$op = Env::getRequest( 'op' );
		if ( !in_array( $op, array( 'init', 'dept', 'user', 'sending' ) ) ) {
			$op = 'init';
		}
		return $this->{'handle' . ucfirst( $op ) }();
	}

	private function deptid_not_in_binding() {
		return " `deptid` NOT IN ( SELECT `deptid` FROM {{department_binding}} WHERE `app` = 'wxqy' ) ";
	}

	private function uid_not_in_binding() {
		return " `uid` NOT IN ( SELECT `uid` FROM {{user_binding}} WHERE `app` = 'wxqy' ) ";
	}

	private function handleInit() {
		$deptCount = IBOS::app()->db->createCommand()
				->select( 'count(deptid)' )
				->from( '{{department}}' )
				->where( $this->deptid_not_in_binding() )
				->queryScalar();
		$userCount = IBOS::app()->db->createCommand()
				->select( 'count(uid)' )
				->from( '{{user}}' )
				->where( $this->uid_not_in_binding() )
				->andWhere( " `status` = 0 " )
				->queryScalar();
		$sendMail = Env::getRequest( 'status' );
		$dept = IBOS::app()->db->createCommand()
				->select( 'deptid,bindvalue' )
				->from( '{{department_binding}}' )
				->where( " `app` = 'wxqy' " )
				->queryAll();
		$deptRelated = array();
		if ( !empty( $dept ) ) {
			foreach ( $dept as $d ) {
				$deptRelated[$d['deptid']] = $d['bindvalue'];
			}
		}
		$wxqy = array(
			'sendmail' => $sendMail,
			'deptlevel' => 0, //部门分层，pid为0的，level为0，依此类推
			'deptcount' => $deptCount,
			'deptrelated' => $deptRelated,
			'error' => array(),
			'success' => array(),
			'successSending' => 0,
			'usercount' => $userCount,
		);
		IBOS::app()->user->setState( 'wxqy', $wxqy );
		$ajaxReturn = array(
			'data' => array(
				'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'dept' ) ),
				'isSuccess' => true,
				'deptCount' => $deptCount,
				'userCount' => $userCount,
			),
			'msg' => '开始同步部门，请耐心等候...',
		);
		return $this->ajaxReturn( $ajaxReturn );
	}

	/**
	 * 根据部门的层级创建查询的条件
	 * @param integer $level
	 */
	private function createConditionByDeptLevel( $level = 0 ) {
		$sqlString = IBOS::app()->db->createCommand()
				->select( 'deptid' )
				->from( '{{department}}' )
				->where( " `pid` IN ( <string> )" )
				->getText();
		$sql = $sqlString;
		while ( $level-- ) {
			$sql = str_replace( '<string>', $sqlString, $sql );
		}
		return str_replace( '<string>', 0, $sql );
	}

	private function getPerDept( $level ) {
		$deptidCondition = $this->createConditionByDeptLevel( $level );
		$return = IBOS::app()->db->createCommand()
				->select( 'deptname,deptid,pid,sort' )
				->from( '{{department}}' )
				->where( " `deptid` IN( {$deptidCondition} )" )
				->andWhere( $this->deptid_not_in_binding() )
				->order( 'deptid ASC' )
				->limit( self::DEPT_NUM_PER )
				->queryAll();
		return $return;
	}

	private function handleDept() {
		$wxqy = IBOS::app()->user->wxqy;
		$level = $wxqy['deptlevel'];
		$i = 10;
		while ( $i ) {
			//这个使用子查询的方式去遍历一棵树
			$deptPer = $this->getPerDept( $level );
			if ( !empty( $deptPer ) ) {
				$wxqy['deptlevel'] = $level;
				break;
			} else {
				$i--;
				//当前层级找不到数据，则尝试着下一层级找部门
				$level ++;
			}
		}
		//找了10层也没有就认为部门同步完成
		if ( empty( $deptPer ) ) {
			//下一层也找不到，则表示完成了
			return $this->ajaxReturn( array(
						'isSuccess' => true,
						'msg' => '同步部门完成。开始处理用户,请稍后..',
						'data' => array(
							'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'user' ) )
				) ) );
		}
		//至少有一个部门才会进来这边
		$related = $wxqy['deptrelated'];
		$url = $this->createUrlByType( 'syncDept' );
		$bindArray = array();
		foreach ( $deptPer as $dept ) {
			$wxqy['deptcount'] --;
			if ( $dept['pid'] == 0 || isset( $related[$dept['pid']] ) ) {
				$pid = $dept['pid'] == 0 ? 1 : $related[$dept['pid']];
				$res = WxApi::getInstance()->createDept( $dept['deptname'], $pid, $dept['sort'], $url );
//				$newId = 1;
//				$res['isSuccess'] = 1;
//				$res['data']['id'] = $dept['deptid'];
				if ( $res['isSuccess'] && isset( $res['data']['id'] ) ) {
					$newId = $res['data']['id'];
				} else if ( !$res['isSuccess'] ) {
					return $this->ajaxReturn( array(
								'isSuccess' => false,
								'msg' => '部门同步失败，错误代码：' . $res['data']['errcode'] . '，错误原因：' . Code::getErrmsg( $res['data']['errcode']
						) ) );
				} else {
					//没有权限或者父部门错了，给顶级部门
					if ( in_array( $res['data']['errcode'], array( '60004', '60011' ) ) ) {
						$newId = 1;
					}
					//如果已经创建，解析返回信息里的部门id……这个，如果微信改了msg，那就gg了
					if ( $res['data']['errcode'] == '60008' ) {
						if ( preg_match_all( '/\s(\\d+)\s/', $res['data']['errmsg'], $matches ) ) {
							$newId = isset( $matches[1] ) ? $matches[1][0] : 1; //如果不给数字了。。。。
						} else {
							$newId = 1;
						}
					}
				}
				//成功，则创建【OA部门=>企业号部门】的对应关系
				$related[$dept['deptid']] = $newId;
				$bindArray[$dept['deptid']] = $newId;
			} else {
				//由于使用了部门分层的方式去查询部门
				//所以可以确保部门数据的顺序一定是按照从上到下的方式，这里正常是不会进来的
				//如果进来了，请修复这个bug
				//file_put_contents( 'wx_syncdept_continue.txt', var_export( $dept, true ), FILE_APPEND );
			}
		}
		$wxqy['deptrelated'] = $related;
		//这个count只是用来告诉用户还有多少个，别无他用
		IBOS::app()->user->setState( 'wxqy', $wxqy );
		$connection = IBOS::app()->db;
		$transaction = $connection->beginTransaction();
		try {
			foreach ( $bindArray as $oaDeptid => $wxDeptid ) {
				$connection->schema->commandBuilder
						->createInsertCommand( '{{department_binding}}', array(
							'deptid' => $oaDeptid,
							'bindvalue' => $wxDeptid,
							'app' => 'wxqy'
						) )
						->execute();
			}
			$transaction->commit();
		} catch (Exception $e) {
			//$transaction->rollback();
		}

		return $this->ajaxReturn( array(
					'isSuccess' => true,
					'msg' => '正在同步部门，还剩下' . $wxqy['deptcount'] . '个',
					'data' => array(
						'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'dept' ) ),
						'remain' => $wxqy['deptcount'],
			) ) );
	}

	private function handleUser() {
		$wxqy = IBOS::app()->user->wxqy;
		$deptidRelated = $wxqy['deptrelated'];
		$errorUidString = implode( ',', array_keys( $wxqy['error'] ) );
		$errorUidCondition = !empty( $errorUidString ) ? " `uid` NOT IN ( {$errorUidString} )" : 1;
		$uidArray = IBOS::app()->db->createCommand()
				->select( 'uid' )
				->from( '{{user}}' )
				->where( $this->uid_not_in_binding() )
				->andWhere( " `status` = 0 " )
				->andWhere( $errorUidCondition )
				->order( " uid ASC " )
				->limit( self::USER_NUM_PER )
				->queryColumn();
		if ( !empty( $uidArray ) ) {
			$bindArray = array();
			//这个是需要绑定的用户UID
			$userArray = UserUtil::wrapUserInfo( $uidArray, false );
			foreach ( $userArray as $user ) {
				$wxqy['usercount'] --;
				$wxDeptid = array();
				foreach ( explode( ',', $user['alldeptid'] ) as $deptid ) {
					if ( isset( $deptidRelated[$deptid] ) ) {
						$wxDeptid[] = $deptidRelated[$deptid];
					}
				}
				$user['deptid'] = implode( ',', $wxDeptid );
				$user['userid'] = $user['mobile'];
				$user['gender'] = $user['gender'] == 1 ? 0 : 1;

				//创建链接
				$url = $this->createUrlByType( 'syncUser' );
				$res = WxApi::getInstance()->createUser( $user, $url );
				//$res = ''; //测试用
				if ( $res !== '' ) {
					//如果用户名已经存在，是不会返回错误的，因为存在的话直接绑定，此时res = ''
					//返回值不是空，说明有错误信息，空经过了我的处理了呢
					$wxqy['error'][$user['uid']] = array(
						'msg' => $res,
						'realname' => $user['realname'],
					);
				} else {
					//记录需要绑定的用户数据，等循环结束后面再绑定
					//当然如果期间php挂了，则会出现下面的情况
					//微信已经绑定成功，但是本地并没有建立绑定关系
					//但是没关系，再点击同步的时候，依旧会把绑定表中没有的数据筛选出提交给微信
					//看到这个if条件的另一半分支了没有，那个分支里返回空字符串
					//会在我接收到微信返回“XX已存在”时立刻建立绑定，所以这里失败了也没关系
					//唯一的缺陷就是，因为这种意外情况导致的本地没有建立绑定但是微信存在的用户
					//不会通过下面的success数组发关注提醒
					$bindArray[$user['uid']] = $user['userid'];
					$wxqy['success'][$user['uid']] = $user['mobile'];
				}
			}
			IBOS::app()->user->setState( 'wxqy', $wxqy );
			if ( !empty( $bindArray ) ) {
				$connection = IBOS::app()->db;
				$transaction = $connection->beginTransaction();
				try {
					foreach ( $bindArray as $uid => $bindValue ) {
						$connection->schema->commandBuilder
								->createInsertCommand( '{{user_binding}}', array(
									'app' => 'wxqy',
									'uid' => $uid,
									'bindvalue' => $bindValue,
								) )->execute();
					}
					$transaction->commit();
				} catch (Exception $e) {
					//$transaction->rollback();
				}
			}
			return $this->ajaxReturn(
							array(
								'isSuccess' => true,
								'msg' => '正在同步用户，还剩下' . $wxqy['usercount'] . '个，请稍后...',
								'data' => array(
									'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'user' ) ),
									'remain' => $wxqy['usercount']
						) ) );
		} else {
			return $this->ajaxReturn(
							array(
								'isSuccess' => true,
								'msg' => '正在发送邀请，请稍后..',
								'data' => array(
									'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'sending' )
									),
						) ) );
		}
	}

	private function handleSending() {
		$wxqy = IBOS::app()->user->wxqy;
		$success = count( $wxqy['success'] );
		$error = count( $wxqy['error'] );
		$downloadlink = $this->createUrl( 'wxsync/downerror' );
		if ( !empty( $error ) && empty( $success ) ) {
			$ajaxReturn = $this->ajaxReturn(
					array(
						'isSuccess' => true,
						'msg' => '全部失败',
						'data' => array(
							'downUrl' => $downloadlink,
							'errorCount' => $error,
							'tpl' => 'error',
						) ) );
		}

		$userid = array_shift( $wxqy['success'] );
		if ( !empty( $userid ) ) {
			$wxqy['successSending']+= 1;
			$url = $this->createUrlByType( 'sendInvition' );
			$res = WxApi::getInstance()->sendInvition( $url, $userid );
			IBOS::app()->user->setState( 'wxqy', $wxqy );
			//这里不需要管到底发送成功与否，因为……这个邀请一个星期发一次的。。。失败了也没辙
			$ajaxReturn = $this->ajaxReturn(
					array(
						'isSuccess' => true,
						'msg' => '正在发送邀请，请稍后..',
						'data' => array(
							'url' => $this->createUrl( 'wxsync/sync', array( 'op' => 'sending' ) )
						) ) );
		} else {
			if ( !empty( $error ) && !empty( $success ) ) {
				$ajaxReturn = $this->ajaxReturn(
						array(
							'isSuccess' => true,
							'msg' => '成功一半。。',
							'data' => array(
								'successCount' => $wxqy['successSending'],
								'errorCount' => $error,
								'downUrl' => $downloadlink,
								'tpl' => 'half',
							) ) );
			} else {
				IBOS::app()->user->setState( 'wxqy', NULL );
				$ajaxReturn = $this->ajaxReturn(
						array(
							'isSuccess' => true,
							'msg' => '成功全部完成！',
							'data' => array(
								'successCount' => $wxqy['successSending'],
								'tpl' => 'success',
							) ) );
			}
		}
		return $ajaxReturn;
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
		$url = $this->createUrlByType( 'syncDeptUserSimple' );
		$wxUsers = WxApi::getInstance()->getDeptUser( $url );
		return $wxUsers;
	}

	/**
	 * 下载导入错误文件
	 */
	public function actionDownerror() {
		$wxqy = IBOS::app()->user->wxqy;
		$error = $wxqy['error'];
		$header = array( 'uid', '真实姓名', '错误原因' );
		$body = array();
		foreach ( $error as $uid => $row ) {
			$body[] = array( $uid, iconv( 'utf-8', 'gbk', $row['realname'] ), iconv( 'utf-8', 'gbk', $row['msg'] ) );
		}
		Convert::exportCsv( '导入用户错误记录' . TIMESTAMP, $header, $body );
		IBOS::app()->user->setState( 'wxqy', NULL );
	}

}
