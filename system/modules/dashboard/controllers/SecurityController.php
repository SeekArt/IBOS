<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\IpBanned;
use application\modules\main\model\Setting;

class SecurityController extends BaseController
{

    public function actionSetup()
    {
        $formSubmit = Env::submitCheck('securitySubmit');
        if ($formSubmit) {
            $fields = array(
                'expiration', 'minlength',
                'mixed', 'errorlimit', 'errorrepeat',
                'errortime', 'autologin', 'allowshare',
                'timeout'
            );
            $updateList = array();
            foreach ($fields as $field) {
                if (!isset($_POST[$field])) {
                    $_POST[$field] = 0;
                }
                $updateList[$field] = intval($_POST[$field]);
            }

            if (intval($updateList['timeout']) == 0) {
                $this->error('请填写一个正确的大于0的超时时间值');
            }
            Setting::model()->updateSettingValueByKey('account', $updateList);
            Cache::update(array('setting'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array();
            $account = Setting::model()->fetchSettingValueByKey('account');
            $data['account'] = StringUtil::utf8Unserialize($account);
            $this->render('setup', $data);
        }
    }

    /**
     * 渲染 log 视图动作
     */
    public function actionLog()
    {
        $data['actions'] = Ibos::getLangSource('dashboard.actions');
        $data['archive'] = Log::getAllArchiveTableId();
        $this->render('log', $data);
    }

    /**
     * 获取后台访问日志列表数据
     * @return json
     */
    public function actionGetAdmincpLogList()
    {
        $highSearch = Env::getRequest('highSearch');
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $draw = Env::getRequest('draw');
        $filterAct = $highSearch['filteract'];
        $timeScope = $highSearch['timescope'];
        $startTime = $highSearch['starttime'];
        $endTime = $highSearch['endtime'];
        $condition = "`level` = 'admincp'";
        if (!empty($timeScope) && !empty($startTime) && !empty($endTime)) {
            $startTime = $timeScope . '-' . $startTime;
            $endTime = $timeScope . '-' . $endTime;
            $condition .= sprintf(" AND `logtime` > %d AND `logtime` < %d", strtotime($startTime), strtotime($endTime) + 86399);
        }
        if (!empty($filterAct)) {
            $condition .= sprintf(" AND `category` = 'module.dashboard.%s'", $filterAct);
        }
        $this->ajaxReturn(array(
            'data' => Log::fetchAllByList(0, $condition, $length, $start),
            'draw' => $draw,
            'recordsFiltered' => Log::countByTableId(0, $condition),
        ));
    }

    /**
     * 获取密码错误日志列表数据
     * @return json
     */
    public function actionGetIllegalLogList()
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $draw = Env::getRequest('draw');
        $condition = "`level` = 'illegal'";
        $this->ajaxReturn(array(
            'data' => Log::fetchAllByList(0, $condition, $length, $start),
            'draw' => $draw,
            'recordsFiltered' => Log::countByTableId(0, $condition),
        ));
    }

    /**
     * 获取前台登录日志列表数据
     * @return json
     */
    public function actionGetLoginLogList()
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $draw = Env::getRequest('draw');
        $condition = "`level` = 'login'";
        $this->ajaxReturn(array(
            'data' => Log::fetchAllByList(0, $condition, $length, $start),
            'draw' => $draw,
            'recordsFiltered' => Log::countByTableId(0, $condition),
        ));
    }

    public function actionIp()
    {
        $formSubmit = Env::submitCheck('securitySubmit');
        if ($formSubmit) {
            if ($_POST['act'] == '') { // act为空，默认操作ip地址
                if (isset($_POST['ip'])) {
                    // 新增处理
                    foreach ($_POST['ip'] as $new) {
                        if ($new['ip1'] != '' && $new['ip2'] != '' && $new['ip3'] != '' && $new['ip4'] != '') {
                            $own = 0;
                            $ip = explode('.', Ibos::app()->setting->get('clientip'));
                            for ($i = 1; $i <= 4; $i++) {
                                if (!is_numeric($new['ip' . $i]) || $new['ip' . $i] < 0) {
                                    $new['ip' . $i] = -1;
                                    $own++;
                                } elseif ($new['ip' . $i] == $ip[$i - 1]) {
                                    $own++;
                                }
                                $new['ip' . $i] = intval($new['ip' . $i]);
                            }
                            if ($own == 4) {
                                $this->error(Ibos::lang('Ipban illegal'));
                            }
                            $expiration = TIMESTAMP + $new['validitynew'] * 86400;
                            $new['admin'] = Ibos::app()->user->username;
                            $new['dateline'] = TIMESTAMP;
                            $new['expiration'] = $expiration;
                            IpBanned::model()->add($new);
                        }
                    }
                }
                // 编辑处理
                if (isset($_POST['expiration'])) {
                    $userName = Ibos::app()->user->username;
                    foreach ($_POST['expiration'] as $id => $expiration) {
                        IpBanned::model()->updateExpirationById($id, strtotime($expiration), $userName);
                    }
                }
            } else if ($_POST['act'] == 'del') { //删除选中
                if (is_array($_POST['id'])) {
                    IpBanned::model()->deleteByPk($_POST['id']);
                }
            } else if ($_POST['act'] == 'clear') { //清空
                $command = Ibos::app()->db->createCommand();
                $command->delete('{{ipbanned}}');
            }
            Cache::update(array('setting', 'ipbanned'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array();
            $lists = IpBanned::model()->fetchAllOrderDateline();
            $list = array();
            foreach ($lists as $banned) {
                for ($i = 1; $i <= 4; $i++) {
                    if ($banned["ip{$i}"] == -1) {
                        $banned["ip{$i}"] = '*';
                    }
                }
                $banned['dateline'] = date('Y-m-d', $banned['dateline']);
                $banned['expiration'] = date('Y-m-d', $banned['expiration']);
                $displayIp = "{$banned['ip1']}.{$banned['ip2']}.{$banned['ip3']}.{$banned['ip4']}";
                $banned['display'] = $displayIp;
                $banned['scope'] = Convert::convertIp($displayIp);
                $list[] = $banned;
            }
            $data['list'] = $list;
            $this->render('ip', $data);
        }
    }

}
