<?php

/**
 * 日程安排模块------周期性事务控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模块------周期性事务控制器，继承CalendarBaseController控制器
 * @package application.modules.calendar.components
 * @version $Id: LoopController.php 1441 2013-10-28 16:48:01Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\calendar\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use CHtml;

Class LoopController extends BaseController
{

    public function init()
    {
        parent::init();
        // 权限检查
        if (!$this->checkIsMe()) {
            $this->error(Ibos::lang('No permission to view loop'), $this->createUrl('loop/index'));
        }
    }

    /**
     * 周期性事务列表
     */
    public function actionIndex()
    {
        //取得周期性事务并分页
        $loopList = Calendars::model()->fetchLoopsAndPage('uid=' . $this->uid . ' AND instancetype = 1');
        $datas = $loopList['datas'];
        $loops = $this->handleLoops($datas);
        $params = array(
            'pages' => $loopList['pages'],
            'loopList' => $loops
        );
        $this->setPageTitle(Ibos::lang('Periodic affairs'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Calendar arrangement'), 'url' => $this->createUrl('loop/index')),
            array('name' => Ibos::lang('Periodic affairs'))
        ));
        $this->render('index', $params);
    }

    /**
     * 处理周期性事务，用于输出显示
     * @param array $loops 周期性事务二维数组
     * @return array  处理过后的二维数组
     */
    private function handleLoops($loops)
    {
        if (!empty($loops)) {
            foreach ($loops as $k => $v) {
                $loops[$k]['subject'] = StringUtil::cutStr($v['subject'], 12);
                $loops[$k]['uptime'] = date('Y-m-d H:i', $v['uptime']);
                $time = date('H:i', $v['starttime']) . '至' . date('H:i', $v['endtime']);
                switch ($v['recurringtype']) {
                    case 'week':
                        $recurringtime = CalendarUtil::digitalToDay($v['recurringtime']);
                        $loops[$k]['cycle'] = '每周' . $recurringtime . ' ' . $time;
                        break;
                    case 'month':
                        $loops[$k]['cycle'] = '每月' . $v['recurringtime'] . '号 ' . $time;
                        break;
                    case 'year':
                        $monthDay = explode('-', $v['recurringtime']);
                        $loops[$k]['cycle'] = '每年' . $monthDay[0] . '月' . $monthDay[1] . '号 ' . $time;
                        break;
                }
            }
        }
        return $loops;
    }

    /**
     * 新增周期性事务
     * 返回状态和信息
     */
    public function actionAdd()
    {
        $data = $this->beforeSave();
        $insertId = Calendars::model()->add($data, true);
        if ($insertId) {
            $loop = Calendars::model()->fetchByPk($insertId);
            $retTemp = $this->handleLoops(array($loop));  //转换成二维数组处理输出数据
            $ret = $retTemp[0];
            $ret['isSuccess'] = true;
        } else {
            $ret['isSuccess'] = false;
        }
        $this->ajaxReturn($ret);
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $editCalendarid = Env::getRequest('editCalendarid');  //编辑的(周期性事务)日程ID
        if (empty($editCalendarid)) {
            $this->error(Ibos::lang('Parameters error', 'error'));
        }
        if ($op == 'geteditdata') {
            $editData = Calendars::model()->fetchEditLoop($editCalendarid);
            $this->ajaxReturn($editData);
        } else {
            $data = $this->beforeSave();
            $editSuccess = Calendars::model()->modify($editCalendarid, $data);
            if ($editSuccess) {
                $retTemp = $this->handleLoops(array($data));  //转换成二维数组处理输出数据
                $ret = $retTemp[0];
            }
            $ret['isSuccess'] = $editSuccess;
            $this->ajaxReturn($ret);
        }
    }

    /**
     * 删除周期性事务
     */
    public function actionDel()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('schedule/index'));
        }
        $delCalendarid = Env::getRequest('delCalendarid');
        if (empty($delCalendarid)) {
            $this->error(Ibos::lang('Parameters error', 'error'));
        }
        $delArr = explode(',', $delCalendarid);
        foreach ($delArr as $calendarid) {
            Calendars::model()->remove($calendarid);
        }
        $ret['isSuccess'] = true;
        $this->ajaxReturn($ret);
    }

    /**
     * 异步添加和编辑所提交的数据
     * @return array  返回处理过后的数据数组
     */
    private function beforeSave()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('schedule/index'));
        }
        $subject = Env::getRequest('subject');  //循环事务内容
        $starttime = Env::getRequest('starttime');  //循环开始时间
        $endtime = Env::getRequest('endtimes');  //循环结束时间
        $category = Env::getRequest('category');  //颜色分类
        $getSetday = Env::getRequest('setday');  //添加循环事务的日期,默认是当前日期
        $setday = empty($getSetday) ? date('Y-m-d') : $getSetday;  //
        $reply = Env::getRequest('reply');
        $getRBegin = Env::getRequest('recurringbegin');
        $rBegin = empty($getRBegin) ? time() : strtotime($getRBegin);
        $getREnd = Env::getRequest('recurringend');
        $rEnd = empty($getREnd) ? 0 : strtotime($getREnd);
        $rType = Env::getRequest('recurringtype');
        $subject = CHtml::encode($subject);
        $data = array(
            'uid' => $this->uid,
            'subject' => empty($subject) ? '无标题的活动' : $subject, //日程内容
            'uptime' => time(), //添加日期
            'starttime' => empty($starttime) ? time() : strtotime($setday . ' ' . $starttime), //开始时间
            'endtime' => empty($endtime) ? strtotime($setday . ' 23:59:59') : strtotime($setday . ' ' . $endtime), //结束时间
            'category' => empty($category) ? '-1' : $category, //颜色类型
            'upuid' => $this->uid // 添加人id
        );
        if ($data['starttime'] > $data['endtime']) { //如果开始时间大于结束时间，则交换两个时间
            $bigtime = $data['starttime'];
            $data['starttime'] = $data['endtime'];
            $data['endtime'] = $bigtime;
        }
        if ($reply == 'true') { //是否为周期性日程
            $data['instancetype'] = '1';
            $data['recurringbegin'] = $rBegin; //周期开始时间
            $data['recurringend'] = $rEnd; //周期结束时间
            if ($data['recurringbegin'] > $data['recurringend'] && $data['recurringend'] != 0) { //如果周期开始时间大于周期结束时间，则交换两个时间
                $bigtime = $data['recurringbegin'];
                $data['recurringbegin'] = $data['recurringend'];
                $data['recurringend'] = $bigtime;
            }
            $data['recurringtype'] = $rType;
            switch ($data['recurringtype']) {
                case 'week':
                    $getWeekbox = Env::getRequest('weekbox');
                    $weekbox = empty($getWeekbox) ? '1,2,3,4,5,6,7' : $getWeekbox;
                    $data['recurringtime'] = $weekbox;
                    break;
                case 'month':
                    $data['recurringtime'] = Env::getRequest('month');
                    break;
                case 'year':
                    $data['recurringtime'] = Env::getRequest('year');
                    break;
            }
        }
        return $data;
    }

}
