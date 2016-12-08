<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\message\model\NotifySms;
use application\modules\message\utils\Message;
use application\modules\user\model\User;
use CJSON;
use CHtml;

class SmsController extends BaseController
{

    public function actionSetup()
    {
        // 是否提交？
        $formSubmit = Env::submitCheck('smsSubmit');
        if ($formSubmit) {
            if (isset($_POST['enabled'])) {
                $enabled = 1;
            } else {
                $enabled = 0;
            }
            $interface = $_POST['interface'];
            $setup = $_POST['interface' . $interface];
            Setting::model()->updateSettingValueByKey('smsenabled', (int)$enabled);
            Setting::model()->updateSettingValueByKey('smsinterface', (int)$interface);
            Setting::model()->updateSettingValueByKey('smssetup', $setup);
            Cache::update(array('setting'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array();
            $smsLeft = 0;
            $arr = Setting::model()->fetchSettingValueByKeys('smsenabled,smsinterface,smssetup');
            $arr['smssetup'] = StringUtil::utf8Unserialize($arr['smssetup']);
            if (is_array($arr['smssetup'])) {
                // 接口1：北程科技
                if ($arr['smsinterface'] == '1') {
                    $accessKey = $arr['smssetup']['accesskey'];
                    $secretKey = $arr['smssetup']['secretkey'];
                    $url = "http://sms.bechtech.cn/Api/getLeft/data/json?accesskey={$accessKey}&secretkey={$secretKey}";
                    $return = File::fileSockOpen($url);
                    if ($return) {
                        $return = CJSON::decode($return, true);
                        if (isset($return['result'])) {
                            $smsLeft = $return['result'];
                        }
                    }
                }
            }
            /**
             * todo::下面这个？
             */
            $temp = Setting::model()->fetchSettingValueByKey('');
            $arr['setup'] = StringUtil::utf8Unserialize($temp);
            $data['setup'] = $arr;
            $data['smsLeft'] = $smsLeft;
            $this->render('setup', $data);
        }
    }

    /**
     *
     */
    public function actionManager()
    {
        $this->render('manager');
    }

    /**
     * 获取手机短信发送管理列表数据方法
     * @return json
     */
    public function actionGetSmsManagerList()
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $draw = Env::getRequest('draw');
        $condition = $this->handleGetSmsCondition();
        $data = array_map(function ($sms) {
            return array(
                'id' => $sms['id'],
                'fromname' => User::model()->fetchRealnameByUid($sms['uid']),
                'toname' => User::model()->fetchRealnameByUid($sms['touid']),
                'content' => $sms['content'],
                'status' => $sms['return'],
                'sendtime' => $sms['ctime'],
            );
        }, NotifySms::model()->fetchAll(array(
            'condition' => $condition,
            'limit' => $length,
            'offset' => $start,
            'order' => 'ctime DESC',
        )));
        $this->ajaxReturn(array(
            'data' => $data,
            'draw' => $draw,
            'recordsFiltered' => NotifySms::model()->count($condition),
        ));
    }

    /**
     * 处理 datatable 插件搜索条件并返回对应的 WHERE 语句
     * 如果手机号格式不正确，将有一个 ajax 错误信息输出
     * @return string WHERE 语句
     */
    private function handleGetSmsCondition()
    {
        $condition = '';
        $type = Env::getRequest('type');
        $search = Env::getRequest('search');
        if ($type === 'search') {
            $searchType = Env::getRequest('searchType');
            $begin = Env::getRequest('begin');
            $end = Env::getRequest('end');
            $sender = Env::getRequest('sender');
            $recnumber = Env::getRequest('recnumber');
            $content = Env::getRequest('content');
            if (!empty($searchType)) {
                $condition .= sprintf("`return` = %d", $searchType);
            }
            if (!empty($begin)) {
                $and = !empty($condition) ? ' AND ' : '';
                $condition .= sprintf("%s`ctime` > %d", $and, strtotime($begin));
            }
            if (!empty($end)) {
                $and = !empty($condition) ? ' AND ' : '';
                $condition .= sprintf("%s`ctime` < %d", $and, strtotime($end));
            }
            if (!empty($sender)) {
                $temp = StringUtil::getUid($sender);
                $sender = $temp[0];
                $and = !empty($condition) ? ' AND ' : '';
                $condition .= sprintf("%s`uid` = %d", $and, $sender);
            }
            if (!empty($recnumber)) {
                // if ( !StringUtil::isMobile( $recnumber ) ) {
                //     $this->ajaxReturn( array( 'isSuccess' => false, 'msg' => '手机号格式不正确' ) );
                // }
                $and = !empty($condition) ? ' AND ' : '';
                $condition .= sprintf("%s`mobile` = %s", $and, $recnumber);
            }
            if (!empty($content)) {
                $and = !empty($condition) ? ' AND ' : '';
                $condition .= $and . "`content` LIKE '%" . CHtml::encode($content) . "%'";
            }
        } else if (!empty($search['value'])) {
            $condition .= "`content` LIKE '%" . CHtml::encode($search['value']) . "%'";
        }
        return $condition;
    }

    /**
     *
     */
    public function actionAccess()
    {
        // 是否提交？
        $formSubmit = Env::submitCheck('smsSubmit');
        if ($formSubmit) {
            $enabledModule = !empty($_POST['enabled']) ? explode(',', $_POST['enabled']) : array();
            Setting::model()->updateSettingValueByKey('smsmodule', $enabledModule);
            Cache::update(array('setting'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array(
                'smsModule' => Ibos::app()->setting->get('setting/smsmodule'),
                'enableModule' => Module::model()->fetchAllNotCoreModule()
            );
            $this->render('access', $data);
        }
    }

    /**
     *
     */
    public function actionDel()
    {
        $id = Env::getRequest('id');
        $id = StringUtil::filterStr($id);
        NotifySms::model()->deleteAll("FIND_IN_SET(id,'{$id}')");
        $this->ajaxReturn(array('isSuccess' => true));
    }

    /**
     *
     */
    public function actionExport()
    {
        $id = Env::getRequest('id');
        $id = StringUtil::filterStr($id);
        Message::exportSms($id);
    }

}
