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

use application\core\utils\Ibos;
use application\core\utils\WebSite;
use CJSON;

class WxbindingController extends WxController
{

    /**
     * 获取企业号绑定视图
     */
    public function actionIndex()
    {
        $unit = Ibos::app()->setting->get('setting/unit');
        $aeskey = Ibos::app()->setting->get('setting/aeskey');
        $params = array(
            'fullname' => $unit['fullname'],
            'shortname' => $unit['shortname'],
            'logo' => $unit['logourl'],
            'domain' => $unit['systemurl'],
            'aeskey' => $aeskey,
            'isBinding' => $this->isBinding,
        );
        $isLogin = $this->wxqyInfo['isLogin'];
        if (false === $isLogin) {
            $view = 'login';
        } else {
            $view = 'index';
            $res = WebSite::getInstance()->fetch('Api/Api/checkAccess', array(
                'domain' => $unit['systemurl'],
                'aeskey' => $aeskey,
            ), 'post');
            if (is_array($res)) {
                $isSuccess = false;
                $msg = $res['error'];
            } else {
                $result = CJSON::decode($res);
                $isSuccess = $result['isSuccess'];
                $msg = $result['msg'];
            }
            $params['access'] = $isSuccess;
            $params['msg'] = $msg;
        }
        if (true === $this->isBinding) {
            $params['wxlogo'] = $this->wxqyInfo['logo'];
            $params['wxcorpid'] = $this->wxqyInfo['corpid'];
            $params['wxname'] = $this->wxqyInfo['name'];
            $params['mobile'] = $this->wxqyInfo['mobile'];
            $params['app'] = $this->wxqyInfo['app'];
            if (true === $isLogin) {
                return $this->redirect($this->createUrl('wxsync/index'));
            }
        }

        return $this->render($view, $params);
    }

    public function actionCheckAccess()
    {
        $domain = Ibos::app()->getRequest()->getPost('domain');
        $aeskey = Ibos::app()->setting->get('setting/aeskey');
        $res = WebSite::getInstance()->fetch('Api/Api/checkAccess', array(
            'domain' => $domain,
            'aeskey' => $aeskey,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        return $this->ajaxReturn($ajaxReturn);
    }

    public function actionLogin()
    {
        $request = Ibos::app()->getRequest();
        $mobile = $request->getPost('mobile');
        $password = $request->getPost('password');
        $res = WebSite::getInstance()->fetch('Api/Api/login', array(
            'mobile' => $mobile,
            'password' => $password,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        if (true === $ajaxReturn['isSuccess']) {
            Ibos::app()->user->setState('param', array(
                'uid' => $ajaxReturn['data']['uid'],
                'wxqyInfo' => array('mobile' => $mobile)
            ));
        }
        return $this->ajaxReturn($ajaxReturn);
    }

    public function actionLogout()
    {
        Ibos::app()->user->setState('param', array());
        return $this->redirect($this->createUrl('wxbinding/index'));
    }

    public function actionRegister()
    {
        $request = Ibos::app()->getRequest();
        $mobile = $request->getPost('mobile');
        $password = $request->getPost('password');
        $username = $request->getPost('realname');
        $res = WebSite::getInstance()->fetch('Api/Api/register', array(
            'mobile' => $mobile,
            'password' => $password,
            'username' => $username,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        if (true === $ajaxReturn['isSuccess']) {
            Ibos::app()->user->setState('param', array(
                'uid' => $ajaxReturn['data']['uid'],
                'wxqyInfo' => array('mobile' => $mobile)
            ));
        }
        return $this->ajaxReturn($ajaxReturn);
    }

    public function actionSendCode()
    {
        $request = Ibos::app()->getRequest();
        $mobile = $request->getPost('mobile');
        $res = WebSite::getInstance()->fetch('Api/Api/sendCode', array(
            'mobile' => $mobile,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        return $this->ajaxReturn($ajaxReturn);
    }

    public function actionCheckCode()
    {
        $request = Ibos::app()->getRequest();
        $mobile = $request->getPost('mobile');
        $code = $request->getPost('code');
        $res = WebSite::getInstance()->fetch('Api/Api/checkCode', array(
            'mobile' => $mobile,
            'code' => $code,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        return $this->ajaxReturn($ajaxReturn);
    }

    public function actionCheckMobile()
    {
        $request = Ibos::app()->getRequest();
        $mobile = $request->getPost('mobile');
        $res = WebSite::getInstance()->fetch('Api/Api/checkMobile', array(
            'mobile' => $mobile,
        ), 'post');
        $ajaxReturn = $this->ajaxReturnArray($res);
        return $this->ajaxReturn($ajaxReturn);
    }

    private function ajaxReturnArray($res)
    {
        if (is_array($res)) {
            return array(
                'isSuccess' => false,
                'msg' => $res['error'],
            );
        } else {
            $result = CJSON::decode($res);
            return array(
                'isSuccess' => $result['isSuccess'],
                'msg' => isset($result['msg']) ? $result['msg'] : '',
                'data' => isset($result['data']) ? $result['data'] : array(),
            );
        }
    }

    public function actionLocationWx()
    {
        $request = Ibos::app()->request;
        $domain = $request->getPost('domain');
        $aeskey = Ibos::app()->setting->get('setting/aeskey');
        if (isset(Ibos::app()->user->param['uid'])) {
            //如果没有登录酷办公，就强制跳转到首页登录
            $uid = Ibos::app()->user->param['uid'];
        } else {
            Ibos::app()->user->setState('param', array());
            return $this->redirect($this->createUrl('wxbinding/index'));
        }
        $url = WebSite::getInstance()->build('Wxapi/Api/toWx', array(
            'state' => base64_encode(json_encode(array(
                'domain' => $domain,
                'uid' => $uid,
                'aeskey' => $aeskey,
                'version' => strtolower(implode(',', array(ENGINE, VERSION, VERSION_DATE)))
            )))
        ));
        $url .= '&' . rand(0, 999);
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => array(
                'url' => $url,
            ),
        ));
    }

}
