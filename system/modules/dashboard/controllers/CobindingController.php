<?php

/**
 * CobindingController.class.file
 * 
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 */
/**
 * 酷办公绑定控制器
 * 
 * @package application.modules.dashboard.controllers
 * @author mumu <2317216477@qq.com>
 * 
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\WebSite;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\utils\User as UserUtil;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;
use application\modules\main\model\Setting;

class CobindingController extends CoController {

	/**
	 * 首页视图
	 */
	public function actionIndex() {
		//首先根据setting表里的accesstoken获取企业管理员的登录信息
		$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
		$res = $this->getCoUser( isset( $coinfo['accesstoken'] ) ? $coinfo['accesstoken'] : ''  );
		if ( $res['code'] == CodeApi::SUCCESS ) {
			//本地accesstoken登录成功
			$param = array( 'data' => $res['data'] );
			$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
			if ( $this->isBinding ) {
				//酷办公是否匹配oa
				$coIsBindOa = $this->coIsBindOa( isset( $coinfo['corptoken'] ) ? $coinfo['corptoken'] : '', $aeskey );
				if ( $coIsBindOa ) {
					$params = $this->returnCorp( $param );
					$params['data']['accesstoken'] = $coinfo['accesstoken'];
					$this->render( 'index', $params );
				} else {
					//否则需要重新登录
					$this->render( 'login' );
				}
			} else { //如果本地没有绑定
				if ( $res['data']['role'] == 'admin' ) {
					$corpRes = CoApi::getInstance()->getCorpByCorpToken( isset( $coinfo['corptoken'] ) ? $coinfo['corptoken'] : ''  );
					if ( $corpRes['code'] == CodeApi::SUCCESS ) {
						if ( !empty( $corpRes['data']['aeskey'] ) ) { //已绑定
							if ( strcmp( $aeskey, $corpRes['data']['aeskey'] ) != 0 ) {//但是企业不一致，显示解绑
								$param['op'] = 'unbindingoa';
								$param['data']['systemurl'] = $corpRes['data']['systemurl'];
								$this->render( 'index', $param );
							} else {
								$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
								//远程已经绑定（有aeskey），但是aeskey相等，能到这里说明是本地cobinding是0，同时又是超管。可是其实这个情况已经算是绑定了的
								Setting::model()->updateSettingValueByKey( 'cobinding', 1 );
								$params = $this->returnCorp( $param );
								$params['data']['accesstoken'] = $coinfo['accesstoken'];
								$this->render( 'index', $params );
							}
						} else { //远程未绑定OA
							$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
							if ( strcmp( $unit['corpcode'], $corpRes['data']['code'] ) == 0 ) { //相等，表示企业代码统一
								$post = array(
									'aeskey' => $aeskey,
									'systemurl' => $unit['systemurl'],
									'name' => $unit['fullname'],
									'shortname' => $unit['shortname']
								);
								$updateRes = CoApi::getInstance()->updateCorpByCorpToken( $coinfo['corptoken'], $post );
								if ( $updateRes['code'] == CodeApi::SUCCESS ) {
									Setting::model()->updateSettingValueByKey( 'cobinding', 1 );
								}
								$params = $this->returnCorp( $param );
								$params['data']['accesstoken'] = $coinfo['accesstoken'];
								$this->render( 'index', $params );
							} else {
								//不想等，企业代码不一致，把本地的和酷办公的输出
								$param['op'] = 'oacode';
								$param['data']['oacode'] = $unit['corpcode'];
								$param['data']['cocode'] = $corpRes['data']['code'];
								$this->render( 'index', $param );
							}
						}
					} else {
						//远程是管理员，本地accesstoken成功登录，但是corptoken过期或者没有，这个情况……什么时候出现的orz
						$this->render( 'login', array( 'mobile' => $coinfo['mobile'], 'readonly' => true ) );
					}
				} else {
					//不是管理员
					if ( $res['data']['isjoin'] == 'no' ) {//没有加入企业，则创建新的企业
						$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
						$param['op'] = 'usercreate';
						$param['data']['corpname'] = $unit['fullname'];
						$param['data']['corpshortname'] = $unit['shortname'];
						$param['data']['corpcode'] = $unit['corpcode'];
						$this->render( 'index', $param );
					} else {
						/**
						 * 加入了企业，则：
						 * 1、退出并创建新企业
						 * 或者
						 * 2、重新登录
						 * 现在使用同一个视图
						 */
						$param['op'] = 'userquitorlogin';
						$this->render( 'index', $param );
					}
				}
			}
		} else {
			//根据Setting coinfo保存的accesstoken尝试登录
			if ( $this->isBinding ) {
				//说明过期，获取本地保存的mobile，写成readonly形式
				$this->render( 'login', array( 'mobile' => $coinfo['mobile'], 'readonly' => true ) );
			} else {
				$this->render( 'login' );
			}
		}
	}

	public function actionLogin() {
		$this->render( 'login' );
	}

	/**
	 * 酷办公是否绑定oa，用aeskey判断，安装的时候用systemurl去验证
	 * @param type $corptoken
	 * @param type $aeskey
	 * @return boolean
	 */
	public function coIsBindOa( $corptoken, $aeskey ) {
		$corpRes = CoApi::getInstance()->getCorpByCorpToken( $corptoken );
		if ( $corpRes['code'] == CodeApi::SUCCESS ) {
			return !strcmp( $aeskey, $corpRes['data']['aeskey'] );
		} else {
			return false;
		}
	}

	/**
	 * 返回企业显示所需要的数据
	 * @param type $param
	 * @return array
	 */
	public function returnCorp( $param ) {
		//匹配就显示该企业的信息
		$oaUser = UserUtil::loadUser();
		$userBinding = UserBinding::model()->fetchAllByApp( 'co' );
		$param['op'] = 'index';
		$param['data']['oaUser'] = $oaUser;
		$param['data']['userBinding'] = $userBinding;
		return $param;
	}

	/**
	 * 前端ajax请求的登录地址
	 */
	public function actionLoginco() {
		$mobile = Env::getRequest( 'mobile' );
		$password = Env::getRequest( 'password' );
		if ( $this->isBinding ) {
			$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
			if ( $coinfo['mobile'] != $mobile ) {
				$this->ajaxReturn( array(
					'isSuccess' => false,
					'msg' => '请用oa绑定的酷办公的超级管理员账号登录'
				) );
			}
		}
		$tokenRes = CoApi::getInstance()->getCoToken( $mobile, $password );
		if ( $tokenRes['code'] == CodeApi::SUCCESS ) {
			//只有在绑定的情况下，如果登录的不是对应的oa的账号，就提示不匹配
			if ( $this->isBinding ) {
				$corpRes = CoApi::getInstance()->getCorpByCorpToken( $tokenRes['data']['corptoken'] );
				$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
				$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
				if ( !empty( $corpRes['data']['aeskey'] ) ) {
					if ( strcmp( $aeskey, $corpRes['data']['aeskey'] ) != 0 ) {
						$this->ajaxReturn( array(
							'isSuccess' => false,
							'msg' => '酷办公账号和oa不匹配，请重新登录', //出现这个情况，是本地accesstoken过期，且远程aeskey错误
						) );
					}
				} else {
					Setting::model()->updateSettingValueByKey( 'cobinding', 0 );
					$this->ajaxReturn( array(
						'isSuccess' => true,
					) );
				}
			}
			/**
			 * 1、没有绑定，登录保存登录信息
			 * 2、绑定了，绑定的酷办公账号对应找到的企业aeskey和本地的一致
			 * 最开始两种状态：
			 * a、没有绑定酷办公
			 * b、绑定了酷办公，此时本地保存了超管的mobile，尝试用超管账号登录失败后，才会来到登录的这个方法
			 * 所以一定对应2的情况
			 */
			$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
			$coinfo['corptoken'] = $tokenRes['data']['corptoken'];
			$coinfo['accesstoken'] = $tokenRes['data']['accesstoken'];
			$coinfo['guid'] = $tokenRes['data']['guid'];
			$coinfo['mobile'] = $mobile;
			Setting::model()->updateSettingValueByKey( 'coinfo', serialize( $coinfo ) );
			$this->ajaxReturn( array(
				'isSuccess' => true,
			) );
		} else {
			$this->ajaxReturn( array(
				'isSuccess' => false,
				'msg' => $tokenRes['message'],
			) );
		}
	}

	/**
	 * 统一ibos和酷办公代码，以远程的为准
	 */
	public function actionUnifyCode() {
		$code = Env::getRequest( 'code' );
		$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
		$unit['corpcode'] = $code;
		Setting::model()->updateSettingValueByKey( 'unit', serialize( $unit ) );
		Setting::model()->updateSettingValueByKey( 'cobinding', 1 );
		$this->ajaxReturn( array(
			'isSuccess' => true,
		) );
	}

	/**
	 * 酷办公解绑原有OA后，绑定现登录OA，并将现有OA与酷办公绑定
	 */
	public function actionImUnbindingIbos() {
		$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
		$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
		$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
		$corptoken = $coinfo['corptoken'];
		$post = array(
			'aeskey' => $aeskey,
			'systemurl' => $unit['systemurl'],
			'name' => $unit['fullname'],
			'shortname' => $unit['shortname']
		);
		$res = CoApi::getInstance()->updateCorpByCorpToken( $corptoken, $post );
		if ( $res['code'] == CodeApi::SUCCESS ) {
			Setting::model()->updateSettingValueByKey( 'cobinding', 1 );
			$this->ajaxReturn( array(
				'isSuccess' => true,
			) );
		} else {
			$this->ajaxReturn( array(
				'isSuccess' => false,
				'msg' => $res['message'],
			) );
		}
	}

	/**
	 * 解绑酷办公
	 */
	public function actionUnbinding() {
		//请求远程，删除aeskey
		$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
		$res = CoApi::getInstance()->updateCorpByCorpToken( $coinfo['corptoken'], array( 'aeskey' => '' ) );
		if ( $res['code'] == CodeApi::SUCCESS ) {
			Setting::model()->updateSettingValueByKey( 'cobinding', 0 );
			Setting::model()->updateSettingValueByKey( 'coinfo', '' );
			$this->ajaxReturn( array( 'isSuccess' => true, ) );
		} else {
			$this->ajaxReturn( array(
				'isSuccess' => false,
				'msg' => $res['message']
			) );
		}
	}
	/**
	 * 退出企业
	 */
	public function actionQuitCo() {
		$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
		$corptoken = $coinfo['corptoken'];
		$res = CoApi::getInstance()->quitCorpByCorpToken( $corptoken );
		if ( $res['code'] == CodeApi::SUCCESS ) {
			$this->ajaxReturn( array(
				'isSuccess' => true,
			) );
		} else {
			$this->ajaxReturn( array(
				'isSuccess' => false,
				'msg' => $res['message'],
			) );
		}
	}
	/**
	 * 创建企业并且绑定
	 */
	public function actionCreateAndBinding() {
		//创建企业
		$unit = unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
		$coinfo = unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
		$accesstoken = $coinfo['accesstoken'];
		$post = array(
			'name' => $unit['shortname'],
			'code' => $unit['corpcode'],
			'regip' => Env::getClientIp(),
			'systemurl' => $unit['systemurl'],
		);
		$res = CoApi::getInstance()->createCorpByToken( $accesstoken, $post );
		if ( $res['code'] == CodeApi::SUCCESS ) {
			//绑定
			$guid = $coinfo['guid'];
			UserBinding::model()->add( array( 'uid' => IBOS::app()->user->uid, 'bindvalue' => $guid, 'app' => 'co' ) );
			$this->ajaxReturn( array(
				'isSuccess' => true,
			) );
		} else {
			$this->ajaxReturn( array(
				'isSuccess' => false,
				'msg' => $res['message'],
			) );
		}
	}

}
