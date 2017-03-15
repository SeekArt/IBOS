<?php

namespace application\core\utils;

/**
 * Description
 *
 * @namespace application\core\utils
 * @filename Model.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-3-17 17:56:11
 * @version $Id$
 */
class Model
{

    /**
     * 表是否存在
     *
     * @param string $tableName 支持如{{user}}和ibos_user两种形式
     * @return boolean
     */
    public static function tableExists($tableName)
    {
        $isExist = Ibos::app()->db
            ->createCommand()
            ->setText(sprintf("SHOW TABLES LIKE '%s'", $tableName))
            ->execute();
        return (bool)$isExist;
    }

    /**
     * 删除表，如果不存在也返回false
     *
     * @param string $tableName
     * @return boolean
     */
    public static function dropTable($tableName)
    {
        $res = false;
        if (self::tableExists($tableName)) {
            $res = Ibos::app()->db
                ->createCommand()->dropTable($tableName);
        }
        return (bool)$res;
    }

    /**
     * 创建表。已经存在则返回false
     *
     * @param string $tableName 表名
     * @param string $sql 创建语句
     * @return boolean
     */
    public static function createTable($tableName, $sql)
    {
        $res = false;
        if (!self::tableExists($tableName)) {
            Ibos::app()->db
                ->createCommand()->setText($sql)->execute();
        }
        return (bool)$res;
    }

    /**
     * 获取当前数据库名
     *
     * @return string 数据库名
     */
    public static function getCurrentDbName()
    {
        $sql = "select database();";
        return Ibos::app()->db
            ->createCommand()->setText($sql)->queryScalar();
    }

    /**
     * 检查列的存在情况
     *
     * @param string $tableName 表名
     * @param mixed $columnMixed 需要检测的列，数组或者逗号字符串
     * @return array 返回不存在的列
     */
    public static function checkColumnExist($tableName, $columnMixed)
    {
        $columnArray = is_array($columnMixed) ? $columnMixed : explode(',', $columnMixed);
        $conditionString = " `COLUMN_NAME` = '" . implode("' OR `COLUMN_NAME` = '", $columnArray) . "' ";
        $currentDbName = self::getCurrentDbName();
        $sql = "SELECT `COLUMN_NAME` FROM information_schema.`COLUMNS`"
            . " WHERE `TABLE_SCHEMA` = '{$currentDbName}'"
            . " AND `TABLE_NAME` = '{$tableName}' AND ( {$conditionString} )";
        $existColumnArray = Ibos::app()->db
            ->createCommand()->setText($sql)->queryColumn();
        $notExistArray = array_diff($columnArray, $existColumnArray);
        return $notExistArray;
    }

    /**
     * 在某张表上不存在 $columnName 列，则添加
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $columnType
     * @return bool true 添加成功，false 添加失败或列已存在
     */
    public static function addColumnIfNotExists($tableName, $columnName, $columnType)
    {
        $table = Ibos::app()->db->schema->getTable($tableName);
        if (isset($table->columns[$columnName])) {
            return false;
        }

        return (boolean)Ibos::app()->db->createCommand()
            ->addColumn($tableName, $columnName, $columnType);
    }

    /**
     * 在某张表上添加多列，如果列已存在，则跳过
     *
     * @param string $tableName
     * @param array $columns
     * @return bool
     */
    public static function addColumnsIfNotExists($tableName, array $columns)
    {
        $successFlag = true;

        foreach ($columns as $column) {
            if (isset($column['columnName']) && isset($column['columnType'])) {
                $successFlag = $successFlag && static::addColumnIfNotExists($tableName, $column['columnName'],
                        $column['columnType']);
            }
        }

        return $successFlag;
    }

    /**
     * 设置表引擎类型
     *
     * @param string $tableName
     * @param string $engineName
     * @return \CDbDataReader
     */
    public static function setTableEngine($tableName, $engineName)
    {
        return Ibos::app()->db->createCommand(sprintf('ALTER TABLE `%s` ENGINE = `%s`', $tableName, $engineName))
            ->execute();
    }

    /**
     * 当不存在某条表记录的时候，插入这条记录
     * 
     * @param string $tableName
     * @param array $columns
     * @param string $condition
     * @param array $params
     * @return bool
     */
    public static function insertRowIfNotExists($tableName, $columns, $condition = '', array $params = array())
    {
        if (!empty($condition)) {
            $row = Ibos::app()->db->createCommand()
                ->from($tableName)
                ->where($condition, $params)
                ->query();

            if (!empty($row)) {
                return false;
            }
        }

        return (boolean)Ibos::app()->db->createCommand()
            ->insert($tableName, $columns);
    }

    public static function executeSqls($sqlString)
    {
        $sqlArray = StringUtil::splitSql($sqlString);
        $command = Ibos::app()->db->createCommand();
        if (is_array($sqlArray)) {
            foreach ($sqlArray as $sql) {
                if (trim($sql) != '') {
                    $result = $command->setText($sql)->execute();
                }
            }
        } else {
            $result = $command->setText($sqlArray)->execute();
        }
        return $result;
    }

}
