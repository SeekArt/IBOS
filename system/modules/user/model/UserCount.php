<?php

/**
 * user模块用户统计model文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user模块 用户统计model
 *
 * @package application.modules.user.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils\StringUtil;
use application\core\utils\Ibos;

class UserCount extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_count}}';
    }

    /**
     * 统计操作
     * @param type $uids
     * @param array $creditArr
     * @return integer
     */
    public function increase($uids, $creditArr)
    {
        $uids = StringUtil::iIntval((array)$uids, true);
        $sql = array();
        $allowKey = array(
            'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5',
            'oltime', 'attachsize'
        );
        foreach ($creditArr as $key => $value) {
            if (($value = intval($value)) && $value && in_array($key, $allowKey)) {
                $sql[] = "`{$key}`=`{$key}`+'{$value}'";
            }
        }
        if (!empty($sql)) {
            $sqlText = 'UPDATE %s SET %s WHERE uid IN (%s)';
            return Ibos::app()->db->createCommand()
                ->setText(sprintf($sqlText, $this->tableName(), implode(',', $sql), StringUtil::iImplode($uids)))
                ->execute();
        }
    }

}
