<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '角色模块',
        'description' => '提供IBOS角色管理所需功能',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'position' => array('class' => 'application\modules\role\RoleModule')
        )
    )
);
