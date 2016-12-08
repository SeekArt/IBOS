<?php

namespace application\core\utils;

use application\modules\dashboard\model\CreditRuleLog;
use application\modules\dashboard\model\CreditRuleLogField;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserGroup;
use application\modules\user\utils\User as UserUtil;

class Credit extends System
{

    private $_coef = 1;
    private $_extraSql = array();

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    public function setExtraSql($extra)
    {
        $this->_extraSql = $extra;
    }

    /**
     * 积分执行函数
     * @param string $action 积分模块
     * @param integer $uid 用户ID
     * @param string $needle 防重复
     * @param integer $coef 积分放大位数（乘以）
     * @param integer $update 是否及时更新数据表
     * @return array 积分规则
     */
    public function execRule($action, $uid = 0, $needle = '', $coef = 1, $update = 1)
    {
        $this->_coef = $coef;
        $uid = intval($uid);
        $rule = $this->getRule($action); //获取积分规则
        $updateCredit = false;
        $timestamp = TIMESTAMP;
        $enabled = false;
        //积分一共允许有5条规则
        if ($rule) {
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($rule['extcredits' . $i])) {
                    $enabled = true;
                    break;
                }
            }
        }

        if ($enabled) {
            $ruleLog = $this->getRuleLog($rule['rid'], $uid);
            if ($ruleLog && $rule['norepeat']) {
                $ruleLog = array_merge($ruleLog, $this->getCheckLogByClId($ruleLog['clid'], $uid));
                $ruleLog['norepeat'] = $rule['norepeat'];
            }
            if ($rule['rewardnum'] && $rule['rewardnum'] < $coef) {
                $coef = $rule['rewardnum'];
            }
            if (empty($ruleLog)) {
                $logArr = array(
                    'uid' => $uid,
                    'rid' => $rule['rid'],
                    'total' => $coef,
                    'cyclenum' => $coef,
                    'dateline' => $timestamp
                );
                if (in_array($rule['cycletype'], array(2))) {
                    $logArr['starttime'] = $timestamp;
                } else {
                    $logArr['starttime'] = 0;
                }
                $logArr = $this->addLogArr($logArr, $rule, false);
                if ($update) {
                    $clid = CreditRuleLog::model()->add($logArr, true);
                    if ($rule['norepeat']) {
                        $ruleLog['isnew'] = 1;
                        $ruleLog['clid'] = $clid;
                        $ruleLog['uid'] = $uid;
                        $ruleLog['norepeat'] = $rule['norepeat'];
                        $this->updateCheating($ruleLog, $needle, true);
                    }
                }
                $updateCredit = true;
            } else {
                $newCycle = false;
                $logArr = array();
                switch ($rule['cycletype']) {
                    case 1: // 一次
                        break;
                    case 2: //每小时
                        $nextCycle = 0;
                        if ($ruleLog['starttime']) {
                            if ($rule['cycletype'] == 2) {
                                $start = strtotime(Convert::formatDate($ruleLog['starttime'], 'Y-m-d H:00:00'));
                                $nextCycle = $start + $rule['cycletime'] * 3600;
                            } else {
                                $nextCycle = $ruleLog['starttime'] + $rule['cycletime'] * 60;
                            }
                        }
                        if ($timestamp <= $nextCycle && $ruleLog['cyclenum'] < $rule['rewardnum']) {
                            if ($rule['norepeat']) {
                                $repeat = $this->checkCheating($ruleLog, $needle, $rule['norepeat']);
                                if ($repeat && !$newCycle) {
                                    return false;
                                }
                            }
                            if ($rule['rewardnum']) {
                                $remain = $rule['rewardnum'] - $ruleLog['cyclenum'];
                                if ($remain < $coef) {
                                    $coef = $remain;
                                }
                            }
                            $logArr = array(
                                'cyclenum' => "cyclenum=cyclenum+'{$coef}'",
                                'total' => "total=total+'{$coef}'",
                                'dateline' => "dateline='{$timestamp}'"
                            );
                            $updateCredit = true;
                        } elseif ($timestamp >= $nextCycle) {
                            $newCycle = true;
                            $logArr = array(
                                'cyclenum' => "cyclenum={$coef}",
                                'total' => "total=total+'{$coef}'",
                                'dateline' => "dateline='{$timestamp}'",
                                'starttime' => "starttime='{$timestamp}'",
                            );
                            $updateCredit = true;
                        }
                        break;
                    case 3: // 每天
                        if ($rule['cycletype'] == 3) {
                            $today = strtotime(date('Y-m-d', $timestamp));
                            if ($ruleLog['dateline'] < $today && $rule['rewardnum']) {
                                $ruleLog['cyclenum'] = 0;
                                $newCycle = true;
                            }
                        }
                        if (empty($rule['rewardnum']) || $ruleLog['cyclenum'] < $rule['rewardnum']) {
                            if ($rule['norepeat']) {
                                $repeat = $this->checkCheating($ruleLog, $needle, $rule['norepeat']);
                                if ($repeat && !$newCycle) {
                                    return false;
                                }
                            }
                            if ($rule['rewardnum']) {
                                $remain = $rule['rewardnum'] - $ruleLog['cyclenum'];
                                if ($remain < $coef) {
                                    $coef = $remain;
                                }
                            }
                            $cyclenunm = $newCycle ? $coef : "cyclenum+'$coef'";
                            $logArr = array(
                                'cyclenum' => "cyclenum={$cyclenunm}",
                                'total' => "total=total+'{$coef}'",
                                'dateline' => "dateline='{$timestamp}'"
                            );
                            $updateCredit = true;
                        }
                        break;
                }
                if ($update) {
                    if ($rule['norepeat'] && $needle) {
                        $this->updateCheating($ruleLog, $needle, $newCycle);
                    }
                    if ($logArr) {
                        $logArr = $this->addLogArr($logArr, $rule, true);
                        CreditRuleLog::model()->increase($ruleLog['clid'], $logArr);
                    }
                }
            }
        }
        if ($update && ($updateCredit || $this->_extraSql)) {
            if (!$updateCredit) {
                $extcredits = $this->getExtCredits();
                for ($i = 1; $i <= 5; $i++) {
                    if (isset($extcredits[$i]) && !empty($extcredits[$i])) {
                        $rule['extcredits' . $i] = 0;
                    }
                }
            }
            $this->updateCreditByRule($rule, $uid, $coef);
        }
        $rule['updateCredit'] = $updateCredit;
        //返回积分规则前，将用户缓存表对应的用户积分更新
        UserUtil::wrapUserInfo($uid, true, true, true);
        return $rule;
    }

    public function updateCreditByRule($rule, $uids = 0, $coef = 1)
    {
        $this->_coef = intval($coef);
        $uids = $uids ? $uids : intval(Ibos::app()->user->uid);
        $rule = is_array($rule) ? $rule : $this->getRule($rule);
        $creditArr = array();
        $updateCredit = false;
        $extCredits = $this->getExtCredits();
        for ($i = 1; $i <= 5; $i++) {
            if (isset($extCredits[$i]) && !empty($extCredits[$i])) {
                $creditArr['extcredits' . $i] = intval($rule['extcredits' . $i]) * $this->_coef;
                $updateCredit = true;
            }
        }
        if ($updateCredit || $this->_extraSql) {
            $this->updateUserCount($creditArr, $uids, is_array($uids) ? false : true, $this->_coef > 0 ? urldecode($rule['rulenameuni']) : '');
        }
    }

    /**
     *
     * @param type $creditArr
     * @param type $uids
     * @param type $checkGroup
     * @param type $ruletxt
     */
    public function updateUserCount($creditArr, $uids = 0, $checkGroup = true, $ruletxt = '')
    {
        if (Ibos::app()->user->isGuest) {
            return;
        }
        $setting = Ibos::app()->setting->toArray();
        if (!$uids) {
            $uids = intval(Ibos::app()->user->uid);
        }
        $uids = is_array($uids) ? $uids : array($uids);
        if ($uids && ($creditArr || $this->_extraSql)) {
            if ($this->_extraSql) {
                $creditArr = array_merge($creditArr, $this->_extraSql);
            }
            $sql = array();
            $allowkey = array(
                'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5',
                'oltime', 'attachsize'
            );
            $creditRemind = $setting['setting']['creditremind'] && Ibos::app()->user->uid && $uids == array(Ibos::app()->user->uid);
            if ($creditRemind) {
                if (!isset($setting['cookiecredits'])) {
                    $setting['cookiecredits'] = !empty($_COOKIE['creditnotice']) ? explode('D', $_COOKIE['creditremind']) : array_fill(0, 6, 0);
                    for ($i = 1; $i <= 5; $i++) {
                        $setting['cookiecreditsbase'][$i] = UserUtil::getUserProfile('extcredits' . $i);
                    }
                }
                if ($ruletxt) {
                    $setting['cookiecreditsrule'][$ruletxt] = $ruletxt;
                }
            }
            foreach ($creditArr as $key => $value) {
                if (!empty($key) && $value && in_array($key, $allowkey)) {
                    $sql[$key] = $value;
                    if ($creditRemind && substr($key, 0, 10) == 'extcredits') {
                        $i = substr($key, 10);
                        $setting['cookiecredits'][$i] += $value;
                    }
                }
            }
            if ($creditRemind) {
                MainUtil::setCookie('creditremind', implode('D', $setting['cookiecredits']) . 'D' . Ibos::app()->user->uid);
                MainUtil::setCookie('creditbase', '0D' . implode('D', $setting['cookiecreditsbase']));
                if (!empty($setting['cookiecreditsrule'])) {
                    MainUtil::setCookie('creditrule', strip_tags(implode("\t", $setting['cookiecreditsrule'])));
                }
            }
            Ibos::app()->setting->copyFrom($setting);
            if ($sql) {
                UserCount::model()->increase($uids, $sql);
            }
            if ($checkGroup && count($uids) == 1) {
                $this->checkUserGroup($uids[0]);
            }
            $this->setExtraSql(array());
        }
    }

    /**
     *
     * @param integer $uid
     * @param boolean $update
     * @return int
     */
    public function countCredit($uid, $update = true)
    {
        $credits = 0;
        $creditsformula = Ibos::app()->setting->get('setting/creditsformula');
        if ($uid && !empty($creditsformula)) {
            $user = UserCount::model()->fetchByPk($uid); // for eval
            eval("\$credits = round(" . $creditsformula . ");");
            if ($uid == $uid) {
                if ($update && $user['credits'] != $credits) {
                    User::model()->updateCredits($uid, $credits);
                    Ibos::app()->user->setState('credits', $credits);
                }
            } elseif ($update) {
                User::model()->updateCredits($uid, $credits);
            }
        }
        return $credits;
    }

    /**
     * 检查用户组更新
     * @param integer $uid
     * @return int
     */
    public function checkUserGroup($uid)
    {
        $uid = intval($uid);
        $user = User::model()->fetchByUid($uid);
        if (empty($user)) {
            return 0;
        }
        $credits = $this->countCredit($uid, false);
        $updateArray = array();
        $groupId = $user['groupid'];
        if ($user['groupid'] > 0) {
            $group = UserGroup::model()->fetchByPk($user['groupid']);
        } else {
            $group = array();
        }
        if ($user['credits'] != $credits) {
            $updateArray['credits'] = $credits;
            $user['credits'] = $credits;
        }
        $user['credits'] = $user['credits'] == '' ? 0 : $user['credits'];
        $sendNotify = false;
        if (empty($group) || !($user['credits'] >= $group['creditshigher'] && $user['credits'] < $group['creditslower'])) {
            $newGroup = UserGroup::model()->fetchByCredits($user['credits']);
            if (!empty($newGroup)) {
                if ($user['groupid'] != $newGroup['gid']) {
                    $updateArray['groupid'] = $groupId = $newGroup['gid'];
                    $sendNotify = true;
                }
            }
        }
        // 更新User表
        if ($updateArray) {
            User::model()->modify($uid, $updateArray);
            UserUtil::cleanCache($uid);
        }
        // 发送提醒
        if ($sendNotify) {
            Notify::model()->sendNotify($uid, 'user_group_upgrade', array(
                    '{groupname}' => $newGroup['title'],
                    '{url}' => Ibos::app()->urlManager->createUrl('user/home/credit', array('op' => 'level', 'uid' => $uid))
                )
            );
        }
        return $groupId;
    }

    /**
     *
     * @param type $ruleLog
     * @param type $needle
     * @param type $newCycle
     */
    protected function updateCheating($ruleLog, $needle, $newCycle)
    {
        if ($needle) {
            $logArr = array();
            switch ($ruleLog['norepeat']) {
                case 0:
                    break;
                case 1:
                    $info = empty($ruleLog['info']) || $newCycle ? $needle : $ruleLog['info'] . ',' . $needle;
                    $logArr['info'] = \CHtml::encode($info);
                    break;
                case 2:
                    $user = empty($ruleLog['user']) || $newCycle ? $needle : $ruleLog['user'] . ',' . $needle;
                    $logArr['user'] = \CHtml::encode($user);
                    break;
                case 3:
                    $app = empty($ruleLog['app']) || $newCycle ? $needle : $ruleLog['app'] . ',' . $needle;
                    $logArr['app'] = \CHtml::encode($app);
                    break;
            }
            if ($ruleLog['isnew']) {
                $logArr['clid'] = $ruleLog['clid'];
                $logArr['uid'] = $ruleLog['uid'];
                CreditRuleLogField::model()->add($logArr);
            } elseif ($logArr) {
                CreditRuleLogField::model()->updateAll($logArr, '`uid` = :uid AND clid = :clid', array(':uid' => $ruleLog['uid'], ':clid' => $ruleLog['clid']));
            }
        }
    }

    /**
     *
     * @param type $logArr
     * @param type $rule
     * @param type $isSql
     * @return type
     */
    public function addLogArr($logArr, $rule, $isSql = 0)
    {
        $extcredits = $this->getExtCredits();
        for ($i = 1; $i <= 5; $i++) {
            if (isset($extcredits[$i]) && !empty($extcredits[$i])) {
                $extcredit = intval($rule['extcredits' . $i]) * $this->_coef;
                if ($isSql) {
                    $logArr['extcredits' . $i] = 'extcredits' . $i . "='$extcredit'";
                } else {
                    $logArr['extcredits' . $i] = $extcredit;
                }
            }
        }
        return $logArr;
    }

    /**
     * 获取规则
     * @param string $action
     * @return array
     */
    public function getRule($action)
    {
        if (empty($action)) {
            return false;
        }
        Cache::load('creditrule');
        $caches = Ibos::app()->setting->get('cache/creditrule');
        $extcredits = $this->getExtCredits();
        $rule = false;
        if (is_array($caches[$action])) {
            $rule = $caches[$action];
            for ($i = 1; $i <= 5; $i++) {
                if (empty($extcredits[$i])) {
                    unset($rule['extcredits' . $i]);
                    continue;
                }
                $rule['extcredits' . $i] = intval($rule['extcredits' . $i]);
            }
        }
        return $rule;
    }

    /**
     * 获取一条积分日志记录
     * @param integer $rid 规则ID
     * @param integer $uid 用户ID
     * @return array
     */
    public function getRuleLog($rid, $uid = 0)
    {
        $log = array();
        $uid = $uid ? $uid : Ibos::app()->user->uid;
        if ($rid && $uid) {
            $log = CreditRuleLog::model()->fetchRuleLog($rid, $uid);
        }
        return $log;
    }

    /**
     *
     * @param integer $clid
     * @param integer $uid
     * @return array
     */
    public function getCheckLogByClId($clid, $uid = 0)
    {
        $uid = $uid ? $uid : Ibos::app()->user->uid;
        return CreditRuleLogField::model()->fetchByAttributes(array('uid' => $uid, 'clid' => $clid));
    }

    /**
     *
     * @return array
     */
    public function getExtCredits()
    {
        return Ibos::app()->setting->get('setting/extcredits');
    }

    /**
     *
     * @param type $ruleLog
     * @param type $needle
     * @param type $checkType
     * @return boolean
     */
    protected function checkCheating($ruleLog, $needle, $checkType)
    {
        $repeat = false;
        switch ($checkType) {
            case 0:
                break;
            case 1:
                $infoArr = explode(',', $ruleLog['info']);
                if (!empty($ruleLog['info']) && in_array($needle, $infoArr)) {
                    $repeat = true;
                }
                break;
            case 2:
                $userArr = explode(',', $ruleLog['user']);
                if (!empty($ruleLog['user']) && in_array($needle, $userArr)) {
                    $repeat = true;
                }
                break;
            case 3:
                $appArr = explode(',', $ruleLog['app']);
                if (!empty($ruleLog['app']) && in_array($needle, $appArr)) {
                    $repeat = true;
                }
                break;
        }
        return $repeat;
    }

}
