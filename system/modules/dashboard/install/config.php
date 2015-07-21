<?php

defined( 'IN_MODULE_ACTION' ) or die( 'Access Denied' );
return array(
    'param' => array(
        'name' => '后台管理',
        'description' => '提供IBOS后台管理所需功能',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'dashboard' => array( 'class' => 'application\modules\dashboard\DashboardModule' )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'dashboard' => 'application.modules.dashboard.language'
                )
            )
        ),
    )
);
