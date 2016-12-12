<?php

/**
 * 招聘模块------ ICResumeContact类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  resume_contact表的数据层操作类，继承ICModel
 * @package application.modules.recruit.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\core;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\recruit\model\ResumeDetail as RDModel;
use application\modules\user\model\User;

class ResumeContact
{

    /**
     * 联系记录数据输出处理
     * @param array $contactList 要处理的联系记录
     * @return array 返回处理过后的联系记录数组
     */
    public static function processListData($contactList)
    {
        foreach ($contactList as $k => $contact) {
            $contactList[$k]['realname'] = RDModel::model()->fetchRealnameByResumeid($contact['resumeid']);
            $contactList[$k]['inputtime'] = date('Y-m-d', $contact['inputtime']);
            $contactList[$k]['detail'] = StringUtil::cutStr($contact['detail'], 12);
            if ($contactList[$k]['input']) {
                $contactList[$k]['input'] = User::model()->fetchRealnameByUid($contact['input']);
            } else {
                $contactList[$k]['input'] = '';
            }
        }
        return $contactList;
    }

    /**
     *  处理联系记录添加或编辑的数据
     * @param array $data 提交过来要添加或编辑的联系记录数组
     * @return array  返回处理过后的联系记录数组
     */
    public static function processAddOrEditData($data)
    {
        $contactArr = array(
            'upuid' => 0,
            'inputtime' => 0,
            'contact' => '',
            'purpose' => '',
            'detail' => ''
        );
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($contactArr))) {
                $contactArr[$k] = $k === 'detail' ? \CHtml::encode($v) : $v;
            }
        }
        $input = implode(',', StringUtil::getId($contactArr['upuid']));
        $contactArr['input'] = empty($input) ? Ibos::app()->user->uid : $input;
        if ($contactArr['inputtime'] != 0) {
            $contactArr['inputtime'] = strtotime($contactArr['inputtime']);
        } else {
            $contactArr['inputtime'] = TIMESTAMP;
        }
        unset($contactArr['upuid']);
        return $contactArr;
    }

}
