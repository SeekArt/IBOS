<?php

/**
 * user_group表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  user_group表的数据层操作
 *
 * @package application.modules.user.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Ibos;

class UserGroup extends Model
{

    public function init()
    {
        $this->cacheLife = 0;
        parent::init();
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_group}}';
    }

    public function afterSave()
    {
        CacheUtil::update('UserGroup');
        CacheUtil::load('UserGroup');
        parent::afterSave();
    }

    /**
     * 查找下一等级的用户组
     * @param integer $creditsLower
     * @return array
     */
    public function fetchNextLevel($creditsLower)
    {
        $criteria = array(
            'condition' => 'creditshigher = :lower',
            'params' => array(':lower' => $creditsLower),
            'limit' => 1
        );
        return $this->fetch($criteria);
    }

    /**
     * 根据积分来查找一个对应的用户组
     * @param mixed $credits
     * @return array
     */
    public function fetchByCredits($credits)
    {
        if (is_array($credits)) {
            $creditsf = intval($credits[0]);
            $creditse = intval($credits[1]);
        } else {
            $creditsf = $creditse = intval($credits);
        }
        $criteria = array(
            'select' => 'title,gid',
            'condition' => ':creditsf>=creditshigher AND :creditse<creditslower',
            'params' => array(':creditsf' => $creditsf, ':creditse' => $creditse),
            'limit' => 1
        );
        return $this->fetch($criteria);
    }

    /**
     * 根据Id字符串删除非系统用户组
     * @param string $ids
     * @author banyan <banyan@ibos.com.cn>
     * @return integer 删除的条数
     */
    public function deleteById($ids)
    {
        $id = explode(',', trim($ids, ','));
        return parent::deleteByPk($id, "`system` = '0'");
    }

    public function findUserGroupIndexByGid()
    {
        $return = $userGroupArray = array();
        $userGroupArray = Ibos::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->queryAll();
        if (!empty($userGroupArray)) {
            foreach ($userGroupArray as $userGroup) {
                $return[$userGroup['gid']] = $userGroup;
            }
        }
        return $return;
    }

}
