<?php

defined( 'IN_MODULE_ACTION' ) or die( 'Access Denied' );
return array(
	'param' => array(
		'name' => '文件柜',
		'category' => '个人办公',
		'description' => '提供企业文件存储',
		'author' => 'gzhzh @ IBOS Team Inc',
		'version' => '1.0',
		'pushMovement' => 0,
		'indexShow' => array(
			'widget' => array(
				'file/file'
			),
			'link' => 'file/default/index'
		)
	),
	'config' => array(
		'modules' => array(
			'file' => array(
                'class' => 'application\modules\file\FileModule'
            )
		),
		'components' => array(
			'messages' => array(
				'extensionPaths' => array(
					'file' => 'application.modules.file.language',
				)
			)
		),
	),
	'authorization' => array(
		'persoanl' => array(
			'type' => 'node',
			'name' => '个人网盘',
			'group' => '文件柜',
			'controllerMap' => array(
				'default' => array( 'index', 'getdynamic' ),
				'personal' => array( 'index', 'getcate', 'add', 'del', 'show', 'ajaxent' ),
				'myshare' => array( 'index', 'getcate', 'share', 'show' ),
				'fromshare' => array( 'index', 'getcate', 'show' )
			)
		),
		'company' => array(
			'type' => 'node',
			'name' => '公司网盘',
			'group' => '文件柜',
			'controllerMap' => array(
				'company' => array( 'index', 'getcate', 'add', 'del', 'show', 'ajaxent' ),
			)
			
		)
	)
);
