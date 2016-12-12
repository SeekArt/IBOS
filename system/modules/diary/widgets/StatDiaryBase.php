<?php

/**
 * IWStatDiaryBase class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 日志 - widget base
 * @package application.modules.diary.widgets
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\widgets;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use CWidget;

class StatDiaryBase extends CWidget
{

    /**
     * 统计的类型
     * @var string
     */
    private $_type;

    /**
     * 设置统计类型
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * 返回统计类型
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 获取是否在个人统计
     * @return boolean
     */
    protected function inPersonal()
    {
        return $this->getType() === 'personal';
    }

    /**
     * 通用获取统计的用户ID方法，在个人统计界面返回自己的ID，在评阅界面返回传入的ID或
     * 下属ID
     * @return array
     */
    protected function getUid()
    {
        if ($this->inPersonal()) {
            $uid = array(Ibos::app()->user->uid);
        } else {
            $id = Env::getRequest('uid');
            $uids = StringUtil::filterCleanHtml(StringUtil::filterStr($id));
            if (empty($uids)) {
                $uid = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);
                if (empty($uid)) {
                    return array();
                }
            } else {
                $uid = explode(',', $uids);
            }
        }
        return $uid;
    }

    /**
     * 检查评阅进入权限。如果在评阅界面通过getUid()方法确没有返回任何ID，是没有意义的
     * @return void
     */
    protected function checkReviewAccess()
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            $this->getController()->redirect('stats/personal');
        }
    }

    /**
     *
     * @param type $class
     * @param type $properties
     * @return type
     */
    protected function createComponent($class, $properties = array())
    {
        return Ibos::createComponent(array_merge(array('class' => $class), $properties));
    }

}
