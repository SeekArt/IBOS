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

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Cache;
use application\modules\dashboard\utils\CoSync;
use application\modules\department\model\DepartmentBinding;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;
use application\modules\user\model\UserBinding;

class CobindingController extends CoController
{

    /**
     * 是否被安装时调用
     * 1 是
     * 0 否
     * @var integer
     */
    private $_isInstall = 0;

    /**
     * 酷办公用户登录信息
     */
    private $_coUser = null;

    /**
     * 控制器初始化
     * 根据当前调用的 URI isInstall 参数判断是在安装流程调用该控制器方法还是后台调用
     * 安装流程调用把 $_isInstall 私有变量设为 1
     * 后台流程调用把 $_isInstall 私有变量设为 0
     */
    public function init()
    {
        parent::init();
        $isInstall = Env::getRequest('isInstall');
        if ($isInstall == 1) {
            $this->_isInstall = 1;
        }
        $this->_coUser = Ibos::app()->user->getState('coUser');
    }

    /**
     * 首页视图动作，新流程
     */
    public function actionIndex()
    {
        // 尝试使用 accesstoken 自动登录
        $loginRes = $this->login();
        // 登录失败，跳转到 login 页面
        if ($loginRes['status'] === false) {
            $loginRes['data']['isInstall'] = $this->_isInstall;
            $this->render('login', $loginRes['data']);
        } // 登录成功
        else {
            $param['data'] = $loginRes['data'];
            // 如果本地已经绑定了酷办公
            if ($this->isBinding) {
                if (!array_key_exists($this->coinfo['corpid'], $this->corpListRes['corpList'])) {
                    $this->redirect(array('cobinding/index'));
                } else {
                    $this->redirect(array('cosync/index'));
                }

            } // IBOS 与酷办公未绑定,转到企业列表选择视图
            else {
                $this->corpListRes['isInstall'] = $this->_isInstall;
                $this->render('selectCorp', $this->corpListRes);
            }
        }
    }

    /**
     * IBOS 后台酷办公登录操作
     * @return array array( 'status' => true|false, 'data' => array() )
     */
    protected function login()
    {
        // 引导用户进行第一次的登录操作
        if (!$this->isBinding && $this->_coUser === null) {
            return array(
                'status' => false, 'data' => array('op' => 'noBinding'),
            );
        } else if ($this->isBinding && $this->_coUser['mobile'] != $this->coinfo['mobile']) {
            // 登录后没有绑定，但其他超管在其他浏览器抢先绑定了
            $unit = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
            $result['data'] = array(
                'ibos' => array(
                    'corplogo' => $unit['logourl'], 'corpshortname' => $unit['shortname'],
                    'systemurl' => $unit['systemurl'],
                ), 'co' => array(
                    'corplogo' => $this->coinfo['corplogo'], 'corpshortname' => $this->coinfo['corpshortname'],
                    'corpid' => $this->coinfo['corpid'],
                ), 'mobile' => $this->coinfo['mobile'], 'readonly' => true, 'op' => 'isBinding',
                'isInstall' => $this->_isInstall,
            );
            $result['status'] = false;
            return $result;
        } else {
            // 未绑定 && accesstoken 无效
            // 登录过，但是没有进行绑定，直到 accesstoken 过期
            // 引导用户重新登录
//				$result['data'] = array('op' => 'noBinding');
            $userInfo = $this->getCoUser($this->_coUser['accesstoken']);
            if ($userInfo['code'] == CodeApi::SUCCESS) {
                return $result = array(
                    'status' => true,
                    'data' => $userInfo,
                    'isInstall' => $this->_isInstall,
                );
            }
        }
    }

    /**
     * 登录动作
     */
    public function actionLogin()
    {
        $op = Env::getRequest('op');
        if ($op === null) {
            $op = 'noBinding';
        }
        if ($op === 'isBinding') {
            $unit = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
            $data = array(
                'ibos' => array(
                    'corplogo' => $unit['logourl'],
                    'corpshortname' => $unit['shortname'],
                    'corpname' => $unit['fullname'],
                ),
                'co' => array(
                    'corplogo' => $this->coinfo['corplogo'],
                    'corpshortname' => $this->coinfo['corpshortname'],
                    'corpname' => $this->coinfo['corpname'],
                ),
                'mobile' => $this->coinfo['mobile'],
                'readonly' => true,
            );
        }
        $data['isInstall'] = $this->_isInstall;
        $data['op'] = $op;
        $this->render('login', $data);
    }

    /**
     * 退出登录
     */
    public function actionLogout()
    {
        Ibos::app()->user->setState('coUser', null);
        $data = array(
            'op' => 'noBinding',
            'isInstall' => $this->_isInstall,
        );
        $this->render('login', $data);
    }

    /**
     * 获取手机验证码 ajax 请求接口
     * @return json
     */
    public function actionSendVerifyCode()
    {
        $mobile = Env::getRequest('mobile');
        $sendRes = CoApi::getInstance()->getVerifyCode(array('mobile' => $mobile));
        if ($sendRes['code'] == CodeApi::SUCCESS) {
            $this->ajaxReturn(array(
                'isSuccess' => true,
            ));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $sendRes['message'],
            ));
        }
    }

    /**
     * 验证手机验证码 ajax 请求接口
     * @return json
     */
    public function actionCheckVerifyCode()
    {
        $mobile = Env::getRequest('mobile');
        $verifyCode = Env::getRequest('verifyCode');
        $post = array(
            'mobile' => $mobile,
            'code' => $verifyCode,
        );
        $checkVerifyCodeRes = CoApi::getInstance()->checkVerifyCode($post);
        if ($checkVerifyCodeRes['code'] == CodeApi::SUCCESS) {
            if ($checkVerifyCodeRes['data']['checked'] == true) {
                $this->ajaxReturn(array(
                    'isSuccess' => true,
                ));
            } else {
                $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => '验证码不正确',
                ));
            }
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $checkVerifyCodeRes['message'],
            ));
        }
    }

    /**
     * 由于现在注册才会使用到手机验证码的流程
     * 所以必须验证手机号是未注册过的才行
     * @return json
     */
    public function actionCheckMobile()
    {
        $mobile = Env::getRequest('mobile');
        $checkMobileRes = CoApi::getInstance()->checkMobile($mobile);
        if ($checkMobileRes['code'] == CodeApi::SUCCESS) {
            switch ($checkMobileRes['data']['isexist']) {
                case '2':
                    $this->ajaxReturn(array(
                        'isSuccess' => false,
                        'msg' => '该手机号已被注册',
                    ));
                    break;
                case '1':
                    $this->ajaxReturn(array(
                        'isSuccess' => false,
                        'msg' => '该手机绑定的酷办公账号需要激活',
                    ));
                    break;
                case '0':
                    $this->ajaxReturn(array(
                        'isSuccess' => true,
                    ));
                    break;
            }
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $checkMobileRes['message'],
            ));
        }
    }

    /**
     * 注册酷办公新用户
     * v2 接口下，注册新用户时填了 password 的话是注册一个已激活的账号
     */
    public function actionRegisterCoUser()
    {
        $mobile = Env::getRequest('mobile');
        $realname = Env::getRequest('realname');
        $password = Env::getRequest('password');
        $post = array(
            'mobile' => $mobile,
            'realname' => $realname,
            'password' => $password,
        );
        $registerCoUserRes = CoApi::getInstance()->registerUser($post);
        if ($registerCoUserRes['code'] == CodeApi::SUCCESS) {
            $this->ajaxReturn(array(
                'isSuccess' => true,
            ));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $registerCoUserRes['message'],
            ));
        }
    }

    /**
     * 需求改了！！！！！！！！！！！
     * 初次登录，根据手机验证码来登录
     * @return json
     */
    // public function actionLoginByVerifyCode() {
    // 	$mobile = Env::getRequest( 'mobile' );
    // 	$verifyCode = Env::getRequest( 'verifyCode' );
    // 	$post = array(
    // 		'mobile'	=> $mobile,
    // 		'code'		=> $verifyCode,
    // 		'autoreg'	=> 1,
    // 		'platform'	=> 'pc',
    // 	);
    // 	$verifyLoginRes = CoApi::getInstance()->checkVerifyCode( $post );
    // 	if ( $verifyLoginRes['code'] == CodeApi::SUCCESS ) {
    // 		$coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
    // 		$coinfo['accesstoken'] = $verifyLoginRes['data']['accesstoken'];
    // 		$coinfo['guid'] = $verifyLoginRes['data']['guid'];
    // 		$coinfo['mobile'] = $mobile;
    // 		Setting::model()->updateSettingValueByKey( 'coinfo', serialize( $coinfo ) );
    // 		// 如果是新注册用户，需要显示提示页
    // 		if ( $verifyLoginRes['data']['isNew'] == 1 ) {
    // 			$uid = Ibos::app()->setting->get( 'session/uid' );
    // 			$userInfo = UserModel::model()->findByPk( $uid );
    // 			$post = array( 'passwordciphertext' => $userInfo->password, 'salt' => $userInfo->salt );
    // 			$syncRes = CoApi::getInstance()->syncPassword( $verifyLoginRes['data']['accesstoken'], $post );
    // 			// 同步密码成功，给用户相应的提示
    // 			if ( $syncRes['code'] == CodeApi::SUCCESS ) {
    // 				$this->ajaxReturn( array(
    // 					'isSuccess' => true,
    // 				) );
    // 			}
    // 			else {
    // 				$this->ajaxReturn( array(
    // 					'isSuccess'	=> false,
    // 					'msg'		=> $syncRes['message'],
    // 				) );
    // 			}
    // 		}
    // 		// 如果是旧用户，直接跳转企业选择页
    // 		else {
    // 			$this->ajaxReturn( array(
    // 				'isSuccess' => true,
    // 			) );
    // 		}
    // 	}
    // 	else {
    // 		/**
    // 		 * 日志记录
    // 		 */
    // 		$log = array(
    // 			'user'		=> Ibos::app()->user->username,
    // 			'ip'		=> Ibos::app()->setting->get( 'clientip' ),
    // 			'isSuccess'	=> 0,
    // 			'msg'		=> $verifyLoginRes['message'],
    // 		);
    // 		Log::write( $log, 'action', 'module.dashboard.cobinding.loginco' );
    // 		$this->ajaxReturn( array(
    // 			'isSuccess'	=> false,
    // 			'msg'		=> $verifyLoginRes['message'],
    // 		) );
    // 	}
    // }

    /**
     * 前端ajax请求的登录地址
     * 本地已经登录过酷办公，存在一个 accesstoken
     * 但是使用这个 accesstoken 无法登录
     * 可能是过期或者别的异常原因
     * 重新使用 手机号 密码 进行登录，更新本地 accesstoken
     * 然后刷新页面继续自动登录吧。。。。。。
     */
    public function actionLoginByPassword()
    {
        $mobile = Env::getRequest('mobile');
        $password = Env::getRequest('password');
        // 这里的验证作用是保证登录酷办公用户的一定是绑定的那个
        if ($this->isBinding) {
            // 绑定状态下登陆还要去看看该登陆用户是不是该企业的超管
//			$coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
            if (!empty($this->coinfo['mobile']) && $this->coinfo['mobile'] != $mobile) {
                $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => '请使用 OA 绑定的正确酷办公账号进行登录。'
                ));
            }
        }
        $tokenRes = CoApi::getInstance()->getCoToken($mobile, $password);
        if ($tokenRes['code'] == CodeApi::SUCCESS) {
            $coUser = array(
                'accesstoken' => $tokenRes['data']['accesstoken'],
                'guid' => $tokenRes['data']['guid'],
                'mobile' => $mobile,
            );
            Ibos::app()->user->setState('coUser', $coUser);
            $coinfo['accesstoken'] = $tokenRes['data']['accesstoken'];
            Setting::model()->updateSettingValueByKey('coinfo', serialize($coinfo));
            $this->ajaxReturn(array(
                'isSuccess' => true,
                'isInstall' => $this->_isInstall,
            ));
        } else {
            /**
             * 日志记录
             */
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'isSuccess' => 0,
                'msg' => $tokenRes['message'],
            );
            Log::write($log, 'action', 'module.dashboard.cobinding.loginco');
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $tokenRes['message'],
            ));
        }
    }

    /**
     * 酷办公解绑原有OA后，绑定现登录OA，并将现有OA与酷办公绑定
     * 这是绑定操作中出现准备绑定的酷办公已经绑定了其他 OA 的情况下调用的解绑操作
     */
    // public function actionAlterCorpBindToMe() {
    // 	$corptoken = Env::getRequest( 'corptoken' );
    // 	$coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
    // 	// 开始绑定
    // 	$res = $this->corpBinding( $corptoken );
    // 	// 绑定成功
    // 	if ( $res['code'] == CodeApi::SUCCESS ) {
    // 		Setting::model()->updateSettingValueByKey( 'cobinding', 1 );
    // 		$coinfo['corptoken'] = $corptoken;
    // 		Setting::model()->updateSettingValueByKey( 'coinfo', serialize( $coinfo ) );
    // 		$this->ajaxReturn( array(
    // 			'isSuccess' => true,
    // 		) );
    // 	// 绑定失败
    // 	} else {
    // 		/**
    // 		 * 日志记录
    // 		 */
    // 		$log = array(
    // 			'user'		=> Ibos::app()->user->username,
    // 			'ip'		=> Ibos::app()->setting->get( 'clientip' ),
    // 			'isSuccess'	=> 0,
    // 			'msg'		=> $res['message'],
    // 		);
    // 		Log::write( $log, 'action', 'module.dashboard.cobinding.loginco' );
    // 		$this->ajaxReturn( array(
    // 			'isSuccess'	=> false,
    // 			'msg'		=> $res['message'],
    // 		) );
    // 	}
    // }

    /**
     * 解绑酷办公
     * 这是 IBOS 与酷办公互相绑定的情况下接触双方绑定关系的解绑操作
     */
    public function actionUnbinding()
    {
        $unbindingRes = CoApi::getInstance()->unbindingCo($this->coinfo['corptoken']);
        if ($unbindingRes['code'] == CodeApi::SUCCESS) {
            $this->ajaxReturn(array(
                'isSuccess' => true,
            ));
        } // 企业令牌过期，更新一下令牌再重新调用接口
        else if ($unbindingRes['code'] == CodeApi::TOKEN_EXPIRY) {
            $this->updateCorptoken($this->coinfo['corptoken']);
            $unbindingRes = CoApi::getInstance()->unbindingCo($this->coinfo['corptoken']);
            if ($unbindingRes['code'] == CodeApi::SUCCESS) {
                $this->ajaxReturn(array(
                    'isSuccess' => true,
                ));
            } else {
                $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => $unbindingRes['message'],
                ));
            }
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $unbindingRes['message'],
            ));
        }
    }

    /**
     * 更新企业令牌操作
     * @param  string &$corptoken 存放返回的新企业令牌
     * @return boolen             true | false
     */
    private function updateCorptoken(&$corptoken)
    {
//		$this->coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
        $updateCorpTokenRes = CoApi::getInstance()->getCorpListByAccessToken($this->coinfo['accesstoken']);
        if ($updateCorpTokenRes['code'] == CodeApi::SUCCESS) {
            foreach ($updateCorpTokenRes['data'] as $corpinfo) {
                if ($corpinfo['corpid'] === $this->coinfo['corpid']) {
                    $this->coinfo['corptoken'] = $corpinfo['corptoken'];
//					Setting::model()->updateSettingValueByKey( 'this->coinfo', serialize( $this->>coinfo ) );
                    $corptoken = $corpinfo['corptoken'];
                    break;
                }
            }
            return true;
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $updateCorpTokenRes['message'],
            ));
        }
    }

    /**
     * 获取 IBOS-酷办公用户同步绑定数据
     * @return array
     */
    // protected function getAllBindingRelationForCo() {
    // 	$bindingRelations = UserBinding::model()->findAll( "`app` = 'co'" );
    // 	foreach ( $bindingRelations as $relation ) {
    // 		$result[] = array(
    // 			'uid'		=> $relation['bindvalue'],
    // 			'bindvalue'	=> $relation['uid'],
    // 		);
    // 	}
    // 	return isset( $result ) ? $result : array();
    // }

    /**
     * 调用酷办公接口，移除对应的绑定关系
     * 需要的关系数组结构：
     * array(
     *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
     *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
     *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
     *     ...
     * )
     * @param  array $relationList 需要移除的绑定关系数组
     * @return ajax
     */
    // protected function removeCoRelation( $relationList ) {
    // 	$coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
    // 	$post = array(
    // 		'type'		=> 'ibos',
    // 		'corpid'	=> $coinfo['corpid'],
    // 		'data'		=> $relationList,
    // 	);
    // 	$removeCoRelationRes = CoApi::getInstance()->removeRelationByList( $post );
    // 	if ( $removeCoRelationRes['errorcode'] != CodeApi::SUCCESS ) {
    // 		$this->ajaxReturn( array(
    // 			'isSuccess'	=> false,
    // 			'msg'	=> $removeCoRelationRes['message'],
    // 		) );
    // 	}
    // }

    /**
     * 创建企业并且绑定
     */
    public function actionCreateAndBinding()
    {
        $corpshortname = Env::getRequest('corpshortname');
        $corpname = Env::getRequest('corpname');
        // $unit = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
        $coinfo = $this->_coUser;
        // $coinfo = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'coinfo' ) );
        $accesstoken = $coinfo['accesstoken'];
        $post = array(
            'shortname' => $corpshortname,
            'name' => $corpname,
            // 'code'		=> $unit['corpcode'],
            'regip' => Env::getClientIp(),
            'craetefrom' => 'ibos',
            // 'systemurl'	=> $unit['systemurl'],
        );
        $createCorpRes = CoApi::getInstance()->createCorpByToken($accesstoken, $post);
        // 创建新企业成功
        if ($createCorpRes['code'] == CodeApi::SUCCESS) {
            $bindRes = $this->corpBinding($createCorpRes['data']['corptoken']);
            if ($bindRes['code'] == CodeApi::SUCCESS) {
                $this->ajaxReturn(array(
                    'isSuccess' => true,
                    'isInstall' => $this->_isInstall,
                ));
            } // 绑定失败
            else {
                $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => $bindRes['message'],
                ));
            }
        } else {
            /**
             * 日志记录
             */
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'isSuccess' => 0,
                'msg' => $createCorpRes['message'],
            );
            Log::write($log, 'action', 'module.dashboard.cobinding.loginco');
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $createCorpRes['message'],
            ));
        }
    }

    /**
     * 根据用户选择的企业，准备进行绑定
     * 在新流程的 index 动作中被使用到
     * @return array array( 'status' => true|false, 'data' => array() )
     */
    public function actionReadyBinding()
    {
        $data['corpid'] = Env::getRequest('corpid');
        $data['corptoken'] = Env::getRequest('corptoken');
        $data['corpshortname'] = Env::getRequest('corpshortname');
        $data['corpname'] = Env::getRequest('corpname');
        $data['corplogo'] = Env::getRequest('corplogo');
        // $corpcode 	= Env::getRequest( 'corpcode' );
        // 是否前台安装步骤结束时的绑定调用判断
        // $isInstall = Env::getRequest( 'isInstall' );
        $msg = '';
        $isSuccess = $this->bindCorp($data, $msg);
        if (true === $isSuccess) {
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'isInstall' => $this->_isInstall,
            ));
        } else {
            return $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => $msg,
            ));
        }
    }

    /**
     * 建立企业绑定关系，更新setting表
     * @param $data
     * @param $msg
     * @return bool
     */
    private function bindCorp($data, &$msg)
    {
        $bindRes = $this->corpBinding($data['corptoken']);
        if ($bindRes['code'] == CodeApi::SUCCESS) {
            return true;
        } else {
            $msg = $bindRes['message'];
            return false;
        }
    }

    /**
     * 绑定酷办公企业操作
     * @param  string $corptoken 企业令牌
     * @return array            返回的数据
     */
    protected function corpBinding($corptoken)
    {
        // 避免多人同时登录了酷办公账号然后先后进行绑定操作
        if ($this->isBinding) {
            return array(
                'code' => 1,
                'message' => '当前 IBOS 已成功绑定酷办公企业',
            );
        }
        $aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
        $systemurl = Ibos::app()->request->getHostInfo();
        $post = array(
            'aeskey' => $aeskey,
            'systemurl' => $systemurl,
        );
        return CoApi::getInstance()->bindingCo($corptoken, $post);
    }

}
