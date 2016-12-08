<?php

/**
 * 工作日志模块------diary_share表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 工作日志模块------diary_share表操作类，继承ICModel
 * @package application.modules.diary.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\model;

use application\core\model\Model;
use application\modules\department\model\Department;
use application\modules\user\model\User;

class DiaryShare extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary_share}}';
    }

    /**
     * 通过uid取得用户的默认共享人员信息
     * @param integer $uid
     * @return array $result
     */
    public function fetchShareInfoByUid($uid)
    {
        $record = $this->fetch('uid=:uid', array(':uid' => $uid));
        if (!empty($record)) {
            $shareIdArrTemp = explode(',', $record['deftoid']);
            $shareIdArr = array_filter($shareIdArrTemp, create_function('$v', 'return !empty($v);'));  //过滤空值或者0
            $result = array();
            foreach ($shareIdArr as $key => $shareId) {
                $result[$key]['department'] = Department::model()->fetchDeptNameByUid($shareId);
                $result[$key]['user'] = User::model()->fetchRealnameByUid($shareId);
                $result[$key]['userid'] = $shareId;
            }
            return array('shareInfo' => $result, 'deftoid' => $record['deftoid']);
        } else {
            return array('shareInfo' => array(), 'deftoid' => '');
        }
    }

    /**
     * 通过uid删除默认分享人
     * @param integer $uid
     */
    public function delDeftoidByUid($uid)
    {
        $record = $this->fetch('uid=:uid', array(':uid' => $uid));
        if ($record) {
            $this->deleteAllByAttributes(array('uid' => $uid));
        }
    }

    /**
     * 通过uid增加或者修改Deftoid的值
     * @param integer $uid
     * @param string $value
     * @return array 添加成功后的用户ID
     */
    public function addOrUpdateDeftoidByUid($uid, $value)
    {
        $record = $this->fetch('uid=:uid', array(':uid' => $uid));
        $share = array(
            'uid' => $uid,
            'deftoid' => implode(',', $value)
        );
        if (empty($record)) {  //如果不存在记录，添加一条记录
            $this->add($share);
        } else {  //存在就修改
            $this->modify($record['id'], array('deftoid' => $share['deftoid']));
        }
    }

}
