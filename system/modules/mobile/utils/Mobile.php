<?php

namespace application\modules\mobile\utils;

use application\core\utils\Env;
use application\core\utils\Ibos;

class Mobile
{

    /**
     * 返回类型
     */
    public static function dataType()
    {
        $dataType = 'JSON';
        $callback = Env::getRequest('callback');
        if (isset($callback)) {
            $dataType = 'JSONP';
        }
        return $dataType;
    }

    /**
     * 创建表
     * @param $tableName
     */
    public static function createDirectTable($tableName)
    {
        $columns = self::returnBaseTable();
        Ibos::app()->db->createCommand()->createTable($tableName, $columns,
            "ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    /**
     * 基本表信息
     */
    public static function returnBaseTable()
    {
        return array(
            "id" => "mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id'",
            "uid" => "mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id'",
            "direct" => "tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否设置只看直属下属,1为是,0为否'",
            "PRIMARY KEY (`id`)",
        );
    }
}

