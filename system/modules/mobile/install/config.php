<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => 'IBOS移动平台',
        'description' => '提供IBOS移动平台数据请求和处理相关功能',
        'author' => 'Aeolus @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'mobile' => array('class' => 'application\modules\mobile\MobileModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'mobile' => 'application.modules.mobile.language'
                )
            )
        )
    )
);
