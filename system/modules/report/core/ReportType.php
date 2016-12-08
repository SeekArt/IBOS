<?php

/**
 * 工作总结与计划模块------工作总结与计划汇报类型组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 *  工作总结与计划模块------工作总结与计划汇报类型组件类
 * @package application.modules.report.core
 * @version $Id: ICReportType.php 66 2013-09-13 08:40:50Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\core;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;

class ReportType
{

    /**
     * 处理汇报类型的添加数据
     * @param array $data 要添加的类型数组
     * @return array 返回填充默认值后的类型数组
     */
    public static function handleSaveData($data)
    {
        $type = array(
            'sort' => intval($data['sort']),
            'typename' => StringUtil::filterCleanHtml($data['typename']),
            'uid' => Ibos::app()->user->uid,
            'intervaltype' => intval($data['intervaltype']),
            'intervals' => intval($data['intervals']),
            'issystype' => 0
        );
        return $type;
    }

    /**
     * 处理汇报类型显示名称
     * @param integer $intervalType 总结与计划区间类型(0:周 1:月 2:季 3:半年 4:年)
     * @return string
     */
    public static function handleShowInterval($intervalType)
    {
        $allInterval = array(
            0 => '周',
            1 => '月',
            2 => '季',
            3 => '半年',
            4 => '年',
            5 => '其他'
        );
        return $allInterval[$intervalType];
    }

}
