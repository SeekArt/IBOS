<?php

/**
 * 招聘模块------ resume_bgchecks表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  resume_bgchecks表的数据层操作类，继承ICModel
 * @package application.modules.resume.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\core;

use application\core\utils\Ibos;
use application\modules\recruit\model\ResumeDetail as RDModel;

class RecruitBgchecks
{

    /**
     * 处理list页面显示数据
     * @param array $data
     * @return array
     */
    public static function processListData($bgcheckList)
    {
        foreach ($bgcheckList as $k => $bgcheck) {
            $bgcheckList[$k]['realname'] = RDModel::model()->fetchRealnameByResumeid($bgcheck['resumeid']);
            $bgcheckList[$k]['entrytime'] = $bgcheck['entrytime'] == 0 ? '-' : date('Y-m-d', $bgcheck['entrytime']);
            $bgcheckList[$k]['quittime'] = $bgcheck['quittime'] == 0 ? '-' : date('Y-m-d', $bgcheck['quittime']);
        }
        return $bgcheckList;
    }

    /**
     *  处理背景调查添加或编辑的数据
     * @param array $data 提交过来要添加或者编辑的背景调查数组
     * @return array  返回处理过后的背景调查数组
     */
    public static function processAddOrEditData($data)
    {
        $bgcheckArr = array(
            'company' => '',
            'address' => '',
            'phone' => '',
            'fax' => '',
            'contact' => '',
            'position' => '',
            'entrytime' => 0,
            'quittime' => 0,
            'detail' => '',
            'uid' => 0
        );
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($bgcheckArr))) {
                $bgcheckArr[$k] = ($k === 'detail' || $k === 'contact') ? \CHtml::encode($v) : $v;
            }
        }
        $bgcheckArr['entrytime'] = strtotime($bgcheckArr['entrytime']);
        $bgcheckArr['quittime'] = strtotime($bgcheckArr['quittime']);
        $bgcheckArr['uid'] = Ibos::app()->user->uid;
        return $bgcheckArr;
    }

}
