<?php

/**
 * UserBinding class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 用户绑定model。该模型保存了所有接入APP与OA用户的绑定信息
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.user.model
 * @version $Id$
 */

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;

class UserBinding extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_binding}}';
    }

    /**
     * 批量查找指定用户与某个绑定类型的值
     * @param array $uids 用户ID数组
     * @param string $app 绑定类型
     * @return type
     */
    public function fetchValuesByUids($uids, $app)
    {
        $rs = Ibos::app()->db->createCommand()
            ->select('bindvalue')
            ->from($this->tableName())
            ->where(sprintf("FIND_IN_SET(uid,'%s') AND app = '%s'", implode(',', $uids), $app))
            ->queryAll();
        return Convert::getSubByKey($rs, 'bindvalue');
    }

    /**
     * 获取指定app的所有信息
     * @param string $app
     * @return array
     */
    public function fetchAllByApp($app)
    {
        $rs = Ibos::app()->db->createCommand()
            ->select('*')
            ->from($this->tableName())
            ->where(sprintf("app = '%s'", $app))
            ->queryAll();
        return $rs;
    }

    /**
     * 获取绑定酷办公的用户人数
     *
     * @param $app
     * @return string
     */
    public function fetchUserNumByApp($app)
    {
        return $this->count('app = :app', array(':app' => $app));
    }

    /**
     * 获取指定app的绑定用户uid
     * @param string $app
     * @return array
     */
    public function fetchUidByApp($app)
    {
        $rs = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(sprintf("app = '%s'", $app))
            ->queryAll();
        return $rs;
    }

    /**
     * 获取指定用户指定app的绑定值
     * @param integer $uid 用户ID
     * @param string $app 绑定类型
     * @return string
     */
    public function fetchBindValue($uid, $app)
    {
        $uid = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(" `uid` = '{$uid}' AND `app` = '{$app}' ")
            ->queryScalar();
        return $uid ? $uid : 0;
    }

    /**
     * 根据绑定值,绑定类型查找UID
     * @param string $value 绑定的值
     * @param string $app 绑定的类型
     * @return integer
     */
    public function fetchUidByValue($value, $app)
    {
        $rs = $this->fetch(array('select' => 'uid', 'condition' => sprintf("bindvalue = '%s' AND app ='%s'", $value, $app)));
        return !empty($rs['uid']) ? intval($rs['uid']) : 0;
    }

    /**
     * 检查指定用户是否与某个类型已经绑定
     * @param integer $uid 用户ID
     * @param string $app 要查询的绑定类型
     * @return boolean
     */
    public function getIsBinding($uid, $app)
    {
        return $this->countByAttributes(array('uid' => intval($uid), 'app' => $app)) != 0;
    }

    /**
     * 设置绑定值
     * @param string $uid UID
     * @param string $bindvalue 绑定值
     * @param string $app 平台标识
     * @return boolean
     */
    public function setBinding($uid, $bindvalue, $app)
    {
        Ibos::app()->db->createCommand()
            ->delete($this->tableName(), " `uid` = '{$uid}' AND `app` = '{$app}' ");
        $res = Ibos::app()->db->createCommand()
            ->insert($this->tableName(), array(
                'uid' => $uid,
                'bindvalue' => $bindvalue,
                'app' => $app,
            ));
        return !!$res;
    }

    /**
     * 删除绑定关系
     * @param string $uid UID
     * @param string $app 平台标识
     * @return boolean
     */
    public function deleteBinding($uid, $app)
    {
        $res = Ibos::app()->db->createCommand()
            ->delete($this->tableName(), " `uid` = '{$uid}' AND `app` = '{$app}' ");
        return !!$res;
    }

}
