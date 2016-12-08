<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '总结',
        'category' => '个人办公',
        'description' => '提供企业工作总结与计划',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0',
        'pushMovement' => 1,
        'indexShow' => array(
            'widget' => array(
                'report/report'
            ),
            'link' => 'report/default/index'
        )
    ),
    'config' => array(
        'modules' => array(
            'report' => array('class' => 'application\modules\report\ReportModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'report' => 'application.modules.report.language',
                )
            )
        ),
    ),
    'authorization' => array(
        'report' => array(
            'type' => 'node',
            'name' => '个人工作总结与计划',
            'group' => '工作总结与计划',
            'controllerMap' => array(
                'default' => array('index', 'add', 'edit', 'del', 'show'),
                'type' => array('add', 'edit', 'del'),
                'comment' => array('getcommentlist', 'addcomment', 'delcomment')
            )
        ),
        'review' => array(
            'type' => 'node',
            'name' => '评阅下属总结与计划',
            'group' => '工作总结与计划',
            'controllerMap' => array(
                'review' => array('index', 'personal', 'add', 'edit', 'del', 'show')
            )
        ),
        'statistics' => array(
            'type' => 'node',
            'name' => '查看统计',
            'group' => '工作总结与计划',
            'controllerMap' => array(
                'stats' => array('personal', 'review')
            )
        )
    ),
    'statistics' => array(
        'sidebar' => 'application\modules\report\widgets\StatReportSidebar',
        'header' => 'application\modules\report\widgets\StatReportHeader',
        'summary' => 'application\modules\report\widgets\StatReportSummary',
        'count' => 'application\modules\report\widgets\StatReportCount'
    )
);
