<?php

/**
 * CoController.class.file
 *
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 */
/**
 * 酷办公中心控制器
 *
 * @package application.modules.dashboard.controllers
 * @author mumu <2317216477@qq.com>
 *
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;
use application\modules\dashboard\utils\CoSync;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;

class CoController extends BaseController {

    /**
     * 绑定状态
     * @var bool
     */
    protected $isBinding = false;
    /**
     * 系统aeskey
     * @var
     */
    protected $aeskey;
    /**
     * 所绑定的企业信息
     * @var
     */
    protected $coinfo = null;
    /**
     * 酷办公登陆用户的企业列表
     * @var
     */
    protected $corpListRes;
    /**
     * 当前登录的酷办公用户信息
     */
    private $_coUser = null;

    public function init() {
        parent::init();
        $this->_coUser = Ibos::app()->user->getState( 'coUser' );
        $this->aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
        // 没有酷办公用户登陆，但有绑定，就显示绑定状态下的登陆页面
        $bind = $this->judgeFromApi();
        if (!empty($bind)) {
            $this->coinfo = $bind;
            $this->isBinding = true;
        }
        // 如果没有绑定，但登陆了
        if (!empty($this->_coUser)) {
            $this->chkBinding($this->_coUser['accesstoken']);
        }
        
    }

    /**
     * 如果没有绑定，则：
     * $this->isBinding、$this->accesstoken、$this->coInfo都是初始值
     */
    protected function chkBinding($accesstoken) {
        // todo:去线上拿绑定关系
        $this->corpListRes = $this->getCorpList($accesstoken);
        $whether = $this->whetherBinding($this->corpListRes, $accesstoken);
        if(false === $whether) {
            $this->isBinding = false;
        }else{
            $this->coinfo = $whether;
            $this->isBinding = true;
        }
    }

    /**
     * 通过accesstoken获取co用户的信息
     * 如果成功，认为用户验证成功
     * @param string $accesstoken
     * @return array or boolean 如果accesstoken有效，则返回用户信息数组，失败返回false
     */
    protected function getCoUser( $accesstoken ) {
        return CoApi::getInstance()->getUserInfo( $accesstoken );
    }

    /**
     * 根据用户 accesstoken 获取用户企业列表
     * 在新流程的 Index 动作中被用到
     * @param  string $accesstoken 用户的 accesstoken
     * @return array              渲染视图需要的参数
     */
    protected function getCorpList( $accesstoken ) {
        try{
            $corpListRes = CoSync::getCorpListByAccessToken( $accesstoken );
        }catch (\CException $e) {
            $this->error( $e->getMessage(), $this->createUrl( 'cobinding/login' ), array(), 3 );
        }
        return $corpListRes;
    }

    /**
     * 如果绑定了就返回企业信息
     * @param $corpListRes
     * @return bool
     */
    private function whetherBinding($corpListRes, $accesstoken) {
        $systemurl = Ibos::app()->request->getHostInfo();
        // 先判断当前登录用户的企业里有没有当前ibos
        foreach ($corpListRes['corpList'] as $k => $v){
            if($systemurl == $v['systemUrl'] && $this->aeskey == $v['aeskey']) {
                return $v;
            }else{
                continue;
            }
        }
        // 如果没有，则请求接口查看当前ibos绑定的企业，没有就返回false了
        $post = array(
            'aeskey' => $this->aeskey,
            'systemurl' => $systemurl,
        );
        $hasBind = CoApi::getInstance()->whetherBind( $accesstoken, $post );
        // 获取绑定关系失败
        if ( $hasBind['code'] != CodeApi::SUCCESS ) {
            $this->error( $hasBind['message'], $this->createUrl( 'cobinding/login' ), array(), 3 );
        }
        if (!empty($hasBind['data'])) {
            $corpinfo = $this->handleData($hasBind['data']);
            return $corpinfo;
        }
        return false;
    }

    /**
     * @param $data
     */
    private function handleData(&$data) {
        $keys = array('logo', 'name', 'shortname');
        foreach ($keys as $key) {
            $data['corp'.$key] = $data[$key];
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * 从api那边判断是否绑定
     * @return bool
     */
    protected function judgeFromApi(){
        $systemurl = Ibos::app()->request->getHostInfo();
        // 如果没有，则请求接口查看当前ibos绑定的企业，没有就返回false了
        $post = array(
            'aeskey' => $this->aeskey,
            'systemurl' => $systemurl,
        );
        $hasBind = CoApi::getInstance()->judgeBind($post);
        // 获取绑定关系失败
        if ( $hasBind['code'] != CodeApi::SUCCESS ) {
            $this->error( $hasBind['message'], $this->createUrl( 'cobinding/login' ), array(), 3 );
        }
        if (!empty($hasBind['data'])) {
            $corpinfo = $this->handleData($hasBind['data']);
            return $corpinfo;
        }
        return false;
    }

}
