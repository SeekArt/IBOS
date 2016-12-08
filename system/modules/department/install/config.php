<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '部门模块',
        'description' => '提供IBOS部门管理所需功能',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'department' => array('class' => 'application\modules\department\DepartmentModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'department' => 'application.modules.department.language'
                )
            )
        ),
    )
);
