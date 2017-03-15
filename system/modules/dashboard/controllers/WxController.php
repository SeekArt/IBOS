<?php

/**
 * WxCenterController.class.file
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 微信企业号应用中心控制器
 *
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: WxCenterController.php 2052 2014-09-22 10:05:11Z gzhzh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;
use application\core\utils\WebSite;
use application\modules\dashboard\utils\Wx;
use application\modules\main\model\Setting;
use application\modules\message\core\wx\WxApi;
use CJSON;

class WxController extends BaseController
{

    protected $isBinding = false;
    protected $msg = '';
    protected $wxqyInfo = array();

    public function init()
    {
        parent::init();
        //通过aeskey向官网发起请求，查询是否有对应记录
        $this->chkBinding();
        // if (true === $this->isBinding && !empty($this->wxqyInfo['isLogin'])) {
        //     $this->chkDeptAuth();
        // }
    }

    /**
     * 没有绑定时显示的视图（旧版！新版没有这个视图，这个东西留在这里就可以了）
     * @return view
     */
    protected function unbindRender()
    {
        return $this->render('application.modules.dashboard.views.wx.unbindtip', array('msg' => $this->msg));
    }

    /**
     * 没有部门权限时显示的视图
     * @param 视图参数 $param
     * @return view
     */
    protected function noDeptRender($param)
    {
        $this->render('application.modules.dashboard.views.wx.nodept', $param);
        die;
    }

    /**
     * 通过aeskey向官网发起请求，查询是否有对应记录
     */
    protected function chkBinding()
    {
        $aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
        $url = 'Api/WxCorp/isBinding';
        $res = WebSite::getInstance()->fetch($url, array('aeskey' => $aeskey));
        $isLogin = false;
        if (!is_array($res)) {
            $result = CJSON::decode($res, true);
            switch ($result['type']) {
                case 1 :
                    Setting::model()->updateSettingValueByKey('corpid', $result['corpid']);
                    Setting::model()->updateSettingValueByKey('qrcode', urldecode($result['qrcode']));
                    $this->isBinding = true;
                    $this->wxqyInfo['name'] = $result['name'];
                    $this->wxqyInfo['corpid'] = $result['corpid'];
                    $this->wxqyInfo['logo'] = $result['logo'];
                    $this->wxqyInfo['qrcode'] = $result['qrcode'];
                    $this->wxqyInfo['mobile'] = $result['mobile'];
                    $this->wxqyInfo['app'] = $result['app'];
                    $this->wxqyInfo['uid'] = $result['uid'];
                    $isLogin = Ibos::app()->user->mobile == $this->wxqyInfo['mobile'];
                    //绑定的时候，如果绑定手机号和当前手机号一致，直接判定为登录
                    if (false === $isLogin) {
                        $param = Ibos::app()->user->param;
                        if (isset($param['wxqyInfo']) && !empty($param['wxqyInfo']['mobile'])) {
                            $isLogin = Ibos::app()->user->mobile == $param['wxqyInfo']['mobile'];
                        }
                    }
                    break;
                case 2 :
                    $this->isBinding = false;
                    $this->msg = $result['msg'];
                    break;
                case 3 :
                    WxApi::getInstance()->resetCorp();
                    $this->isBinding = false;
                    $this->msg = $result['msg'];
                    break;
            }
            if (false === $isLogin) {
                $param = Ibos::app()->user->param;
                if (isset($param['wxqyInfo']) && !empty($param['wxqyInfo']['mobile'])) {
                    $isLogin = true;
                }
            }

            //如果退出，则设置param为空
            if (false === $isLogin) {
                Ibos::app()->user->setState('param', array());
            }
        }
        
        $this->wxqyInfo['isLogin'] = $isLogin;
    }

    /**
     * 检查是否有部门权限
     * 这里要说明一下，判断是否有通讯录应用的方法其实不是下面的那段代码去判断的，但是
     * 通讯录应用的最终目的是判断是否有部门的权限，用户其实可以设置成安装了通讯录应用，但是不给权限（第一种情况）
     * 还有一种情况是，现在并没有把通讯录单独分出一个套件来，好多旧的用户还是用旧的套件的通讯录（第二种情况）
     * 这两种情况下，直接去拿授权的应用列表去判断是否有通讯录应用都是不准确的
     * 而且通过授权的应用去判断，在官网还得加接口以及更多的容错情况（第二种情况）
     * 因此直接请求微信拿所有部门的列表（简易版的部门列表接口，实测即使是万级的部门数据也是几秒钟）
     * 通过部门列表是不是空的去判断有没有权限是更加【准确】和【粗暴】的方式
     *
     * 上面的说明的接口使用的是获取企业号部门列表的接口，现在换成了获取部门成员列表的接口
     * 原因很简单，微信给的接口以前是会提示没有权限的，现在改了（明显改错了，没有权限就不该返回数据，但是现在返回了）
     * @return null or view
     */
    protected function chkDeptAuth()
    {

        $aeskey = Wx::getInstance()->getAeskey();
        $url = WebSite::getInstance()->build('Api/Wxsync/syncDeptUserSimple', array('aeskey' => $aeskey));
        // 优先检测微信是否授权部门，如果没有直接提示没有权限
        $return = WxApi::getInstance()->getDeptUser($url);
        $hasAuth = true;
        if (empty($return)) {
            $hasAuth = false;
        }
        if (false === $hasAuth) {
            return $this->noDeptRender(array());
        }
    }

}
