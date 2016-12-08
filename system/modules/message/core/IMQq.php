<?php

/**
 * IMQq class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * IM组件QQ类，实现ICIM里的抽象方法并提供推送，同步等功能
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core
 * @version $Id$
 */

namespace application\modules\message\core;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use CJSON;
use Exception;

class IMQq extends IM
{

    /**
     * 同步标记，是增加还是删除
     * @var type
     */
    protected $syncFlag;

    /**
     * 检查企业qq绑定是否可用。只需检查初始化COM组件即可
     * @return boolean
     */
    public function check()
    {
        if ($this->isEnabled('open')) {
            $config = $this->getConfig();
            if (isset($config['checkpass']) && $config['checkpass'] == '1') {
                return true;
            } else {
                if (!empty($config['id']) && !empty($config['token'])) {
                    $res = $this->getApi()->getCorBase();
                    if (!is_array($res)) {
                        $info = CJSON::decode($res, true);
                        if (isset($info['ret']) && $info['ret'] == '0') {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * 统一推送接口
     */
    public function push()
    {
        $type = $this->getPushType();
        if ($type == 'notify' && $this->isEnabled('push/note')) {
            $this->pushNotify();
        } elseif ($type == 'pm' && $this->isEnabled('push/msg')) {
            //$this->pushMsg();
        }
    }

    public function syncOrg()
    {
        ;
    }

    /**
     * 同步用户
     */
    public function syncUser()
    {
        $oplist = array('confirm', 'sync');
        $op = Env::getRequest('op');
        if (!in_array($op, $oplist)) {
            $op = 'confirm';
        }
        $syncUsers = User::model()->fetchAllByUids($this->getUid());
        $flag = $this->getSyncFlag();
        if ($op == 'confirm') {
            if ($flag != 1) {
                $bindingUser = $this->getBindingUser($this->getUid());
                if (!empty($bindingUser)) {
                    $userNames = explode(',', User::model()->fetchRealnamesByUids($bindingUser));
                    $uids = $bindingUser;
                } else {
                    $exit = <<<EOT
			<script>parent.Ui.tip('无需同步','success');parent.Ui.closeDialog();</script>
EOT;
                    Env::iExit($exit);
                }
            } else {
                $userNames = Convert::getSubByKey($syncUsers, 'realname');
                $uids = $this->getUid();
            }
            $properties = array(
                'usernames' => $userNames,
                'uid' => implode(',', $uids),
                'flag' => $flag
            );
            Ibos::app()->getController()->renderPartial('application.modules.dashboard.views.user.qqsync', $properties);
        } else if ($op == 'sync') {
            $count = count($syncUsers);
            if ($flag == 1) {
                $res = $this->addUser($syncUsers);
            } else {
                $res = $this->setUserStatus($syncUsers, $flag);
            }
            if ($count >= 1 && $count == $res) {
                $exit = <<<EOT
			<script>parent.Ui.tip('同步完成','success');parent.Ui.closeDialog();</script>
EOT;
            } else {
                $errors = $this->getError(self::ERROR_SYNC);
                $exit = implode(',', array_unique($errors));
            }
            Env::iExit($exit);
        }
    }

    /**
     * 增加用户到企业QQ
     * @param array $users 要增加的数组
     * @return int
     */
    private function addUser($users)
    {
        $count = 0;
        try {
            foreach ($users as $user) {
                $account = Convert::getPY($user['username']);
                $data = array(
                    'account' => $account,
                    'name' => $user['realname'],
                    'gender' => $user['gender'] == 0 ? 2 : 1,
                    'mobile' => $user['mobile']
                );
                $result = $this->getApi()->addAccount($data);
                if (!is_array($result)) {
                    $res = CJSON::decode($result, true);
                    if (isset($res['ret'])) {
                        if ($res['ret'] == 0) {
                            $this->setBinding($user['uid'], implode(',', $res['data']));
                            $count++;
                        } else {
                            $this->setError($res['msg'], self::ERROR_SYNC);
                        }
                    }
                }
            }
            return $count;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage(), self::ERROR_SYNC);
            return 0;
        }
    }

    /**
     * 设置用户状态
     * @param array $users
     * @param integer $flag
     * @return int
     */
    private function setUserStatus($users, $flag)
    {
        if ($flag == 0) {
            $flag = 2;
        } else {
            $flag = 1;
        }
        $count = 0;
        try {
            foreach ($users as $user) {
                $openId = UserBinding::model()->fetchBindValue($user['uid'], 'bqq');
                $re = $this->getApi()->setStatus($openId, $flag);
                if (!is_array($re)) {
                    $res = CJSON::decode($re, true);
                    if (isset($res['ret'])) {
                        if ($res['ret'] == 0) {
                            $count++;
                        } else {
                            $this->setError($res['msg'], self::ERROR_SYNC);
                        }
                    }
                }
            }
            return $count;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage(), self::ERROR_SYNC);
            return 0;
        }
    }

    /**
     * 推送 私信到IM
     * @return boolean
     */
    protected function pushMsg()
    {

    }

    /**
     * 推送提醒
     * @return type
     */
    protected function pushNotify()
    {
        $openIds = UserBinding::model()->fetchValuesByUids($this->getUid(), 'bqq');
        if (!empty($openIds)) {
            try {
                $unit = Ibos::app()->setting->get('setting/unit/shortname');
                $content = strip_tags($this->getMessage(), '<a>');
                $data = array(
                    'window_title' => Ibos::lang('From unit', 'default', array('{unit}' => $unit)),
                    'tips_title' => Ibos::lang('System notify', 'default'),
                    'tips_content' => $content,
                    'receivers' => implode(',', $openIds),
                    'to_all' => 0,
                    'receive_type' => 0,
                    'display_time' => 0,
                    'need_verify' => $this->isEnabled('sso') ? 1 : 0
                );
                if (!empty($this->url)) {
                    $data['tips_url'] = $this->getUrl();
                }
                $this->getApi()->sendNotify($data);
            } catch (Exception $exc) {
                return;
            }
        }
    }

    /**
     * 获取企业QQAPI专用对象
     * @staticvar object $api
     * @return \BQQApi
     */
    public function getApi()
    {
        static $api = null;
        if (empty($api)) {
            $config = $this->getConfig();
            $properties = array(
                'company_id' => $config['id'],
                'company_token' => $config['token'],
                'app_id' => $config['appid'],
                'client_ip' => Ibos::app()->setting->get('clientip')
            );
            $api = new BQQApi($properties);
        }
        return $api;
    }

    /**
     * 设置企业绑定信息，一般是用户ID与openid
     * @param integer $uid 用户ID
     * @param string $openId 32位的MD5
     */
    protected function setBinding($uid, $openId)
    {
        if (!UserBinding::model()->getIsBinding($uid, 'bqq')) {
            UserBinding::model()->add(array('uid' => $uid, 'bindvalue' => $openId, 'app' => 'bqq'));
        } else {
            UserBinding::model()->updateAll(array('bindvalue' => $openId), sprintf("uid = %d AND app = 'bqq'", $uid));
        }
    }

    /**
     * 查看是否有用户已经绑定企业QQ
     * @param array $uids 用户ID数组
     * @return boolean
     */
    protected function getBindingUser($uids)
    {
        $result = array();
        foreach ($uids as $uid) {
            if (UserBinding::model()->getIsBinding($uid, 'bqq')) {
                $result[] = $uid;
            }
        }
        return $result;
    }

}
