<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '信息中心',
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
            'name' => '信息查看',
            'group' => '新闻',
            'controllerMap' => array(
                'comment' => array('getcommentlist', 'addcomment', 'delcomment', 'getcommentview'),
                'category' => array('index'),
               // 'index' => array('index', 'show', 'preview', 'vote', 'getreader', 'getcount', 'read'),
                'default' => array('index', 'show', 'preview', 'vote', 'getreader', 'getcount', 'read'),
                'data' => array('index', 'show', 'preview'),
                'verify' => array('flowlog'),
            )
        ),
        'verify' => array(
            'type' => 'node',
            'name' => '信息审核',
            'group' => '新闻',
            'controllerMap' => array(
                'verify' => array('index', 'verify', 'back', 'index', 'cancel'),
            ),
        ),
        'publish' => array(
            'type' => 'node',
            'name' => '信息发布',
            'group' => '新闻',
            'controllerMap' => array(
                'publish' => array('index', 'call', 'cancel'),
               // 'index' => array('add', 'vote', 'submit'),
                'default' => array('add', 'vote', 'submit'),
                'data' => array('edit', 'option'),
                'category' => array('getcurapproval'),
            )
        ),
        'category' => array(
            'type' => 'node',
            'name' => '分类管理',
            'group' => '新闻',
            'controllerMap' => array(
                'category' => array('add', 'edit', 'del', 'move', 'index',),
            ),
        ),
        'manager' => array(
            'type' => 'data',
            'name' => '内容管理',
            'group' => '新闻',
            'node' => array(
                'edit' => array(
                    'name' => '编辑',
                    'controllerMap' => array(
                        //'index' => array('edit', 'top', 'move', 'hightlight', 'save', 'getmove', 'vote', 'submit'),
                        'default' => array('edit', 'top', 'move', 'hightlight', 'save', 'getmove', 'vote', 'submit'),
                        'data' => array('edit', 'option'),
                    )
                ),
                'del' => array(
                    'name' => '删除',
                    'controllerMap' => array(
                        //'index' => array('delete'),
                        'default' => array('delete'),
                    )
                ),
            ),
            'controllerMap' => array(
                'category' => array('add', 'edit', 'del'),
            ),
        )
    )
);
