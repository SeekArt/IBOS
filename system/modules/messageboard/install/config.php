<?php
array(
        'name' => '留言板',
        'description' => '这是一个简单而经典的程序',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'configure' => array(
        'modules' => array(
            'messageboard'
        ),
        'import' => array(
            'application.modules.messageboard.controllers.*',
            'application.modules.messageboard.model.*',
        )
    ),
);