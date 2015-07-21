<?php
namespace application\modules\calendar\utils;

use application\core\utils\IBOS;

class TaskApi{
	
	/**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex() {
        $data = array(
            'taskList' => $this->loadNewTask(),
            'lant' => IBOS::getLangSource( 'calendar.default' ),
            'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'calendar' )
        );
        $viewAlias = 'application.modules.calendar.views.indexapi.task';
        $return['calendar/task'] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        return array(
            'name' => 'calendar/task',
            'title' => IBOS::lang( 'Task', 'calendar.default' ),
            'style' => 'in-calendar'
        );
    }

    /**
     * 获取最新待办 不作处理，返回0
     * @return integer
     */
    public function loadNew() {
        return intval( 0 );
    }
	
	/**
	 * 获取最新的5条待办
	 */
	public function LoadNewTask(){
		$uid = IBOS::app()->user->uid;
		$task = IBOS::app()->db->createCommand()
				->select( "*" )
				->from( "{{tasks}}" )
				->where( "uid = {$uid} order by addtime desc limit 5")
				->queryAll();
		return $task;
	}
}

