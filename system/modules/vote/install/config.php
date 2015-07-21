<?php

defined( 'IN_MODULE_ACTION' ) or die( 'Access Denied' );
return array(
    'param' => array(
        'name' => '投票模块',
        'description' => '提供信息中心等模块投票调查使用',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'vote' => array( 'class' => 'application\modules\vote\VoteModule' )
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'vote' => 'application.modules.vote.language'
                )
            )
        ),
    )
);
