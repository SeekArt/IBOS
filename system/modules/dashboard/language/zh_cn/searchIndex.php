<?php

return array(
	0 => array(
		'index' => array( '管理中心首页' => '?r=dashboard/global/index' ),
		'text' => array(
			0 => '管理中心首页',
		)
	),
	1 => array(
		'index' => array( '系统状态' => '?r=dashboard/global/status' ),
		'text' => array(
			0 => '系统状态',
		)
	),
	2 => array(
		'index' => array( '单位管理' => '?r=dashboard/global/unit' ),
		'text' => array(
			0 => '单位管理',
			1 => '单位企业的信息尽在此设置，您可以上传企业的LOGO,填写管理员邮箱等'
		)
	),
	3 => array(
		'index' => array(
			'积分设置' => '?r=dashboard/global/creditsetup',
			'积分公式' => '?r=dashboard/global/creditformula',
			'积分策略' => '?r=dashboard/global/creditrule',
		),
		'text' => array(
			0 => '允许设置扩展积分，变动后提醒',
			1 => '允许设置积分的计算公式',
			3 => '允许设置积分给予周期，配置特定的策略',
		)
	),
	4 => array(
		'index' => array(
			'用户组设置' => '?r=dashboard/global/usergroup',
		),
		'text' => array(
			0 => '允许设置用户组的称谓，配置企业更具个性的用户组群及其积分范围',
		)
	),
	5 => array(
		'index' => array(
			'性能优化' => '?r=dashboard/global/optimizecache',
			'全文搜索设置' => '?r=dashboard/global/optimizesearch',
			'Sphinx设置' => '?r=dashboard/global/optimizesphinx',
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
			'时间设置' => '?r=dashboard/global/date',
		),
		'text' => array(
			0 => '时间设置',
		)
	),
	7 => array(
		'index' => array(
			'上传设置' => '?r=dashboard/global/upload',
		),
		'text' => array(
			0 => '上传设置',
		)
	),
	8 => array(
		'index' => array(
			'手机短信设置' => '?r=dashboard/global/sms&type=setup',
			'短信发送管理' => '?r=dashboard/global/sms&type=manager',
			'模块使用权限' => '?r=dashboard/global/sms&type=access',
		),
		'text' => array(
			0 => '手机短信设置，允许设置手机短信接口及查看余额',
		)
	),
	9 => array(
		'index' => array(
			'即时通讯绑定' => '?r=dashboard/global/im'
		),
		'text' => array(
			0 => 'RTX设置',
		)
	),
	10 => array(
		'index' => array(
			'系统代码设置' => '?r=dashboard/global/syscode'
		),
		'text' => array(
			0 => '系统代码设置',
		)
	),
	11 => array(
		'index' => array(
			'邮件设置' => '?r=dashboard/global/email',
			'邮件校验' => '?r=dashboard/global/email&op=check',
		),
		'text' => array(
			0 => '邮件设置',
			1 => '邮件校验',
		)
	),
	11 => array(
		'index' => array(
			'账户设置' => '?r=dashboard/global/security',
			'运行日志' => '?r=dashboard/global/security&op=log',
			'禁止IP' => '?r=dashboard/global/security&op=ip',
		),
		'text' => array(
			0 => '账户设置',
			1 => '运行日志',
			2 => '禁止IP',
		)
	),
	12 => array(
		'index' => array(
			'系统图章' => '?r=dashboard/global/sysstamp',
		),
		'text' => array(
			0 => '系统图章',
		)
	),
	13 => array(
		'index' => array(
			'导航设置' => '?r=dashboard/interface/nav',
		),
		'text' => array(
			0 => '导航设置',
		)
	),
	14 => array(
		'index' => array(
			'登陆页背景设置' => '?r=dashboard/interface/login',
		),
		'text' => array(
			0 => '登陆页背景设置',
		)
	),
	15 => array(
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
	16 => array(
		'index' => array(
			'更新缓存' => '?r=dashboard/manager/updatecache',
		),
		'text' => array(
			0 => '更新系统缓存',
		)
	),
	17 => array(
		'index' => array(
			'系统公告' => '?r=dashboard/manager/announcement',
		),
		'text' => array(
			0 => '系统公告',
		)
	),
	18 => array(
		'index' => array(
			'数据库' => '?r=dashboard/manager/database',
		),
		'text' => array(
			0 => '数据库备份与恢复',
		)
	),
	18 => array(
		'index' => array(
			'数据库' => '?r=dashboard/manager/database',
			'数据库恢复' => '?r=dashboard/manager/database&op=restore',
			'数据库优化' => '?r=dashboard/manager/database&op=optimize',
		),
		'text' => array(
			0 => '数据库备份',
			1 => '数据库恢复',
			2 => '数据库优化',
		)
	),
	19 => array(
		'index' => array(
			'分表存档' => '?r=dashboard/manager/split&op=manager',
			'邮件分表存档' => '?r=dashboard/manager/split&op=manager&mod=email',
			'日志分表存档' => '?r=dashboard/manager/split&op=manager&mod=diary',
		),
		'text' => array(
			0 => '分表存档',
			1 => '邮件分表存档',
			2 => '日志分表存档',
		)
	),
	19 => array(
		'index' => array(
			'计划任务' => '?r=dashboard/manager/cron',
		),
		'text' => array(
			0 => '计划任务',
		)
	),
	19 => array(
		'index' => array(
			'文件权限检查' => '?r=dashboard/manager/fileperms',
		),
		'text' => array(
			0 => '文件权限检查',
		)
	),
	20 => array(
		'index' => array(
			'在线升级' => '?r=dashboard/manager/upgrade',
		),
		'text' => array(
			0 => '在线升级',
		)
	),
	20 => array(
		'index' => array(
			'IBOS商店' => '?r=dashboard/service/index',
		),
		'text' => array(
			0 => 'IBOS商店',
		)
	),
);