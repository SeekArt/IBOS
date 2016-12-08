<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '统计模块',
        'category' => '统计模块',
        'description' => '统计模块，提供各支持模块的数据统计及汇总，采用模块扩展的方式灵活定义统计内容与视图',
        'author' => 'banyan @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'statistics' => array(
                'class' => 'application\modules\statistics\StatisticsModule'
            )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'statistics' => 'application.modules.statistics.language'
                )
            )
        ),
    ),
    'authorization' => array()
);
