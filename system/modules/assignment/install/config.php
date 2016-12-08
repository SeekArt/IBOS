<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '任务指派',
        'category' => '个人办公',
        'description' => '提供企业工作任务指派',
        'author' => 'gzhzh @ IBOS Team Inc',
        'version' => '1.0',
        'pushMovement' => 1,
        'indexShow' => array(
            'widget' => array(
                'assignment/assignment'
            ),
            'link' => 'assignment/unfinished/index'
        )
    ),
    'config' => array(
        'modules' => array(
            'assignment' => array(
                'class' => 'application\modules\assignment\AssignmentModule'
            )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'assignment' => 'application.modules.assignment.language',
                )
            )
        ),
    ),
    'authorization' => array(
        'assignment' => array(
            'type' => 'node',
            'name' => '任务管理',
            'group' => '任务指派',
            'controllerMap' => array(
                'default' => array('add', 'edit', 'del', 'show'),
                'unfinished' => array('index', 'ajaxentrance'),
                'finished' => array('index'),
                'comment' => array('getcommentlist', 'addcomment', 'delcomment')
            )
        ),
        'review' => array(
            'type' => 'node',
            'name' => '查看下属任务',
            'group' => '任务指派',
            'controllerMap' => array(
                'unfinished' => array('sublist')
            )
        ),
    )
);
