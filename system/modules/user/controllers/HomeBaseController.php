<?php

namespace application\modules\user\controllers;

use application\core\controllers\Controller;
use application\core\utils as util;
use application\modules\message\model\UserData;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\model\Follow;

class HomebaseController extends Controller
{

    /**
     * 当前用户ID
     * @var integer
     */
    private $_uid = 0;

    /**
     * 当前用户数组
     * @var array
     */
    private $_user = array();

    /**
     * 是否本人标识
     * @var boolean
     */
    private $_isMe = false;

    /**
     * 初始化当前用户ID及是否本人标识
     * @return void
     */
    public function init()
    {
        $uid = intval(util\Env::getRequest('uid'));
        if (!$uid) {
            $uid = util\Ibos::app()->user->uid;
        }
        $this->_uid = $uid;
        $user = User::model()->fetchByUid($uid, false, true);
        if (!$user) {
            $this->error(util\Ibos::lang('Cannot find the user'), $this->createUrl('home/index'));
        } else {
            $this->_user = $user;
        }
        $this->_isMe = $uid == util\Ibos::app()->user->uid;
        parent::init();
    }

    /**
     * 是否本人访问
     * @return boolean
     */
    public function getIsMe()
    {
        return $this->_isMe;
    }

    /**
     * 查看微博模块是否有启用，未启用则不显示tab
     * @return boolean
     */
    public function getIsWeiboEnabled()
    {
        return util\Module::getIsEnabled('weibo');
    }

    /**
     * 获取当前uid
     * @return integer
     */
    public function getUid()
    {
        return $this->_uid;
    }

    /**
     * 获取当前用户资料
     * @return array
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 积分栏目获取sidebar
     * @param array $lang
     * @return type
     */
    public function getCreditSidebar($lang = array())
    {
        $data['lang'] = $lang;
        $data['creditFormulaExp'] = strip_tags(util\Ibos::app()->setting->get('setting/creditsformulaexp'));
        $extcredits = util\Ibos::app()->setting->get('setting/extcredits');
        if (!empty($extcredits)) {
            $user = UserCount::model()->fetchByPk($this->getUid());
            foreach ($extcredits as $index => &$ext) {
                if (!empty($ext)) {
                    $ext['value'] = $user['extcredits' . $index];
                }
            }
        }
        $data['userCount'] = UserCount::model()->fetchByPk($this->getUid());
        $data['extcredits'] = $extcredits;
        $data['user'] = $this->getUser();

        return $this->renderPartial('application.modules.user.views.home.creditSidebar', $data, true);
    }

    /**
     * 公共头部获取,用于视图
     * @return string 公共头部HTML
     */
    public function getHeader($lang = array())
    {
        $onlineStatus = UserUtil::getOnlineStatus($this->getUid());
        $styleMap = array(
            -1 => 'o-pm-offline',
            0 => 'o-pm-online',
            1 => 'o-pm-online',
            2 => 'o-pm-offline',
        );
        $data = array(
            'user' => $this->getUser(),
            'assetUrl' => $this->getAssetUrl('user'),
            'swfConfig' => util\Attach::getUploadConfig(),
            'onlineIcon' => $styleMap[$onlineStatus],
            'lang' => $lang
        );
        if ($this->getIsWeiboEnabled()) {
            $data['userData'] = UserData::model()->getUserData($this->getUid());
            !$this->getIsMe() && $data['states'] = Follow::model()->getFollowState(util\Ibos::app()->user->uid, $this->getUid());
        }
        return $this->renderPartial('application.modules.user.views.header', $data, true);
    }

    /**
     * 获取部门同事
     * @param array $user
     * @return array
     */
    public function getColleagues($user, $includeMe = true, $offset = 0, $limit = 4)
    {
        $contacts = array();
        if (!empty($user['deptid'])) {
            $upId = $user['upuid'];
            $deptUsers = User::model()->fetchAll(
                array(
                    'select' => 'uid',
                    'condition' => "`deptid` = :deptid AND `status` IN (0,1)",
                    'offset' => $offset,
                    'limit' => $limit,
                    'params' => array(':deptid' => $user['deptid']),
                )
            );
            if (!empty($deptUsers)) {
                $deptUserIds = util\Convert::getSubByKey($deptUsers, 'uid');
                $meUidIndex = array_search($this->getUid(), $deptUserIds);
                if ($meUidIndex !== false) {
                    unset($deptUserIds[$meUidIndex]);
                }
                $includeMe && $contacts[0] = $user;
                if ($upId && $upId != $this->getUid()) {
                    $upIdIndex = array_search($upId, $deptUserIds);
                    if ($upIdIndex !== false) {
                        unset($deptUserIds[$upIdIndex]);
                    }
                    $contacts[1] = User::model()->fetchByUid($upId);
                }
                $deptUserIds = array_values($deptUserIds);
                for ($i = 2, $j = 0; $j < count($deptUserIds); $i++, $j++) {
                    $contacts[$i] = User::model()->fetchByUid($deptUserIds[$j]);
                }
            }
        }
        return $contacts;
    }

}
