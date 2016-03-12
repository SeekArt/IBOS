<?php

namespace application\core\utils;

class Model {

    /**
     * 表是否存在
     * @param string $tableName 支持如{{user}}和ibos_user两种形式
     * @return type
     */
    public static function tableExists($tableName) {
        $isExist = Ibos::app()->db
                ->createCommand()
                ->setText(sprintf("SHOW TABLES LIKE '%s'", $tableName))
                ->execute();
        return (bool) $isExist;
    }

    /**
     * 删除表
     * @param string $tableName
     * @return type
     */
    public static function dropTable($tableName) {
        $res = false;
        if (self::tableExists($tableName)) {
            $res = Ibos::app()->db->createCommand()->dropTable($tableName);
        }
        return (bool) $res;
    }

    public static function createTable($tableName, $sql) {
        $res = false;
        if (self::tableExists($tableName)) {
            Ibos::app()->db->createCommand()->setText($sql)->execute();
        }
        return (bool) $res;
    }

}
