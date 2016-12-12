<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  文件柜动态表
 * @package application.modules.file.model
 * @version $Id: FileDynamic.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;
use application\modules\user\model\User;

class FileDynamic extends Model
{

    const LIMIT = 50;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_dynamic}}';
    }

    /**
     * 记录动态
     * @param integer $fid 文件id
     * @param string $content 动态内容
     * @param string $uid 产生动态的uid
     * @param string $touids uid串
     * @param string $todeptids 部门id串
     * @param string $toposids 岗位id串
     * @return boolean
     */
    public function record($fid, $uid, $content, $touids = '', $todeptids = '', $toposids = '')
    {
        $file = File::model()->fetchByFid($fid);
        if (!empty($file)) {
            $data = array(
                'fid' => intval($fid),
                'uid' => $uid,
                'content' => $content,
                'touids' => $touids,
                'todeptids' => $todeptids,
                'toposids' => $toposids,
                'time' => TIMESTAMP
            );
            return $this->add($data);
        }
    }

    /**
     * 查找50条动态
     * @param integer $uid 登陆者uid
     * @param integer $offset 从第几条开始
     * @return array
     */
    public function fetchDynamic($uid, $offset = 0, $limit = 0, $extraCon = 1)
    {
        $user = User::model()->fetchByUid($uid);
        $deptIds = explode(',', $user['alldeptid'] . ',alldept');
        $deptCon = '';
        foreach ($deptIds as $deptid) {
            $deptCon .= " OR FIND_IN_SET('{$deptid}',`todeptids`) ";
        }
        $con = "( FIND_IN_SET({$uid}, `touids`) OR FIND_IN_SET({$user['positionid']}, `toposids`) {$deptCon} )";
        $limit = !$limit ? $this->count($con) : $limit;
        $record = $this->fetchAll(array(
            'condition' => $con,
            'offset' => $offset,
            'limit' => $limit,
            'order' => 'time DESC'
        ));
        return $record;
    }

}
