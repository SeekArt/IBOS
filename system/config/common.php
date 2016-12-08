<?php

/**
 * 程序启动配置文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
return array(
    // 程序根目录
    'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system',
    // 程序名称
    'name' => 'IBOS',
    // 默认控制器 - 主模块下的index
    'defaultController' => 'main/default/index',
    // 框架核心语言
    'sourceLanguage' => 'en_us',
    // 定义所用组件
    'components' => array(
        // --------- 全局与系统组件 ---------
        // 浏览器组件，检测用户浏览器版本及信息
        'browser' => array('class' => 'application\core\components\Browser'),
        // 分类组件
        'category' => array('class' => 'application\core\components\Category'),
        'request' => array('class' => 'application\core\components\Request'),
        // 基础数据库配置，详细的会在engine配置
        'db' => array(
            'enableProfiling' => YII_DEBUG,
            'emulatePrepare' => true,
            'enableParamLogging' => false
        ),
        // 日志记录组件
        'log' => array(
            'class' => '\CLogRouter',
            'routes' => array(
                array(
                    'class' => '\CFileLogRoute',
                    'levels' => 'error',
                ),
                array(
                    'class' => 'application\core\components\Log',
                    'levels' => 'admincp,illegal,login,action,db',
                )
            ),
        ),
        // 全局认证组件
        'authManager' => array(
            'class' => 'application\core\components\AuthManager'
        ),
        // 主题管理组件
        'themeManager' => array(
            'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system/theme',
            'class' => 'application\core\components\ThemeManager',
        ),
        // 资源管理组件
        'assetManager' => array('class' => 'application\core\components\AssetManager'),
        // URL资源管理器
        'urlManager' => array(
            'urlFormat' => 'get',
            'caseSensitive' => false,
            'showScriptName' => false,
            'rules' => array(
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>', // Not Coding Standard
            )
        ),
        //语言包基本目录和扩展目录配置
        'messages' => array(
            'class' => 'application\core\components\MessageSource',
            'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system/language'
        ),
        // 性能检测组件，部署模式可删除掉这行
        'performance' => array(
            'class' => 'application\core\components\PerformanceMeasurement'
        ),
        // 全局缓存组件
        'cache' => array(
            //如果是saas版，还需要配置redis服务器设置
            'class' => defined('SAAS_STORAGE') ? '\CRedisCache' : '\CFileCache',
        ),
    ),
    'params' => array(
        // Yii版本
        'yiiVersion' => '1.1.17',
        'supportedLanguages' => array(
            'en' => 'English',
            'cn' => 'zh-cn',
        ),
        // 默认翻每页的页数
        'basePerPage' => 10,
        // 等待跳转时间
        'timeout' => 3,
        'incentiveword' => false,
    ),
    'preload' => array(
        'db', 'cache', 'log'
    ),
);
