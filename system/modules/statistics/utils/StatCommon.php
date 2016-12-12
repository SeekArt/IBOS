<?php

/**
 * StatCommon class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计通用工具类
 * @package application.modules.statistics.utils
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\statistics\utils;

use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\Ibos;
use CJSON;

class StatCommon
{

    /**
     * 获取所有可以支持统计的模块
     * @return array 模块数组
     */
    public static function getStatisticsModules()
    {
        static $statModules = array();
        if (empty($statModules)) {
            foreach (Ibos::app()->getEnabledModule() as $module => $configs) {
                $config = CJSON::decode($configs['config'], true);
                if (isset($config['statistics'])) {
                    $statModules[] = array('module' => $module, 'name' => $configs['name']);
                }
            }
        }
        return $statModules;
    }

    /**
     * 获取模块对应的widget
     * @param string $module
     * @return array
     */
    public static function getWidget($module)
    {
        $modules = Ibos::app()->getEnabledModule();
        $widgets = array();
        if (isset($modules[$module])) {
            $configs = $modules[$module]['config'];
            $config = CJSON::decode($configs, true);
            if (isset($config['statistics'])) {
                $widgets = $config['statistics'];
            }
        }
        return $widgets;
    }

    /**
     * 获取模块对应widget的类名
     * @param string $module 模块
     * @param string $name 特定的widget名称，详见
     * @return string
     */
    public static function getWidgetName($module, $name = '')
    {
        $widgets = self::getWidget($module);
        return isset($widgets[$name]) ? $widgets[$name] : '';
    }

    /**
     * 通用获取时间范围及类型的方法
     * @staticvar array $timeScope 静态缓存，时间范围
     * @return string
     */
    public static function getCommonTimeScope()
    {
        static $timeScope = array();
        if (empty($timeScope)) {
            $time = Env::getRequest('time');
            $start = Env::getRequest('start');
            $end = Env::getRequest('end');
            if (!empty($time)) {
                if (!in_array($time, array('thisweek', 'lastweek', 'thismonth', 'lastmonth'))) {
                    $time = 'thisweek';
                }
            } else if (!empty($start) && !empty($end)) { // 自定义时间范围
                $start = strtotime($start);
                $end = strtotime($end);
                if ($start && $end) {
                    $timeScope = array(
                        'timestr' => 'custom',
                        'start' => $start,
                        'end' => $end
                    );
                }
            } else {
                $time = 'thisweek';
            }
            if (empty($timeScope)) {
                $timeScope = DateTime::getStrTimeScope($time);
                $timeScope['timestr'] = $time;
            }
        }
        return $timeScope;
    }

}
