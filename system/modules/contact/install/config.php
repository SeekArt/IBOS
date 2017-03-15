<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '通讯录',
        'category' => '人力资源',
        'description' => '提供企业员工通讯录',
        'author' => 'gzhzh @ IBOS Team Inc',
        'version' => '1.0',
        'indexShow' => array(
            'link' => 'contact/default/index'
        )
    ),
    'config' => array(
        'modules' => array(
            'contact' => array(
                'class' => 'application\modules\contact\ContactModule'
            )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'contact' => 'application.modules.contact.language'
                )
            )
        ),
    ),
    'authorization' => array(
        'contact' => array(
            'type' => 'node',
            'name' => '通讯录',
            'group' => '通讯录',
            'controllerMap' => array(
                'default' => array('index', 'ajaxapi', 'export', 'printcontact'),
                'constant' => array('index'),
                'api' => array('deptlist', 'userlist', 'groupuserlist', 'search', 'corp', 'dept', 'user', 'hiddenuidarr'),
            )
        )
    ),
    'behaviors' => array(
        'onUpdateCache' => array(
            'class' => 'application\modules\contact\behaviors\UpdateContactCache'
        )
    ),
);
