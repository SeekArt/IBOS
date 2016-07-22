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
class Model {

    /**
     * 表是否存在
     * @param string $tableName 支持如{{user}}和ibos_user两种形式
     * @return boolean
     */
    public static function tableExists( $tableName ) {
        $isExist = IBOS::app()->db
                ->createCommand()
                ->setText( sprintf( "SHOW TABLES LIKE '%s'", $tableName ) )
                ->execute();
        return (bool) $isExist;
    }

    /**
     * 删除表，如果不存在也返回false
     * @param string $tableName
     * @return boolean
     */
    public static function dropTable( $tableName ) {
        $res = false;
        if ( self::tableExists( $tableName ) ) {
            $res = IBOS::app()->db
                            ->createCommand()->dropTable( $tableName );
        }
        return (bool) $res;
    }

    /**
     * 创建表。已经存在则返回false
     * @param string $tableName 表名
     * @param string $sql 创建语句
     * @return boolean
     */
    public static function createTable( $tableName, $sql ) {
        $res = false;
        if ( !self::tableExists( $tableName ) ) {
            IBOS::app()->db
                    ->createCommand()->setText( $sql )->execute();
        }
        return (bool) $res;
    }

    /**
     * 获取当前数据库名
     * @return string 数据库名
     */
    public static function getCurrentDbName() {
        $sql = "select database();";
        return IBOS::app()->db
                        ->createCommand()->setText( $sql )->queryScalar();
    }

    /**
     * 检查列的存在情况
     * @param string $tableName 表名
     * @param mixed $columnMixed 需要检测的列，数组或者逗号字符串
     * @return array 返回不存在的列
     */
    public static function checkColumnExist( $tableName, $columnMixed ) {
        $columnArray = is_array( $columnMixed ) ? $columnMixed : explode( ',', $columnMixed );
        $conditionString = " `COLUMN_NAME` = '" . implode( "' OR `COLUMN_NAME` = '", $columnArray ) . "' ";
        $currentDbName = self::getCurrentDbName();
        $sql = "SELECT `COLUMN_NAME` FROM information_schema.`COLUMNS`"
                . " WHERE `TABLE_SCHEMA` = '{$currentDbName}'"
                . " AND `TABLE_NAME` = '{$tableName}' AND ( {$conditionString} )";
        $existColumnArray = IBOS::app()->db
                        ->createCommand()->setText( $sql )->queryColumn();
        $notExistArray = array_diff( $columnArray, $existColumnArray );
        return $notExistArray;
    }

    public static function executeSqls( $sqlString ) {
        $sqlArray = StringUtil::splitSql( $sqlString );
        $command = IBOS::app()->db->createCommand();
        if ( is_array( $sqlArray ) ) {
            foreach ( $sqlArray as $sql ) {
                if ( trim( $sql ) != '' ) {
                    $result = $command->setText( $sql )->execute();
                }
            }
        } else {
            $result = $command->setText( $sqlArray )->execute();
        }
        return $result;
    }

}
