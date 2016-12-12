<?php

/**
 * 主模块Session处理
 * @package application.modules.main.components
 * @todo 缓存的增删查改方法
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$
 */

namespace application\modules\main\components;

use application\core\utils as util;
use application\modules\main\model as MainModel;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\model as UserModel;
use application\modules\user\utils\User as UserUtil;
use CHttpSession;

class Session extends CHttpSession
{

    /**
     * session id
     * @var mixed
     */
    public $sid = null;

    /**
     * 当前session变量数组
     * @var mixed
     */
    public $var;

    /**
     * 新用户标识
     * @var boolean
     */
    public $isNew = false;

    /**
     * 上一个session
     * @var array
     */
    private $old = array('sid' => '', 'ip' => '', 'uid' => 0);

    /**
     * 新用户初始化session数组
     * @var array
     */
    private $newGuest = array(
        'sid' => 0, 'ip1' => 0, 'ip2' => 0,
        'ip3' => 0, 'ip4' => 0, 'uid' => 0,
        'username' => '', 'groupid' => 0, 'invisible' => 0,
        'action' => 0, 'lastactivity' => 0, 'lastolupdate' => 0
    );

    /**
     * session组件加载调用的方法
     * @param string $sid sessionId
     * @param string $ip ip地址
     * @param integer $uid 用户id
     */
    public function load($sid = '', $ip = '', $uid = 0)
    {
        $this->old = array('sid' => $sid, 'ip' => $ip, 'uid' => $uid);
        $this->var = $this->newGuest;
        if (!empty($ip)) {
            $this->initialize($sid, $ip, $uid);
        }
    }

    /**
     * 初始化方法，如果已经存在sid,查找数据库记录。为空则创建一个默认session数组,返回
     * 给全局变量
     * @param string $sid session id
     * @param string $ip 当前用户ip地址
     * @param integer $uid 用户id
     */
    public function initialize($sid, $ip, $uid)
    {
        $this->old = array('sid' => $sid, 'ip' => $ip, 'uid' => $uid);
        $session = array();
        if ($sid) {
            $session = MainModel\Session::model()->fetchBySid($sid, $ip, $uid);
        }
        if (empty($session) || $session['uid'] != $uid) {
            $session = $this->create($ip, $uid);
        }
        $this->var = $session;
        $this->sid = $session['sid'];
    }

    /**
     * 创建一个默认session数组，赋值给var变量
     * @param type $ip
     * @param type $uid
     * @return type
     */
    public function create($ip, $uid)
    {
        $this->isNew = true;
        $this->var = $this->newGuest;
        $this->setKey('sid', util\StringUtil::random(6));
        $this->setKey('uid', $uid);
        $this->setKey('ip', $ip);
        // 如果是已登录用户，查找其是否可见状态
        if ($uid) {
            $this->setKey('invisible', UserUtil::getUserProfile('invisible'));
        }
        $this->setKey('lastactivity', time());
        $this->sid = $this->var['sid'];
        return $this->var;
    }

    /**
     * 内部set方法封装
     * @param string $key 键
     * @param string $value 值
     */
    public function setKey($key, $value)
    {
        if (isset($this->newGuest[$key])) {
            $this->var[$key] = $value;
        } elseif ($key == 'ip') {
            $ips = explode('.', $value);
            if (count($ips) == 4) {
                $this->setKey('ip1', $ips[0]);
                $this->setKey('ip2', $ips[1]);
                $this->setKey('ip3', $ips[2]);
                $this->setKey('ip4', $ips[3]);
            }
        }
    }

    /**
     * 内部get方法封装
     * @param string $key 键
     * @return string 值
     */
    public function getKey($key)
    {
        if (isset($this->newGuest[$key])) {
            return $this->var[$key];
        } elseif ($key == 'ip') {
            return $this->getKey('ip1') . '.' . $this->getKey('ip2') . '.' . $this->getKey('ip3') . '.' . $this->getKey('ip4');
        }
    }

    /**
     * 更新session
     * @staticvar boolean $updated
     * @return boolean 更新标识
     */
    public function updateSession()
    {
        static $updated = false;
        if (!$updated) {
            $global = util\Ibos::app()->setting->toArray();
            // 设置最后活动时间
            if (!util\Ibos::app()->user->isGuest) {
                if (isset($global['cookie']['ulastactivity'])) {
                    $userLastActivity = util\StringUtil::authCode($global['cookie']['ulastactivity'], 'DECODE');
                } else {
                    $userLastActivity = UserUtil::getUserProfile('lastactivity');
                    MainUtil::setCookie('ulastactivity', util\StringUtil::authCode($userLastActivity, 'ENCODE'), 31536000);
                }
            }
            //统计每个用户总共和当月的在线时间，本设置用以设定更新用户在线时间的时间频率。
            //例如设置为 10，则用户每在线 10 分钟更新一次记录。
            //本设置值越小，则统计越精确，但消耗资源越大。
            //建议设置为 5～30 范围内，0 为不记录用户在线时间
            $onlineTimeSpan = 10; //$global['setting']['oltimespan']; todo::后台设置后应读取全局变量
            $lastOnlineUpdate = $this->var['lastolupdate'];
            $onlineTimeOffset = $lastOnlineUpdate ? $lastOnlineUpdate : $userLastActivity;
            $allowUpdateOnlineTime = (TIMESTAMP - $onlineTimeOffset > $onlineTimeSpan * 60);
            // 更新在线时间
            if (!util\Ibos::app()->user->isGuest && $allowUpdateOnlineTime) {
                $updateStatus = UserModel\OnlineTime::model()->updateOnlineTime(util\Ibos::app()->user->uid, $onlineTimeSpan, $onlineTimeSpan, TIMESTAMP);
                if ($updateStatus === false) {
                    $onlineTime = new UserModel\OnlineTime();
                    $onlineTime->uid = util\Ibos::app()->user->uid;
                    $onlineTime->thismonth = $onlineTimeSpan;
                    $onlineTime->total = $onlineTimeSpan;
                    $onlineTime->lastupdate = $global['timestamp'];
                    $onlineTime->save();
                }
                $this->setKey('lastolupdate', TIMESTAMP);
            }
            // 在线状态
            $this->var['invisible'] = UserUtil::getUserProfile('invisible');
            // 赋值用户变量到var数组，然后更新
            foreach ($this->var as $key => $value) {
                if (util\Ibos::app()->user->hasState($key) && $key != 'lastactivity') {
                    $this->setKey($key, util\Ibos::app()->user->$key);
                }
            }

            util\Ibos::app()->session->update();
            if (!util\Ibos::app()->user->isGuest) {
                $updateStatusField = array('lastip' => $global['clientip'], 'lastactivity' => TIMESTAMP, 'lastvisit' => TIMESTAMP, 'invisible' => 1);
                if (TIMESTAMP - $userLastActivity > 21600) {
                    if ($onlineTimeSpan && TIMESTAMP - $userLastActivity > 43200) {
                        $onlineTime = UserModel\OnlineTime::model()->fetchByPk(util\Ibos::app()->user->uid);
                        UserModel\UserCount::model()->updateByPk(util\Ibos::app()->user->uid, array('oltime' => round(intval($onlineTime['total']) / 60)));
                    }
                    MainUtil::setCookie('ulastactivity', util\StringUtil::authCode(TIMESTAMP, 'ENCODE'), 31536000);
                    UserModel\UserStatus::model()->updateByPk(util\Ibos::app()->user->uid, $updateStatusField);
                }
            }
            // 切换session更新状态标识,以免重复更新
            $updated = true;
        }
        return $updated;
    }

    /**
     * 数据层更新session
     */
    public function update()
    {
        if ($this->sid !== null) {
            if ($this->isNew) {
                $this->delete();
                MainModel\Session::model()->add($this->var);
            } else {
                if (IN_DASHBOARD) {
                    MainUtil::setCookie('lastactivity', TIMESTAMP);
                }
                MainModel\Session::model()->updateByPk($this->var['sid'], $this->var);
            }
            util\Ibos::app()->setting->set('session', $this->var);
            MainUtil::setCookie('sid', $this->sid, 86400);
        }
    }

    /**
     * 删除session
     * @return boolean
     */
    public function delete()
    {
        return MainModel\Session::model()->deleteBySession($this->var);
    }

}
