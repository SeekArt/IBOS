<?php

/**
 * 分表存档工具类文件。
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 分表存档工具类
 * @package application.modules.dashboard.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\utils;

use application\core\utils\Cache;
use application\core\utils\Page;
use application\modules\main\model\Setting;

class ArchiveSplit
{

    /**
     * 更新存档表ID
     * @param array $tableDriver 分表模块表驱动配置
     * @return void
     */
    public static function updateTableIds($tableDriver)
    {
        $tableIds = $tableDriver['mainTable']::model()->fetchTableIds();
        Setting::model()->updateSettingValueByKey($tableDriver['tableId'], $tableIds);
        Cache::save($tableDriver['tableId'], $tableIds);
    }

    /**
     * 获取指定模块的表状态，包括主表，内容表及存档表
     * @param array $tableIds 存档表ID
     * @param array $tableDriver 分表模块表驱动配置
     * @return array
     */
    public static function getTableStatus($tableIds, $tableDriver)
    {
        $data = array();
        // 获取主表信息
        $data['main'] = $tableDriver['mainTable']::model()->getTableStatus();
        $data['body'] = $tableDriver['bodyTable']::model()->getTableStatus();
        $tables = array();
        //分表信息
        foreach ($tableIds as $tableId) {
            if (!$tableId) {
                continue;
            }
            $tables[$tableId]['main'] = $tableDriver['mainTable']::model()->getTableStatus($tableId);
            $tables[$tableId]['body'] = $tableDriver['bodyTable']::model()->getTableStatus($tableId);
        }
        $data['tables'] = $tables;
        return $data;
    }

    /**
     * 查询分表数据
     * @param array $conditions 查询的条件
     * @param array $tableDriver 分表模块表驱动配置
     * @param boolean $countOnly 是否只返回统计数据
     * @return mixed
     */
    public static function search($conditions, $tableDriver, $countOnly = false, $length = 20)
    {
        global $page;
        $list = array();
        $tableId = $conditions['sourcetableid'] ? $conditions['sourcetableid'] : 0;
        $sql = $tableDriver['mainTable']::model()->getSplitSearchContdition($conditions);
        $count = $tableDriver['mainTable']::model()->countBySplitCondition($tableId, $sql);
        if ($countOnly) {
            return $count;
        } else {
            $page = Page::create($count, $length);
            $list = $tableDriver['mainTable']::model()->fetchAllBySplitCondition($tableId, $sql, $page->getOffset(), $page->getLimit());
        }
        return $list;
    }

}
