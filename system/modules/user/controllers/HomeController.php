<?php

namespace application\modules\user\controllers;

use application\core\model\Log;
use application\core\model\Module;
use application\core\utils as util;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\CreditLog;
use application\modules\dashboard\model\CreditRule;
use application\modules\dashboard\model\CreditRuleLog;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\message\model\Notify;
use application\modules\message\utils\Message as MessageUtil;
use application\modules\user\model as UserModel;
use application\modules\user\model\UserBinding;
use application\modules\user\utils\User as UserUtil;
use CException;
use CHttpSession;
use CJSON;
use Yii;

class HomeController extends HomeBaseController
{

    /**
     * 个人首页
     */
    public function actionIndex()
    {
        // 视图变量
        $data = $this->getIndexData();
        $this->setPageState('breadCrumbs', array(
            array('name' => util\Ibos::lang('Home'), 'url' => $this->createUrl('home/index')),
            array('name' => util\Ibos::lang('Home page'))
        ));
        $this->render('index', $data);
    }

    /**
     * 个人资料
     * @throws CException
     */
    public function actionPersonal()
    {
        $op = util\Env::getRequest('op');
        if (!in_array($op, array('profile', 'avatar', 'history', 'password', 'skin', 'remind'))) {
            $op = 'profile';
        }
        // 提交动作
        if (util\Env::submitCheck('formhash')) {
            // 如果不是本人操作，不能进行提交操作
            if (!$this->getIsMe()) {
                throw new CException(util\Ibos::lang('Parameters error', 'error'));
            }
            $data = $_POST;
            // 个人资料提交
            if ($op == 'profile') {
                $profileField = array('birthday', 'bio', 'telephone', 'address', 'qq');
                $userField = array('mobile', 'email');
                $model = array();
                // 确定更新所使用MODEL
                foreach ($_POST as $key => $value) {
                    if (in_array($key, $profileField)) {
                        // 生日字段的转换处理
                        if ($key == 'birthday') {
                            $value = !empty($value) ? strtotime($value) : 0;
                        }
                        $model['application\modules\user\model\UserProfile'][$key] = util\StringUtil::filterCleanHtml($value);
                    } else if (in_array($key, $userField)) {
                        $model['application\modules\user\model\User'][$key] = util\StringUtil::filterCleanHtml($value);
                    }
                }

                //简单检查一下手机、邮箱、电话和QQ格式
                //手机就简单的看看是否是11位数字，可以做更严格的验证
                if (isset($model['application\modules\user\model\User']) && !empty($model['application\modules\user\model\User']['mobile'])) {
                    if (!preg_match("/^[0-9]{11}$/", $model['application\modules\user\model\User']['mobile'])) {
                        $this->error(util\Ibos::lang('Phone number format error'), $this->createUrl('home/personal'));
                    }
                }
                //邮箱地址检查
                if (isset($model['application\modules\user\model\User']) && !empty($model['application\modules\user\model\User']['email'])) {
                    if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $model['application\modules\user\model\User']['email'])) {
                        $this->error(util\Ibos::lang('Email address format error'), $this->createUrl('home/personal'));
                    }
                }
                // 更新操作

                foreach ($model as $modelObject => $value) {
                    $modelObject::model()->modify($this->getUid(), $value);
                }
            } else if ($op == 'password') {// 密码设置
                $user = $this->getUser();
                $update = false;
                if ($data['originalpass'] == '') {
                    // 没有填写原来的密码
                    $this->error(util\Ibos::lang('Original password require'));
                } else if (strcasecmp(md5(md5($data['originalpass']) . $user['salt']), $user['password']) !== 0) {
                    // 密码跟原来的对不上
                    $this->error(util\Ibos::lang('Password is not correct'));
                } else if (!empty($data['newpass']) && strcasecmp($data['newpass'], $data['newpass_confirm']) !== 0) {
                    // 两次密码不一致
                    $this->error(util\Ibos::lang('Confirm password is not correct'));
                } else {
                    $password = md5(md5($data['newpass']) . $user['salt']);
                    $update = UserModel\User::model()->updateByUid($this->getUid(), array('password' => $password, 'lastchangepass' => TIMESTAMP));
                }
            } else if ($op == 'remind') { // 提醒设置
                // 提醒设置
                $remindSetting = array();
                foreach (array('email', 'sms', 'app') as $field) {
                    if (!empty($data[$field])) {
                        foreach ($data[$field] as $id => $value) {
                            $remindSetting[$id][$field] = $value;
                        }
                    }
                }
                if (!empty($remindSetting)) {
                    $remindSetting = serialize($remindSetting);
                } else {
                    $remindSetting = '';
                }
                // 更新数据库及缓存
                UserModel\UserProfile::model()->updateByPk($this->getUid(), array('remindsetting' => $remindSetting));
            }
            // 更新缓存
            UserUtil::cleanCache($this->getUid());
            $this->success(util\Ibos::lang('Save succeed', 'message'), $this->createUrl('home/personal', array('op' => $op)));
        } else {
            if (in_array($op, array('avatar', 'history', 'password', 'remind'))) {
                if (!$this->getIsMe()) {
                    $this->error(util\Ibos::lang('Parameters error', 'error'), $this->createUrl('home/index'));
                }
            }
            //查找酷办公是否有绑定
            $uid = $this->getUid();
            $isCo = UserBinding::model()->getIsBinding($uid, 'co');
            $cobinding = Setting::model()->fetchSettingValueByKey('cobinding');
            $dataCo = array(
                'co' => $isCo,
                'cobinding' => $cobinding
            );
            $wxcorpid = Setting::model()->fetchSettingValueByKey('corpid');
            $dataWx = array(
                'wxqy' => UserBinding::model()->getIsBinding($uid, 'wxqy'),
                'wxqybinding' => !empty($wxcorpid),
                'value' => UserBinding::model()->fetchBindValue($uid, 'wxqy'),
            );
            $dataProvider = 'get' . ucfirst($op);
            $data = array();
            if (method_exists($this, $dataProvider)) {
                $data = $this->$dataProvider();
            }
            $param = array_merge(array(
                'user' => $this->getUser(),
                'op' => $op,
            ), $data, $dataCo, $dataWx);

            $this->setPageState('breadCrumbs', array(
                array('name' => util\Ibos::lang('Home'), 'url' => $this->createUrl('home/index')),
                array('name' => util\Ibos::lang('Profile'))
            ));
            $this->render($op, $param);
        }
    }

    /**
     * 积分
     * @return void
     */
    public function actionCredit()
    {
        if (!$this->getIsMe()) {
            $this->error(util\Ibos::lang('Parameters error', 'error'), $this->createUrl('home/index'));
        }
        $op = util\Env::getRequest('op');
        if (!in_array($op, array('log', 'level', 'rule'))) {
            $op = 'log';
        }
        $dataProvider = 'getCredit' . ucfirst($op);
        $data = $this->$dataProvider();
        $this->setPageState('breadCrumbs', array(
            array('name' => util\Ibos::lang('Home'), 'url' => $this->createUrl('home/index')),
            array('name' => util\Ibos::lang('Credit'))
        ));
        $this->render('credit' . ucfirst($op), $data);
    }

    /**
     * 检测安全得分
     */
    public function actionCheckSecurityRating()
    {
        if (util\Ibos::app()->request->getIsAjaxRequest()) {
            $rating = $this->getSecurityRating();
            $this->ajaxReturn(array('IsSuccess' => true, 'rating' => $rating));
        }
    }

    /**
     * 检查验证邮箱或手机是否重复
     */
    public function actionCheckRepeat()
    {
        if (!$this->getIsMe()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $op = util\Env::getRequest('op');
        if (!in_array($op, array('email', 'mobile'))) {
            $op = 'email';
        }
        $data = urldecode(util\Env::getRequest('data'));
        $record = UserModel\User::model()->countByAttributes(array($op => $data));
        if ($record > 1) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => util\Ibos::lang('Repeat ' . $op)));
        } else {
            $res = $this->sendVerify($op, $data);
            $msg = $res ? util\Ibos::lang('Operation succeed', 'message') : util\Ibos::lang('Error ' . $op);
            $this->ajaxReturn(array('isSuccess' => $res, 'msg' => $msg));
        }
    }

    /**
     * 显示酷办公绑定视图
     */
    public function actionShow()
    {
        $this->renderPartial('bindIbosco');
    }

    /**
     * 酷办公绑定
     */
    public function actionBindco()
    {
        //如果前端传过来的id不是本人的，就提示参数错误
        if (!$this->getIsMe()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $username = util\Env::getRequest('account');
        $password = util\Env::getRequest('password');
        $userArr = array(
            'username' => $username,
            'password' => md5($password),
        );
        $aeskey = Yii::app()->setting->get('setting/aeskey');
        $oaUrl = rtrim(Yii::app()->setting->get('siteurl'), '/');
        $signature = $this->getSignature($aeskey, $oaUrl);
        $unit = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
        $param = array(
            'code' => $unit['corpcode'],
            'url' => urlencode($oaUrl),
            'signature' => $signature,
            'op' => 'bindCo',
        );
        //请求酷办公那边验证帐号密码
        $api = util\Api::getInstance();
        $url = $api->buildUrl(CoApi:: CO_URL . 'api/ibospublic', $param);
        $res = $api->fetchResult($url, $userArr, 'post');
        if (!is_array($res)) {
            $resArr = CJSON::decode($res, true);
            //返回成功，则查找绑定表，
            if ($resArr['isSuccess']) {
                $userBind = UserBinding::model()->fetchBindValue($this->getUid(), 'co');
                if (empty($userBind)) {
                    $data = array(
                        'uid' => $this->getUid(),
                        'bindvalue' => $resArr['guid'],
                        'app' => 'co'
                    );
                    $return = UserBinding::model()->add($data);
                    if (!$return) {
                        $this->ajaxReturn(array('isSuccess' => true, 'msg' => '绑定失败'));
                    } else {
                        $this->ajaxReturn(array('isSuccess' => true, 'msg' => '绑定成功'));
                    }
                }
            } else {
                $this->ajaxReturn($resArr);
            }
        } else {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => $res['error']));
        }
    }

    public function actionUnbindco()
    {
        if (!$this->getIsMe()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $userBind = UserBinding::model()->fetch('uid = :uid AND  app = :app', array(':uid' => $this->getUid(), ':app' => 'co'));
        if (!empty($userBind)) {
            UserBinding::model()->deleteByPk($userBind['id']);
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\Ibos::lang('Operation succeed', 'message')));
        } else {
            $this->ajaxReturn(array('isSuceess' => false, 'msg' => util\Ibos::lang('Operation failure', 'message')));
        }
    }

    /**
     * 绑定微信企业号
     * @return ajaxArray
     */
    public function actionBindwxqy()
    {
        $request = Ibos::app()->request;
        if (!$this->getIsMe() || !$request->getIsPostRequest()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $userid = $request->getPost('userid');
        $uid = $this->getUid();
        $isBinding = UserBinding::model()->getIsBinding($uid, 'wxqy');
        if ($isBinding) {
            return $this->error(Ibos::lang('Parameters error', 'error'));
        }
        UserBinding::model()->setBinding($uid, $userid, 'wxqy');
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Operation succeed', 'message'),
        ));
    }

    /**
     * 解绑微信企业号
     * @return ajaxArray
     */
    public function actionUnbindwxqy()
    {
        $request = Ibos::app()->request;
        if (!$this->getIsMe() || !$request->getIsPostRequest()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        UserBinding::model()->deleteBinding($this->getUid(), 'wxqy');
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Operation succeed', 'message'),
        ));
    }

    public function getSignature($aeskey, $oaUrl)
    {
        $signature = md5($aeskey . $oaUrl);
        return $signature;
    }

    /**
     * 绑定操作
     */
    public function actionBind()
    {
        if (!$this->getIsMe()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $op = util\Env::getRequest('op');
        if (!in_array($op, array('mobile', 'email', 'wxqy'))) {
            $op = 'email';
        }
        $user = $this->getUser();
        $this->renderPartial('bind' . ucfirst($op), array('user' => $user, 'lang' => util\Ibos::getLangSources()));
    }

    /**
     * 检查验证码
     * @return void
     */
    public function actionCheckVerify()
    {
        if (!$this->getIsMe()) {
            exit(util\Ibos::lang('Parameters error', 'error'));
        }
        $op = util\Env::getRequest('op');
        if (!in_array($op, array('email', 'mobile'))) {
            $op = 'email';
        }
        $data = urldecode(util\Env::getRequest('data'));
        $session = new CHttpSession;
        $session->open();
        $verifyVal = md5($data);
        $verifyName = $op;
        if (isset($session[$verifyName]) && $session[$verifyName] == $verifyVal) {
            $check = true;
            $this->updateVerify($op);
        } else {
            $check = false;
        }
        $this->ajaxReturn(array('isSuccess' => $check));
    }

    /**
     * 发送验证流程
     * @param string $operation 验证项目
     * @param string $data 验证目标（邮件地址或手机）
     * @return boolean 验证成功与否
     */
    protected function sendVerify($operation, $data)
    {
        $session = new CHttpSession;
        $session->open();
        if ($operation == 'email') {
            $val = util\StringUtil::random(8);
        } else if ($operation == 'mobile') {
            $val = util\StringUtil::random(5, 1);
        }
        $verifyVal = md5($val);
        $verifyName = $operation;
        $session[$verifyName] = $verifyVal;
        $session['verifyData'] = $data;
        $res = $this->makeVerify($operation, $data, $val);
        $session->close();
        return $res;
    }

    /**
     * 创建验证
     * @param string $op 验证动作
     * @param string $data 验证目标（邮件地址或手机）
     * @param string $val 验证码
     * @return boolean 创建成功与否
     */
    private function makeVerify($op, $data, $val)
    {
        if ($op == 'email') {
            $message = util\Ibos::lang('Verify email content', '', array('{code}' => $val, '{date}' => util\Convert::formatDate(TIMESTAMP, 'd')));
            if (util\Cloud::getInstance()->isOpen()) {
                $res = util\Mail::sendCloudMail($data, util\Ibos::lang('Verify email title'), $message);
            } else {
                $res = util\Mail::sendMail($data, util\Ibos::lang('Verify email title'), $message);
            }
        } else if ($op == 'mobile') {
            $message = util\Ibos::lang('Verify mobile content', '', array('{code}' => $val));
            $res = MessageUtil::sendSms($data, $message, 'user', $this->getUid());
        }
        return $res;
    }

    /**
     * 更新验证项
     * @param string $op 更新动作
     * @return void
     */
    private function updateVerify($op)
    {
        $uid = $this->getUid();
        $session = new CHttpSession;
        $session->open();
        $data = $session['verifyData'];
        if ($op == 'email') {
            UserModel\User::model()->updateByUid($uid, array('validationemail' => 1, 'email' => $data));
        } else if ($op == 'mobile') {
            UserModel\User::model()->updateByUid($uid, array('validationmobile' => 1, 'mobile' => $data));
        }
        UserUtil::updateCreditByAction('verify' . $op, $this->getUid());
    }

    /**
     * 个人首页数据获取
     * @return type
     */
    protected function getIndexData()
    {
        $allCreditRankList = UserModel\User::model()->fetchAllCredit();
        // 当前排名
        $curRanking = array_search($this->getUid(), $allCreditRankList);
        // 总人数
        $totalRanking = count($allCreditRankList);
        // 排名百分比
        $rankPercent = (float)100 - (round(($curRanking + 1) / $totalRanking, 2) * 100);
        // 积分top6
        $top6 = array_slice($allCreditRankList, 0, 6);
        $ranklist = UserModel\User::model()->fetchAllByUids($top6);
        // 是否第一名
        if (!empty($ranklist) && $allCreditRankList[0] == $this->getUid()) {
            $isTop = true;
        } else {
            $isTop = false;
        }
        // 积分项目及用户统计
        $extcredits = util\Ibos::app()->setting->get('setting/extcredits');
        $userCount = UserModel\UserCount::model()->fetchByPk($this->getUid());
        // 人脉
        $user = $this->getUser();
        $data = array(
            'curRanking' => ($curRanking + 1),
            'totalRanking' => $totalRanking,
            'rankPercent' => $rankPercent,
            'ranklist' => $ranklist,
            'isTop' => $isTop,
            'user' => $user,
            'extcredits' => $extcredits,
            'userCount' => $userCount,
            'contacts' => $this->getColleagues($user)
        );
        if ($this->getIsMe()) {
            $data['securityRating'] = $this->getSecurityRating();
            $logTableId = Log::getLogTableId();
            $con = sprintf("`level` = 'login' AND `category` = 'module.user.%d'", $this->getUid());
            // 登陆历史
            $data['history'] = Log::fetchAllByList($logTableId, $con, 4, 0);
        }
        return $data;
    }

    /**
     * 头像上传数据获取
     * @return array
     */
    protected function getAvatar()
    {
        return array('user' => $this->getUser(), 'swfConfig' => util\Attach::getUploadConfig($this->getUid()));
    }

    /**
     * 登陆历史数据获取
     * @return array
     */
    protected function getHistory()
    {
        $lastMonth = strtotime('last month');
        $logTableId = Log::getLogTableId();
        $con = sprintf("`logtime` BETWEEN %d AND %d AND `level` = 'login' AND `category` = 'module.user.%d'", $lastMonth, TIMESTAMP, $this->getUid());
        $count = Log::countByTableId($logTableId, $con);
        $pages = util\Page::create($count, 20);
        $logHistory = Log::fetchAllByList($logTableId, $con, 20, $pages->getOffset());
        return array('history' => $logHistory, 'pages' => $pages);
    }

    /**
     *
     * @return array
     */
    protected function getRemind()
    {
        $user = $this->getUser();
        $user['remindsetting'] = !empty($user['remindsetting']) ? StringUtil::utf8Unserialize($user['remindsetting']) : array();
        $nodeList = Notify::model()->getNodeList();
        $coBinding = Setting::model()->fetchSettingValueByKey('cobinding');
        foreach ($nodeList as $id => &$node) {
            $node['moduleName'] = Module::model()->fetchNameByModule($node['module']);
            $node['appdisabled'] = $coBinding == '1' ? 0 : 1;
            $node['maildisabled'] = $user['validationemail'] == 0 || !$node['sendemail'] ? 1 : 0;
            $node['smsdisabled'] = $user['validationmobile'] == 0 || !$node['sendsms'] ? 1 : 0;
            if (isset($user['remindsetting'][$id])) {
                $node['appcheck'] = isset($user['remindsetting'][$id]['app']) ? $user['remindsetting'][$id]['app'] : 0;
                $node['emailcheck'] = isset($user['remindsetting'][$id]['email']) ? $user['remindsetting'][$id]['email'] : 0;
                $node['smscheck'] = isset($user['remindsetting'][$id]['sms']) ? $user['remindsetting'][$id]['sms'] : 0;
            } else {
                $node['emailcheck'] = $node['smscheck'] = $node['appcheck'] = 0;
            }
        }
        return array('nodeList' => $nodeList);
    }

    /**
     * 积分记录数据获取
     * @return array
     */
    protected function getCreditLog()
    {
        util\Cache::load('creditrule');
        // 系统
        $creditRule = CreditRule::model()->fetchAllSortByPk('rid');
        $credits = util\Ibos::app()->setting->get('setting/extcredits');
        $relateRules = CreditRuleLog::model()->fetchAllByAttributes(array('uid' => $this->getUid()));
        $criteria = array(
            'condition' => "`uid` = :uid",
            'params' => array(':uid' => $this->getUid()),
            'order' => 'dateline DESC',
        );
        $count = CreditLog::model()->count($criteria);
        $pages = util\Page::create($count, 20);
        $criteria['limit'] = 20;
        $criteria['offset'] = $pages->getOffset();
        $creditLog = CreditLog::model()->fetchAll($criteria);
        return array(
            'creditLog' => $creditLog,
            'relateRules' => $relateRules,
            'credits' => $credits,
            'creditRule' => $creditRule,
            'pages' => $pages
        );
    }

    /**
     * 积分称谓数据获取
     * @return array
     */
    protected function getCreditLevel()
    {
        $usergroup = util\Ibos::app()->setting->get('cache/usergroup');
        return array('level' => $usergroup, 'user' => $this->getUser());
    }

    /**
     * 积分规则数据获取
     * @return array
     */
    protected function getCreditRule()
    {
        $count = CreditRule::model()->count();
        $pages = util\Page::create($count);
        $creditRule = CreditRule::model()->fetchAllSortByPk('rid', array('offset' => $pages->getOffset(), 'limit' => $pages->getLimit()));
        $credits = util\Ibos::app()->setting->get('setting/extcredits');
        return array('credits' => $credits, 'creditRule' => $creditRule, 'pages' => $pages);
    }

    /**
     * 安全评分数据
     * @return int
     */
    protected function getSecurityRating()
    {
        $score = 0;
        $user = $this->getUser();
        if (!empty($user['email'])) {
            $score += 25;
        }
        if (!empty($user['mobile'])) {
            $score += 25;
        }
        if ($user['validationemail'] == 1) {
            $score += 25;
        }
        if ($user['validationmobile'] == 1) {
            $score += 25;
        }
        return $score;
    }

    /**
     * 检查数据是否已经被注册过
     * @return string
     */
    public function actionIsRegister()
    {
        //$fieldName获取要检查的字段名
        $fieldName = util\Env::getRequest('clientid');
        //$fieldValue获取此字段用户输入的值
        $fieldValue = util\Env::getRequest($fieldName);
        //如果有传递uid，是用户编辑资料，没有uid，是新注册资料
        $uid = util\Env::getRequest('uid');
        $result = UserUtil::isRegister($uid, $fieldName, $fieldValue);
        $this->ajaxReturn(array('isSuccess' => $result), 'json');
    }
}
