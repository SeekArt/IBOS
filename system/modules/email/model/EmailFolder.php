<?php

namespace application\modules\email\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class EmailFolder extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{email_folder}}';
    }

    /**
     * 根据uid获取所有非系统文件夹
     * @param integer $uid 用户id
     * @param string $search 查询类型
     * @return array
     */
    public function fetchAllUserFolderByUid($uid, $search = 'all')
    {
        $cond = '1';
        if ($search == 'all') {
            // do nothing
        } else if ($search == 'web') {
            $cond = 'webid!=0';
        } else if ($search == 'folder') {
            $cond = 'webid=0';
        }
        $records = $this->fetchAll("uid={$uid} AND `system`='0' AND {$cond} ORDER BY sort DESC");
        return $records;
    }

    /**
     * 根据外部邮箱ID查找文件夹名字
     * @param integer $id 外部邮箱ID
     * @return string
     */
    public function fetchFolderNameByWebId($id)
    {
        $rs = Ibos::app()->db->createCommand()
            ->select('name')
            ->from('{{email_folder}}')
            ->where('webid = ' . intval($id))
            ->queryScalar();
        return $rs ? $rs : '';
    }

    /**
     * 获取用户已用数量
     * @param integer $uid
     * @return integer
     */
    public function getUsedSize($uid)
    {
        $where = array('and', "toid={$uid}", 'isdel=0', 'issend=1');
        $count = Ibos::app()->db->createCommand()
            ->select('SUM(eb.size) AS sum')
            ->from('{{email}} e')
            ->leftJoin('{{email_body}} eb', 'e.bodyid = eb.bodyid')
            ->where($where)
            ->queryScalar();
        return $count ? intval($count) : 0;
    }

    /**
     * 获取指定文件夹的大小
     * @param integer $uid 用户ID
     * @param integer $fid 文件夹ID
     * @return integer 统计的大小
     */
    public function getFolderSize($uid, $fid)
    {
        $where = array('and', "toid={$uid}", "fid={$fid}", 'isdel=0', 'issend=1');
        $count = Ibos::app()->db->createCommand()
            ->select('SUM(eb.size) AS sum')
            ->from('{{email}} e')
            ->leftJoin('{{email_body}} eb', 'e.bodyid = eb.bodyid')
            ->where($where)
            ->queryScalar();
        return $count ? intval($count) : 0;
    }

    /**
     * 获取指定系统文件夹的大小
     * @param integer $uid 用户ID
     * @param integer $fid 系统文件夹别名
     * @return integer
     */
    public function getSysFolderSize($uid, $alias)
    {
        $param = Email::model()->getListParam($alias, $uid);
        $count = Ibos::app()->db->createCommand()
            ->select('SUM(eb.size) AS sum')
            ->from('{{email}} e')
            ->leftJoin('{{email_body}} eb', 'e.bodyid = eb.bodyid')
            ->where($param['condition'])
            ->queryScalar();
        return $count ? intval($count) : 0;
    }

}
