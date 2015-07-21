<?php

defined( 'IN_MODULE_ACTION' ) or die( 'Access Denied' );
return array(
    'param' => array(
        'name' => '新闻',
        'category' => '信息中心',
        'description' => '提供企业新闻信息发布',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0',
        'pushMovement' => 1,
        'indexShow' => array(
            'widget' => array(
				'article/article'
			),
            'link' => 'article/default/index'
        )
    ),
    'config' => array(
        'modules' => array(
            'article' => array(
                'class' => 'application\modules\article\ArticleModule'
            )
        ),
        'components' => array(
            'ArticleVote' => array(
                'class' => 'application\modules\article\components\ArticleVote',
            ),
            'messages' => array(
                'extensionPaths' => array(
                    'article' => 'application.modules.article.language'
                )
            )
        ),
    ),
	'authorization' => array(
		'view' => array(
			'type' => 'node',
			'name' => '新闻浏览',
			'group' => '新闻',
			'controllerMap' => array(
				'default' => array( 'index' ),
				'category' => array( 'index', 'add', 'edit', 'del' ),
				'comment' => array( 'getcommentlist', 'addcomment', 'delcomment' )
			)
		),
		'publish' => array(
			'type' => 'node',
			'name' => '新闻发布',
			'group' => '新闻',
			'controllerMap' => array(
				'default' => array( 'add' ),
			)
		),
		'manager' => array(
			'type' => 'data',
			'name' => '新闻管理',
			'group' => '新闻',
			'node' => array(
				'edit' => array(
					'name' => '编辑',
					'controllerMap' => array(
						'default' => array( 'edit' )
					)
				),
				'del' => array(
					'name' => '删除',
					'controllerMap' => array(
						'default' => array( 'del' )
					)
				),
			)
		)
	)
);
