<?php

defined('IN_MODULE_ACTION') or die('Access Denied');
return array(
    'param' => array(
        'name' => '后台管理',
        'category' => '权限列表',
        'description' => '提供IBOS后台管理所需功能',
        'author' => 'banyanCheung @ IBOS Team Inc',
        'version' => '1.0'
    ),
    'config' => array(
        'modules' => array(
            'dashboard' => array('class' => 'application\modules\dashboard\DashboardModule')
        ),
        'components' => array(
            'messages' => array(
                'extensionPaths' => array(
                    'dashboard' => 'application.modules.dashboard.language'
                )
            )
        ),
    ),
    'authorization' => array(
        'cobindings' => array(
            'type' => 'node',
            'name' => '酷办公绑定',
            'group' => '绑定',
            'controllerMap' => array(
                'cobinding' => array('index'),
                'cosync' => array('index'),
            )
        ),
        'wxbindings' => array(
            'type' => 'node',
            'name' => '微信企业号绑定',
            'group' => '绑定',
            'controllerMap' => array(
                'wxbinding' => array('index'),
            )
        ),
        'ims' => array(
            'type' => 'node',
            'name' => '即时通讯绑定',
            'group' => '绑定',
            'controllerMap' => array(
                'im' => array('index'),
            )
        ),
        'globals' => array(
            'type' => 'node',
            'name' => '单位管理',
            'group' => '全局',
            'controllerMap' => array(
                'unit' => array('index'),
            )
        ),
        'credits' => array(
            'type' => 'node',
            'name' => '积分设置',
            'group' => '全局',
            'controllerMap' => array(
                'credit' => array('setup'),
            )
        ),
        'usergroups' => array(
            'type' => 'node',
            'name' => '用户组',
            'group' => '全局',
            'controllerMap' => array(
                'usergroup' => array('index'),
            )
        ),
        'optimizes' => array(
            'type' => 'node',
            'name' => '性能优化',
            'group' => '全局',
            'controllerMap' => array(
                'optimize' => array('cache'),
            )
        ),
        'dates' => array(
            'type' => 'node',
            'name' => '时间设置',
            'group' => '全局',
            'controllerMap' => array(
                'date' => array('index'),
            )
        ),
        'uploads' => array(
            'type' => 'node',
            'name' => '上传设置',
            'group' => '全局',
            'controllerMap' => array(
                'upload' => array('index'),
            )
        ),
        'smss' => array(
            'type' => 'node',
            'name' => '手机短信设置',
            'group' => '全局',
            'controllerMap' => array(
                'sms' => array('manager'),
            )
        ),
        'syscodes' => array(
            'type' => 'node',
            'name' => '系统代码设置',
            'group' => '全局',
            'controllerMap' => array(
                'syscode' => array('index'),
            )
        ),
        'emails' => array(
            'type' => 'node',
            'name' => '邮件设置',
            'group' => '全局',
            'controllerMap' => array(
                'email' => array('setup'),
            )
        ),
        'securitys' => array(
            'type' => 'node',
            'name' => '安全设置',
            'group' => '全局',
            'controllerMap' => array(
                'security' => array('setup'),
            )
        ),
        'sysstamps' => array(
            'type' => 'node',
            'name' => '系统图章',
            'group' => '全局',
            'controllerMap' => array(
                'sysstamp' => array('index'),
            )
        ),
        'approvals' => array(
            'type' => 'node',
            'name' => '审批流程',
            'group' => '全局',
            'controllerMap' => array(
                'approval' => array('index'),
            )
        ),
        'notifys' => array(
            'type' => 'node',
            'name' => '提醒策略设置',
            'group' => '全局',
            'controllerMap' => array(
                'notify' => array('setup'),
            )
        ),
        'users' => array(
            'type' => 'node',
            'name' => '部门用户管理',
            'group' => '用户',
            'controllerMap' => array(
                'user' => array('index'),
            )
        ),
        'roles' => array(
            'type' => 'node',
            'name' => '角色权限管理',
            'group' => '用户',
            'controllerMap' => array(
                'role' => array('index'),
            )
        ),
        'positions' => array(
            'type' => 'node',
            'name' => '岗位管理',
            'group' => '用户',
            'controllerMap' => array(
                'position' => array('index'),
            )
        ),
        'roleadmins' => array(
            'type' => 'node',
            'name' => '管理员管理',
            'group' => '用户',
            'controllerMap' => array(
                'roleadmin' => array('index'),
            )
        ),
        'navs' => array(
            'type' => 'node',
            'name' => '顶部导航设置',
            'group' => '界面',
            'controllerMap' => array(
                'nav' => array('index'),
            )
        ),
        'quicknavs' => array(
            'type' => 'node',
            'name' => '快捷导航设置',
            'group' => '界面',
            'controllerMap' => array(
                'quicknav' => array('index'),
            )
        ),
        'logins' => array(
            'type' => 'node',
            'name' => '登录页背景设置',
            'group' => '界面',
            'controllerMap' => array(
                'login' => array('index'),
            )
        ),
        'backgrounds' => array(
            'type' => 'node',
            'name' => '系统背景设置',
            'group' => '界面',
            'controllerMap' => array(
                'backgroud' => array('index'),
            )
        ),
        'modules' => array(
            'type' => 'node',
            'name' => '模块管理',
            'group' => '模块',
            'controllerMap' => array(
                'module' => array('manager'),
            )
        ),
        'permissionss' => array(
            'type' => 'node',
            'name' => '权限设置',
            'group' => '模块',
            'controllerMap' => array(
                'permissions' => array('setup'),
            )
        ),
        'updates' => array(
            'type' => 'node',
            'name' => '更新缓存',
            'group' => '管理',
            'controllerMap' => array(
                'update' => array('index'),
            )
        ),
        'announcements' => array(
            'type' => 'node',
            'name' => '系统公告',
            'group' => '管理',
            'controllerMap' => array(
                'announcement' => array('setup'),
            )
        ),
        'databases' => array(
            'type' => 'node',
            'name' => '数据库',
            'group' => '管理',
            'controllerMap' => array(
                'database' => array('backup'),
            )
        ),
        'crons' => array(
            'type' => 'node',
            'name' => '计划任务',
            'group' => '管理',
            'controllerMap' => array(
                'cron' => array('index'),
            )
        ),
        'upgrades' => array(
            'type' => 'node',
            'name' => '在线升级',
            'group' => '管理',
            'controllerMap' => array(
                'upgrade' => array('index'),
            )
        ),
        'services' => array(
            'type' => 'node',
            'name' => '云服务',
            'group' => '服务',
            'controllerMap' => array(
                'service' => array('index'),
            )
        ),
    ),
);
