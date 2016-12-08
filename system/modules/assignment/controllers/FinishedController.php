<?php

/**
 * 任务指派模块------已完成任务控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 任务指派模块------已完成任务控制器，继承AssignmentBaseController
 * @package application.modules.assignment.controllers
 * @version $Id: FinishedController.php 3297 2014-04-29 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\model\User;

class FinishedController extends BaseController
{

    // 查询条件
    protected $_condition;

    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        //是否搜索，并且必须是post类型请求
        if (Env::getRequest('param') == 'search' && Ibos::app()->request->isPostRequest) {
            $this->search();
        }
        // 完成任务查看条件，指派人或负责人或参与人
        $this->_condition = AssignmentUtil::joinCondition($this->_condition, "(`status` = 2 OR `status` = 3) AND (`designeeuid` = {$uid} OR `chargeuid` = {$uid} OR FIND_IN_SET({$uid}, `participantuid`))");
        $data = Assignment::model()->fetchAllAndPage($this->_condition);
        $data['datas'] = AssignmentUtil::handleListData($data['datas'], $uid);

        $data['datas'] = $this->groupByFinishtime($data['datas']);
        $this->setPageTitle(Ibos::lang('Assignment'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Assignment'), 'url' => $this->createUrl('unfinished/index')),
            array('name' => Ibos::lang('Finished list'))
        ));
        $this->render('list', $data);
    }

    /**
     * 按完成时间分组处理显示数据
     * @param array $datas 已完成的任务数组
     * @return array
     */
    protected function groupByFinishtime($datas)
    {
        $res = array();
        foreach ($datas as $k => $v) {
            $finishDate = strtotime(date('Y-m-d', $v['finishtime']));
            if ($k > 0 && strtotime(date('Y-m-d', $datas[$k - 1]['finishtime'])) == $finishDate) {
                $preFinishDate = strtotime(date('Y-m-d', $datas[$k - 1]['finishtime']));
                $res[$preFinishDate][] = $v;
            } else {
                $res[$finishDate][] = $v;
            }
        }
        return $res;
    }

    /**
     * 搜索
     * @return void
     */
    protected function search()
    {
        $conditionCookie = MainUtil::getCookie('condition');
        if (empty($conditionCookie)) {
            MainUtil::setCookie('condition', $this->_condition, 10 * 60);
        }
        if (Env::getRequest('search')) {
            // 关键字
            $keyword = Env::getRequest('keyword');
            //添加对keyword的转义，防止SQL错误
            $keyword = \CHtml::encode($keyword);
            if (!empty($keyword)) {
                $this->_condition = " (`subject` LIKE '%$keyword%' ";
                $users = User::model()->fetchAll("`realname` LIKE '%$keyword%'");
                if (!empty($users)) { // 用户关键字
                    $uids = Convert::getSubByKey($users, 'uid');
                    $uidStr = implode(',', $uids);
                    $this->_condition .= " OR FIND_IN_SET(`designeeuid`, '{$uidStr}') OR FIND_IN_SET( `chargeuid`, '{$uidStr}' ) ";
                    foreach ($uids as $uid) {
                        $this->_condition .= " OR FIND_IN_SET({$uid}, `participantuid`)";
                    }
                }
                $this->_condition .= ')';
            }
            // 时间
            $daterange = Env::getRequest('daterange');
            if (!empty($daterange)) {
                $time = explode(' - ', $daterange);
                //添加判断$time是否是两个
                //如果不是默认设置starttime是3天前
                //endtime为当前时间
                if (2 != count($time)) {
                    $starttime = date('Y/m/d', time() - 3 * 24 * 60 * 60);
                    $endtime = date('Y/m/d');
                } else {
                    $starttime = $time[0];
                    $endtime = $time[1];
                }
                $st = strtotime($starttime);
                $et = strtotime($endtime);
                $this->_condition = AssignmentUtil::joinCondition($this->_condition, "`starttime` >= {$st} AND `endtime` <= {$et}");
            }
            MainUtil::setCookie('keyword', $keyword, 10 * 60);
            MainUtil::setCookie('daterange', $daterange, 10 * 60);
        } else {
            $this->_condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ($this->_condition != MainUtil::getCookie('condition')) {
            MainUtil::setCookie('condition', $this->_condition, 10 * 60);
        }
    }

}
