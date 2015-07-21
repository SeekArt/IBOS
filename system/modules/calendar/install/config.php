<?php

defined( 'IN_MODULE_ACTION' ) or die( 'Access Denied' );
return array(
	'param' => array(
		'name' => '日程',
		'category' => '个人办公',
		'description' => '提供企业工作日程安排。',
		'author' => 'banyan @ IBOS Team Inc',
		'version' => '1.0',
		'indexShow' => array(
			'widget' => array(
				'calendar/calendar',
				'calendar/task'
			),
			'link' => 'calendar/schedule/index'
		)
	),
	'config' => array(
		'modules' => array(
			'calendar' => array(
				'class' => 'application\modules\calendar\CalendarModule'
			)
		),
		'components' => array(
			'messages' => array(
				'extensionPaths' => array(
					'calendar' => 'application.modules.calendar.language'
				)
			)
		),
	),
	'authorization' => array(
        'schedule' => array(
            'type' => 'node',
            'name' => '日程',
			'group' => '日程安排',
            'controllerMap' => array(
                'schedule' => array( 'index', 'subschedule', 'add', 'edit', 'del' )
            )
        ),
		'task' => array(
            'type' => 'node',
            'name' => '待办',
			'group' => '日程安排',
            'controllerMap' => array(
                'task' => array( 'index', 'subtask', 'add', 'edit', 'del' )
            )
        ),
		'loop' => array(
            'type' => 'node',
            'name' => '周期性事务',
			'group' => '日程安排',
            'controllerMap' => array(
                'loop' => array( 'index', 'add', 'edit', 'del' )
            )
        )
    )
);
