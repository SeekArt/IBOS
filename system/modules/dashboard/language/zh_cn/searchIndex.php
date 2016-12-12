<?php

return array(
    0 => array(
        'index' => array('管理中心首页' => '?r=dashboard/index/index'),
        'text' => array(
            0 => '管理中心首页',
        )
    ),
    1 => array(
        'index' => array('系统状态' => '?r=dashboard/status/index'),
        'text' => array(
            0 => '系统状态',
        )
    ),
    2 => array(
        'index' => array('单位管理' => '?r=dashboard/unit/index'),
        'text' => array(
            0 => '单位管理',
            1 => '单位企业的信息尽在此设置，您可以上传企业的LOGO,填写管理员邮箱等'
        )
    ),
    3 => array(
        'index' => array(
            '积分设置' => '?r=dashboard/credit/setup',
            '积分公式' => '?r=dashboard/credit/formula',
            '积分策略' => '?r=dashboard/credit/rule',
        ),
        'text' => array(
            0 => '允许设置扩展积分，变动后提醒',
            1 => '允许设置积分的计算公式',
            3 => '允许设置积分给予周期，配置特定的策略',
        )
    ),
    4 => array(
        'index' => array(
            '用户组设置' => '?r=dashboard/usergroup/index',
        ),
        'text' => array(
            0 => '允许设置用户组的称谓，配置企业更具个性的用户组群及其积分范围',
        )
    ),
    5 => array(
        'index' => array(
            '内存优化' => '?r=dashboard/optimize/cache',
            '全文搜索设置' => '?r=dashboard/optimize/search',
            'Sphinx设置' => '?r=dashboard/optimize/sphinx',
        ),
        'text' => array(
            0 => '清空缓存，查看缓存使用清空',
            1 => '允许全文搜索',
            2 => '此设置只对搜索特定模块有效。注意: 当数据量大时，全文搜索将非常耗费服务器资源，请慎用',
            3 => 'Sphinx 全文检索设置',
            4 => '设置是否开启 Sphinx 全文检索功能，开启前确认 Sphinx 安装及配置成功',
            5 => 'settings_sphinx_sphinxhost',
            6 => '设置 Sphinx 主机名，或者 Sphinx 服务 socket 地址',
            7 => '填写 Sphinx 主机名：例如，本地主机填写“localhost”，或者填写 Sphinx 服务 socket 地址，必须是绝对地址：例如，/tmp/sphinx.sock',
            8 => '设置 Sphinx 主机端口',
            9 => '填写 Sphinx 主机端口：例如，3312，主机名填写 socket 地址的，则此处不需要设置',
            10 => '设置标题索引名',
            11 => '填写 Sphinx 配置中的标题主索引名及标题增量索引名。注意：多个索引使用半角逗号 "," 隔开，必须按照 Sphinx 配置文件中的索引名填写',
            12 => '设置全文索引名',
            13 => '填写 Sphinx 配置中的全文主索引名及全文增量索引名。注意：多个索引使用半角逗号 "," 隔开，必须按照 Sphinx 配置文件中的索引名填写',
        )
    ),
    6 => array(
        'index' => array(
            '时间设置' => '?r=dashboard/date/index',
        ),
        'text' => array(
            0 => '时间设置',
        )
    ),
    7 => array(
        'index' => array(
            '上传设置' => '?r=dashboard/upload/index',
        ),
        'text' => array(
            0 => '上传设置',
        )
    ),
    8 => array(
        'index' => array(
            '手机短信设置' => '?r=dashboard/sms/manager&type=setup',
        ),
        'text' => array(
            0 => '手机短信设置，允许设置手机短信接口及查看余额',
        )
    ),
    9 => array(
        'index' => array(
            '即时通讯绑定' => '?r=dashboard/im/index'
        ),
        'text' => array(
            0 => 'RTX设置',
        )
    ),
    10 => array(
        'index' => array(
            '系统代码设置' => '?r=dashboard/syscode/index'
        ),
        'text' => array(
            0 => '系统代码设置',
        )
    ),
    11 => array(
        'index' => array(
            '邮件设置' => '?r=dashboard/email/setup',
            '邮件校验' => '?r=dashboard/email/check',
        ),
        'text' => array(
            0 => '邮件设置',
            1 => '邮件校验',
        )
    ),
    12 => array(
        'index' => array(
            '账户设置' => '?r=dashboard/security/setup',
            '运行日志' => '?r=dashboard/security/log',
            '禁止IP' => '?r=dashboard/security/ip',
        ),
        'text' => array(
            0 => '账户设置',
            1 => '运行日志',
            2 => '禁止IP',
        )
    ),
    13 => array(
        'index' => array(
            '系统图章' => '?r=dashboard/sysstamp/index',
        ),
        'text' => array(
            0 => '系统图章',
        )
    ),
    14 => array(
        'index' => array(
            '导航设置' => '?r=dashboard/nav/index',
        ),
        'text' => array(
            0 => '导航设置',
        )
    ),
    15 => array(
        'index' => array(
            '登录页背景设置' => '?r=dashboard/login/index',
        ),
        'text' => array(
            0 => '登录页背景设置',
        )
    ),
    16 => array(
        'index' => array(
            '模块中心' => '?r=dashboard/module/manager',
            '安装模块' => '?r=dashboard/module/manager&op=installed',
            '未安装模块' => '?r=dashboard/module/manager&op=uninstalled',
        ),
        'text' => array(
            0 => '模块中心',
            1 => '已安装模块',
            2 => '未安装模块',
        )
    ),
    17 => array(
        'index' => array(
            '更新缓存' => '?r=dashboard/update/index',
        ),
        'text' => array(
            0 => '更新系统缓存',
        )
    ),
    18 => array(
        'index' => array(
            '系统公告' => '?r=dashboard/announcement/setup',
        ),
        'text' => array(
            0 => '系统公告',
        )
    ),
    19 => array(
        'index' => array(
            '数据库' => '?r=dashboard/database/backup',
            '数据库恢复' => '?r=dashboard/database/restore',
            '数据库优化' => '?r=dashboard/database/optimize',
        ),
        'text' => array(
            0 => '数据库备份',
            1 => '数据库恢复',
            2 => '数据库优化',
        )
    ),
    20 => array(
        'index' => array(
            '计划任务' => '?r=dashboard/cron/index',
        ),
        'text' => array(
            0 => '计划任务',
        )
    ),
    21 => array(
        'index' => array(
            '在线升级' => '?r=dashboard/upgrade/index',
        ),
        'text' => array(
            0 => '在线升级',
        )
    ),
    22 => array(
        'index' => array(
            'IBOS商店' => '?r=dashboard/service/index',
        ),
        'text' => array(
            0 => 'IBOS商店',
        )
    ),
);
