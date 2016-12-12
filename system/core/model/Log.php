<?php

namespace application\core\model;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use CJSON;

class Log
{

    /**
     * 写入日志
     * @param array $msg
     * @param string $level 日志层级
     * @param string $category
     * @return void
     */
    public static function write($msg, $level = 'action', $category = __CLASS__)
    {
        $message = CJSON::encode($msg);
        $logger = Ibos::getLogger();
        return $logger->log($message, $level, $category);
    }

    /**
     * 查询列表数据
     * @param integer $tableId 存档表ID
     * @param string $condition 查询条件
     * @param integer $limit
     * @param integer $offset
     * @param string $order
     * @return array
     */
    public static function fetchAllByList($tableId, $condition = '', $limit = 20, $offset = 0, $order = 'logtime DESC')
    {
        $table = self::getTableName($tableId);
        $list = array_map(function ($temp) {
            $temp['logtime'] = date('Y-m-d H:i:s', $temp['logtime']);
            return $temp;
        }, Ibos::app()->db->createCommand()
            ->select('*')
            ->from($table)
            ->where($condition)
            ->order($order)
            ->limit($limit)
            ->offset($offset)
            ->queryAll());
        return $list;
    }

    /**
     * 根据存档表统计条数
     * @param type $tableId
     * @param type $condition
     * @return type
     */
    public static function countByTableId($tableId = 0, $condition = '')
    {
        $table = self::getTableName($tableId);
        $count = Ibos::app()->db->createCommand()
            ->select('count(id)')
            ->from($table)
            ->where($condition)
            ->queryScalar();
        return intval($count);
    }

    /**
     * 获取日志存档表ID
     * @return integer
     */
    public static function getLogTableId()
    {
        $tableId = Cache::get('logtableid');
        if ($tableId === false) {
            $tableId = Ibos::app()->db->createCommand()
                ->select('svalue')
                ->from('{{setting}}')
                ->where("skey = 'logtableid'")
                ->queryScalar();
            Cache::set('logtableid', intval($tableId));
        }
        return $tableId;
    }

    /**
     * 根据存档表id获取存档表名
     * @param integer $tableId 存档表id
     * @return string
     */
    public static function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        $year = date('Y');
        return $tableId > 0 ? "{{log_{$tableId}}}" : sprintf('{{log_%s}}', $year);
    }

    /**
     * 获取所有存档表的年份后缀ID,返回一个一维数组
     * @return array
     */
    public static function getAllArchiveTableId()
    {
        $return = array();
        $db = Ibos::app()->db->createCommand();
        $prefix = $db->getConnection()->tablePrefix;
        $tables = $db->setText("SHOW TABLES LIKE '" . str_replace('_', '\_', $prefix . 'log_%') . "'")
            ->queryAll(false);
        if (!empty($tables)) {
            $tableArr = Convert::getSubByKey($tables, 0);
            $return = array_map(
                (function ($archiveTable) {
                    return substr($archiveTable, -4);
                }), $tableArr);
        }
        return $return;
    }

}
