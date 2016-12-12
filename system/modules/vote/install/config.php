<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '投票模块',
        'category' => '调查投票',
        'description' => '提供信息中心等模块投票调查使用',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0',
        'pushMovement' => 1,
    ),
    'config' => array(
        'modules' => array(
            'vote' => array('class' => 'application\modules\vote\VoteModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'vote' => 'application.modules.vote.language'
                )
            )
        ),
    ),
    'authorization' => array(
        'view' => array(
            'type' => 'node',
            'name' => '调查浏览',
            'group' => '调查投票',
            'controllerMap' => array(
                'default' => array('index', 'show', 'fetchindexlist', 'showvote', 'showvoteusers', 'vote'),
            )
        ),
        'publish' => array(
            'type' => 'node',
            'name' => '调查发布',
            'group' => '调查投票',
            'controllerMap' => array(
                'form' => array('addorupdate', 'updateendtime', 'del', 'show', 'edit'),
            )
        ),
        'manager' => array(
            'type' => 'node',
            'name' => '调查管理',
            'group' => '调查投票',
            'controllerMap' => array(
                'default' => array('export'),
            )
        ),
    )

);
