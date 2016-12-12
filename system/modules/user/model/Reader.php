<?php
/**
 * 用户阅读记录表
 *
 * @namespace application\modules\user\model
 * @filename Reader.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/21 20:09
 */

namespace application\modules\user\model;


use application\core\model\Model;

class Reader extends Model
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{reader}}';
    }

    /**
     * 根据模块名返回阅读记录列表
     *
     * @param string $moduleName 模块名
     * @return array
     */
    public function fetchListByModuleName($moduleName)
    {
        return $this->fetchAll('module = :module', array(
            ':module' => $moduleName,
        ));
    }

    /**
     * 添加一个阅读记录
     *
     * @param string $moduleName
     * @param integer $moduleId
     * @param integer $uid
     * @return bool
     */
    public function addRecord($moduleName, $moduleId, $uid)
    {
        $moduleName = \CHtml::encode($moduleName);
        $moduleId = filter_var($moduleId, FILTER_SANITIZE_NUMBER_INT);
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        $model = new self();
        $model->module = $moduleName;
        $model->moduleid = $moduleId;
        $model->uid = $uid;
        return $model->save();
    }

    /**
     * 如果不存在阅读记录，则添加
     *
     * @param string $moduleName
     * @param integer $moduleId
     * @param integer $uid
     * @return bool
     */
    public function addRecordIsNotExists($moduleName, $moduleId, $uid)
    {
        if ($this->isExists($moduleName, $moduleId, $uid)) {
            return false;


        }

        return $this->addRecord($moduleName, $moduleId, $uid);
    }

    /**
     * 是否存在阅读记录
     *
     * @param string $moduleName 模块名
     * @param integer $moduleId 关联模块 id
     * @param integer $uid 用户 uid
     * @return bool true 已阅读，false 为阅读
     */
    public function isExists($moduleName, $moduleId, $uid)
    {
        return $this->exists('module = :module AND moduleid = :moduleid AND uid = :uid', array(
            ':module' => $moduleName,
            ':moduleid' => $moduleId,
            ':uid' => $uid,
        ));
    }

    /**
     * 是否阅读过某条记录
     *
     * @param string $moduleName 模块名
     * @param integer $moduleId 关联模块 id
     * @param integer $uid 用户 uid
     * @return bool true 已阅读，false 为阅读
     */
    public function isRead($moduleName, $moduleId, $uid)
    {
        return $this->isExists($moduleName, $moduleId, $uid);
    }
}