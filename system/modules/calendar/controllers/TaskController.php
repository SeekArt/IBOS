<?php

/**
 * 日程安排模块------任务控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模块------任务控制器，继承CalendarBaseController控制器
 * @package application.modules.calendar.components
 * @version $Id: TaskController.php 1441 2013-10-28 16:48:01Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\calendar\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS; 
use application\modules\calendar\model\Calendars;
use application\modules\calendar\model\Tasks;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CHtml;

Class TaskController extends BaseController {

    /**
     * 查询条件
     * @var string 
     * @access protected 
     */
    private $_condition;
    private $complete;

    /**
     * 个人任务列表
     */
    public function actionIndex() {
        // 权限判断
        if ( !$this->checkIsMe() ) {
            $this->error( IBOS::lang( 'No permission to view task' ), $this->createUrl( 'task/index' ) );
        }
        $postComp = Env::getRequest( 'complete' );
        $this->complete = empty( $postComp ) ? 0 : $postComp;
        //是否搜索
        if (Env::getRequest('param') == 'search' && IBOS::app()->request->isPostRequest) {
            $this->search();
        }
        $this->_condition = CalendarUtil::joinCondition( $this->_condition, "uid = " . $this->uid );
        $data = Tasks::model()->fetchTaskByComplete( $this->_condition, $this->complete );
        $data['complete'] = $this->complete;
        $data['user'] = User::model()->fetchByUid( $this->uid );
        $this->setPageTitle( IBOS::lang( 'Personal task' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Calendar arrangement' ), 'url' => $this->createUrl( 'schedule/index' ) ),
            array( 'name' => IBOS::lang( 'Personal task' ) )
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 下属任务列表
     */
    public function actionSubTask() {
        // 权限判断
        if ( !UserUtil::checkIsSub( IBOS::app()->user->uid, $this->uid ) ) {
            $this->error( IBOS::lang( 'No permission to view task' ), $this->createUrl( 'task/index' ) );
        }
        $postComp = Env::getRequest( 'complete' );
        $this->complete = empty( $postComp ) ? 0 : $postComp;
        //是否搜索
        if ( Env::getRequest( 'param' ) == 'search' ) {
            $this->search();
        }
        $this->_condition = CalendarUtil::joinCondition( $this->_condition, "uid = " . $this->uid );
        $data = Tasks::model()->fetchTaskByComplete( $this->_condition, $this->complete );
        $data['complete'] = $this->complete;
        $data['user'] = User::model()->fetchByUid( $this->uid );
        $data['supUid'] = UserUtil::getSupUid( $this->uid ); //获取上司uid
        $data['allowEditTask'] = CalendarUtil::getIsAllowEidtTask();
        $this->setPageTitle( IBOS::lang( 'Subordinate task' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Calendar arrangement' ), 'url' => $this->createUrl( 'schedule/index' ) ),
            array( 'name' => IBOS::lang( 'Subordinate task' ) )
        ) );
        $this->render( 'subtask', $data );
    }

    /**
     * 添加任务
     */
    public function actionAdd() {
        if (Env::submitCheck('formhash')) {
            // 权限判断
            if (!$this->checkTaskPermission()) {
                $this->error(IBOS::lang('No permission to add task'), $this->createUrl('task/index'));
            }
            $postData = $_POST;
            $postData['text'] = CHtml::encode($postData['text']);
            $postData['upuid'] = $this->upuid;
            $postData['uid'] = $this->uid;
            $postData['addtime'] = time();
            if (!isset($postData['pid'])) {
                $count = Tasks::model()->count('pid=:pid', array(':pid' => ''));
                $postData['sort'] = $count + 1;
            }
            Tasks::model()->add($postData, true);
            // 消息提醒(当添加人是上司时)
            if ($this->upuid != $this->uid) {
                //添加对$_POST['text']的xss安全过滤
                $config = array(
                    '{sender}' => User::model()->fetchRealnameByUid($this->upuid),
                    '{subject}' => htmlspecialchars($_POST['text']),
                    '{url}' => IBOS::app()->urlManager->createUrl('calendar/task/index')
                );
                Notify::model()->sendNotify( $this->uid, 'task_message', $config, $this->upuid );
            }
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     * 处理任务
     */
    public function actionEdit() {
        if ( Env::submitCheck( 'formhash' ) ) {
            // 权限判断
            if ( !$this->checkTaskPermission() ) {
                $this->error( IBOS::lang( 'No permission to edit task' ), $this->createUrl( 'task/index' ) );
            }
            $op = Env::getRequest( 'op' );
            $id = Env::getRequest( 'id' );
            switch ( $op ) {
                case 'mark':
                    $mark = Env::getRequest( 'mark' );
                    Tasks::model()->modifyTasksMark( $id, $mark );
                    break;
                case 'complete':
                    $complete = Env::getRequest( 'complete' );
                    Tasks::model()->modifyTasksComplete( $id, $complete );
                    Tasks::model()->updateCalendar( $id, $complete );
                    break;
                case 'save':
                    $text = Env::getRequest('text');
                    $text = CHtml::encode($text);
                    Tasks::model()->modify( $id, array( 'text' => $text ) );
                    //若已存在与日程，则改变相应日程的主题
                    $schedule = Calendars::model()->fetchByAttributes( array( 'taskid' => $id ) );
                    if ( !empty( $schedule ) ) {
                        Calendars::model()->modify( $schedule['calendarid'], array( 'subject' => $text ) );
                    }
                    break;
                case 'date':
                    $date = Env::getRequest( 'date' );
                    Tasks::model()->modify( $id, array( 'date' => date( 'Y-m-d', $date ) ) );
                    if ( $date ) {  //如果设定了完成时间，就添加或者修改相应的日程
                        $data = Tasks::model()->handleCalendar( $id );
                        $schedule = Calendars::model()->fetchByAttributes( array( 'taskid' => $id ) );
                        if ( empty( $schedule ) ) {  //若不存在这条日程，就添加
                            Calendars::model()->add( $data );
                        } else {  //否则就修改这条日程的时间
                            $task = Tasks::model()->fetchByPk( $id );
                            $data['status'] = $task['complete'] ? 1 : 0;
                            Calendars::model()->modify( $schedule['calendarid'], $data );
                        }
                    } else {  //如果删除了完成时间，则删除对应的日程
                        $this->delCalendarByTaskid( $id );
                    }
                    break;
                case 'sort':
                    $currentId = Env::getRequest( 'currentId' );
                    $targetId = Env::getRequest( 'targetId' );
                    $type = Env::getRequest( 'type' );
                    $this->sortTask( $currentId, $targetId, $type );
                    break;
            }
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     * 删除任务
     */
    public function actionDel() {
        if ( Env::submitCheck( 'formhash' ) ) {
            // 权限判断
            if ( !$this->checkTaskPermission() ) {
                $this->error( IBOS::lang( 'No permission to del task' ), $this->createUrl( 'task/index' ) );
            }
            $id = Chtml::encode($_POST['id']);
            Tasks::model()->removeTasksById( $id );
            Calendars::model()->deleteAllByAttributes( array( 'taskid' => $id ) );
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     * 如果删除带有完成时间的任务，则把相应的日程也删除
     * @param string $taskid  任务ID
     */
    private function delCalendarByTaskid( $taskid ) {
        $schedule = Calendars::model()->fetchByAttributes( array( 'taskid' => $taskid ) );
        if ( !empty( $schedule ) ) {
            Calendars::model()->remove( $schedule['calendarid'] );
        }
    }

    /**
     * 任务拖拽排序
     * @param string $currentId  拖拽的任务ID
     * @param string $targetId  参照任务ID
     * @param string $type down为拖到参照任务的下面，up为拖到参照任务的上面
     */
    private function sortTask( $currentId, $targetId, $type ) {
        $current = Tasks::model()->fetchByPk( $currentId );  //拖拽要改变顺序的任务
        $target = Tasks::model()->fetchByPk( $targetId );  //参照的任务
        $cSort = $current['sort'];
        $tSort = $target['sort'];
        if ( $type == 'up' && ($cSort - $tSort) != 1 ) { //排除拖拽过程中又重新拖回原来位置
            //要重新排序的任务
            Tasks::model()->updateCounters( array( 'sort' => -1 ), "sort BETWEEN ($cSort+1) AND $tSort" );
            Tasks::model()->modify( $currentId, array( 'sort' => $tSort ) );
        } elseif ( $type == 'down' && ( $tSort - $cSort) != 1 ) {
            Tasks::model()->updateCounters( array( 'sort' => +1 ), "sort BETWEEN $tSort AND ($cSort-1)" );
            Tasks::model()->modify( $currentId, array( 'sort' => $tSort ) );
        } else {
            return;
        }
    }

    /**
     * 搜索
     * @return void
     */
    private function search() {
        $uid = $this->uid;
        $complete = $this->complete;
        $type = Env::getRequest( 'type' );
        $conditionCookie = MainUtil::getCookie( 'condition' );
        if ( empty( $conditionCookie ) ) {
            MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
        }
        if ( $type == 'normal_search' ) {
            $keyword = CHtml::encode(Env::getRequest('keyword'));
            MainUtil::setCookie( 'keyword', $keyword, 10 * 60 );
            //第一种先把父任务有关键字的找出来
            $pTasks = Tasks::model()->fetchPTasks( $uid, $complete, $keyword );
            //第二种把子任务有关键字的找出来，取得所有的父ID
            $cTasks = Tasks::model()->fetchCTasks( $uid, $complete, $keyword );
            $array = array();
            foreach ( $pTasks as $task ) {
                $array[] = $task['id'];
            }
            foreach ( $cTasks as $task ) {
                $array[] = $task['pid'];
            }
            $pids = array_unique( $array );  //去掉重复的父ID
            $pidTemp = '';
            foreach ( $pids as $v ) { //把pid数组转换成逗号隔开的字符串形式，用于sql的in查询
                $pidTemp .= '"' . $v . '",';
            }
            $pidStr = rtrim( $pidTemp, ',' );
            if ( !empty( $pidStr ) ) {
                $this->_condition = " uid='{$uid}' AND id IN($pidStr) AND allcomplete='{$complete}'";
            } else {
                $this->_condition = " uid='{$uid}' AND id IN('') AND allcomplete='{$complete}'";
            }
        } else {
            $this->_condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ( $this->_condition != MainUtil::getCookie( 'condition' ) ) {
            MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
        }
    }

}
