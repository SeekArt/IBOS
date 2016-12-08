<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '核心模块',
        'description' => '系统核心模块。提供IBOS程序核心流程初始化及处理',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0',
        'indexShow' => array(
            'widget' => array(
                'main/voiceConference'
            ),
            'link' => 'main/default/index',
        ),
    ),
    'config' => array(
        'modules' => array(
            'main' => array(
                'class' => 'application\modules\main\MainModule'
            )
        ),
        'components' => array(
            'setting' => array(
                'class' => 'application\modules\main\components\Setting'
            ),
            'session' => array(
                'class' => 'application\modules\main\components\Session'
            ),
            'cron' => array(
                'class' => 'application\modules\main\components\Cron',
            ),
            'process' => array(
                'class' => 'application\modules\main\components\Process',
            ),
            'errorHandler' => array(
                'errorAction' => 'main/default/error',
            ),
            'messages' => array(
                'extensionPaths' => array(
                    'main' => 'application.modules.main.language'
                )
            )
        ),
    ),
    'behaviors' => array(
        'onInitModule' => array(
            'class' => 'application\modules\main\behaviors\InitMainModule'
        )
    ),
);

