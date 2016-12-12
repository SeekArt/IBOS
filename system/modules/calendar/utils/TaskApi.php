<?php
namespace application\modules\calendar\utils;

use application\core\utils\Ibos;

class TaskApi
{

    /**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex()
    {
        $data = array(
            'taskList' => $this->loadNewTask(),
            'lant' => Ibos::getLangSource('calendar.default'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('calendar')
        );
        $viewAlias = 'application.modules.calendar.views.indexapi.task';
        $return['calendar/task'] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        return array(
            'name' => 'calendar/task',
            'title' => Ibos::lang('Task', 'calendar.default'),
            'style' => 'in-calendar'
        );
    }

    /**
     * 获取最新待办 不作处理，返回0
     * @return integer
     */
    public function loadNew()
    {
        return intval(0);
    }

    /**
     * 获取最新的5条待办
     */
    public function LoadNewTask()
    {
        $uid = Ibos::app()->user->uid;
        $task = Ibos::app()->db->createCommand()
            ->select("*")
            ->from("{{tasks}}")
            ->where("uid = {$uid} order by addtime desc limit 5")
            ->queryAll();
        return $task;
    }
}

