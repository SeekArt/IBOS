<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '企业微博',
        'description' => '企业微博',
        'author' => 'banyan @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'weibo' => array('class' => 'application\modules\weibo\WeiboModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'weibo' => 'application.modules.weibo.language'
                )
            )
        ),
    )
);
