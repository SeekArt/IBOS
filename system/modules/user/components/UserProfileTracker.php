<?php

namespace application\modules\user\components;

use application\core\utils\File;
use application\modules\user\utils\User as UserUtil;
use CWidget;

class UserProfileTracker extends CWidget
{

    private $_user = array();
    private $_checkItem = array(
        'birthday' => 10,
        'mobile' => 20,
        'email' => 10,
        'avatar' => 30,
        'password' => 20,
        'others' => 10
    );

    public function setUser($user = array())
    {
        $this->_user = $user;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function run()
    {
        $status = $this->checkUserProfile();
        $percent = array_sum($status);
        $diff = array_diff_key($this->_checkItem, $status);
        if (!empty($diff)) {
            $tips = array_rand($diff);
        } else {
            $tips = '';
        }
        $this->render('application.modules.user.views.tracker', array('percent' => $percent, 'tip' => $tips));
    }

    /**
     *
     * @return array
     */
    protected function checkUserProfile()
    {
        $status = array();
        foreach ($this->_checkItem as $item => $percent) {
            $checkMethod = 'check' . ucfirst($item);
            if (method_exists($this, $checkMethod)) {
                if ($this->$checkMethod($this->getUser())) {
                    $status[$item] = $percent;
                }
            }
        }
        return $status;
    }

    /**
     * 密码检查
     * @param array $user
     * @return boolean
     */
    protected function checkPassword($user)
    {
        return true; // DEBUG::暂时不进行判定
    }

    /**
     * 检查生日填写
     * @param array $user
     * @return boolean
     */
    protected function checkBirthday($user)
    {
        if (!empty($user['birthday'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查手机绑定
     * @param array $user
     * @return boolean
     */
    protected function checkMobile($user)
    {
        if ($user['validationmobile'] == '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查邮件绑定
     * @param array $user
     * @return boolean
     */
    protected function checkEmail($user)
    {
        if ($user['validationemail'] == '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查其他选项
     * @param array $users
     * @return boolean
     */
    protected function checkOthers($users)
    {
        if (!empty($users['bio']) && !empty($users['address'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查头像
     * @param array $users
     * @return boolean
     */
    protected function checkAvatar($users)
    {
        $avatar = UserUtil::getAvatar($users['uid']);
        if (File::fileExists('data/avatar/' . $avatar)) {
            return true;
        } else {
            return false;
        }
    }

}
