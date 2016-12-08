<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '通知公告',
        'category' => '信息中心',
        'description' => '提供企业通知信息发布，以及版本记录',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0',
        'pushMovement' => 1,
        'indexShow' => array(
            'widget' => array(
                'officialdoc/officialdoc'
            ),
            'link' => 'officialdoc/officialdoc/index'
        )
    ),
    'config' => array(
        'modules' => array(
            'officialdoc' => array(
                'class' => 'application\modules\officialdoc\OfficialdocModule'
            )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'officialdoc' => 'application.modules.officialdoc.language',
                )
            )
        ),
    ),
    'authorization' => array(
        'view' => array(
            'type' => 'node',
            'name' => '通知浏览',
            'group' => '通知公告',
            'controllerMap' => array(
                'officialdoc' => array('index', 'show', 'getdoclist'),
                'category' => array('index'),
                'comment' => array('getcommentlist', 'addcomment', 'delcomment')
            )
        ),
        'publish' => array(
            'type' => 'node',
            'name' => '通知发布',
            'group' => '通知公告',
            'controllerMap' => array(
                'officialdoc' => array('add'),
            )
        ),
        'category' => array(
            'type' => 'node',
            'name' => '通知分类管理',
            'group' => '通知公告',
            'controllerMap' => array(
                'category' => array('index', 'add', 'edit', 'del'),
            ),
        ),
        'manager' => array(
            'type' => 'data',
            'name' => '通知管理',
            'group' => '通知公告',
            'node' => array(
                'edit' => array(
                    'name' => '编辑',
                    'controllerMap' => array(
                        'officialdoc' => array('edit')
                    )
                ),
                'del' => array(
                    'name' => '删除',
                    'controllerMap' => array(
                        'officialdoc' => array('del')
                    )
                ),
            )
        )
    )
);
