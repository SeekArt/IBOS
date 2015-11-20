<?php

\application\core\utils\Cache::set( 'notifyNode', null );

//清除评论和回复
$assignmentComments = \application\modules\message\model\Comment::model()->fetchAllByAttributes(array('module'=>'assignment'));
$cidArr = \application\core\utils\Convert::getSubByKey($assignmentComments, 'cid');
if(!empty($assignmentComments)){
	$cidStr = implode( ',', $cidArr );
	\application\modules\message\model\Comment::model()->deleteAll("rowid IN({$cidStr})");
	\application\modules\message\model\Comment::model()->deleteAllByAttributes(array('module'=>'assignment'));
}