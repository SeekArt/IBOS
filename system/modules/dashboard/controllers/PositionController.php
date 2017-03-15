<?php

/**
 * 组织架构模块岗位控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块岗位控制器类
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: PositionController.php 4553 2014-11-18 05:46:11Z zhangrong $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\position\model\Position;
use application\modules\position\model\PositionRelated;
use application\modules\position\model\PositionResponsibility;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\position\model\PositionCategory;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class PositionController extends OrganizationbaseController
{

    /**
     *
     * @var string 下拉列表中的<option>格式字符串
     */
    public $selectFormat = "<option value='\$catid' \$selected>\$spacer\$name</option>";

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex()
    {
        $category = StringUtil::getTree(PositionUtil::loadPositionCategory(), $this->selectFormat);
        $this->setPageTitle(Ibos::lang('Position manager'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Organization'), 'url' => $this->createUrl('department/index')),
            array('name' => Ibos::lang('Position manager'))
        ));
        $this->render('index', array('category' => $category), false, array('category'));
    }

    /**
     * 获取岗位列表数据方法
     * @return json
     */
    public function actionGetPositionList()
    {
        $catid = Env::getRequest('catid');
        $search = Env::getRequest('search');
        $draw = Env::getRequest('draw');
        $condition = '';
        if (!empty($search['value'])) {
            $key = \CHtml::encode($search['value']);
            $condition .= "`posname` LIKE '%{$key}%'";
        }
        if (!is_null($catid)) {
            $condition = !empty($condition) ? $condition . ' AND `catid` = ' . intval($catid) : '`catid` = ' . intval($catid);
        }
        $this->ajaxReturn(array(
            'data' => $this->handlePositionListDataByCondition($condition),
            'draw' => $draw,
            'recordsFiltered' => Position::model()->count($condition),
        ));
    }

    /**
     * 按处理岗位列表返回数据
     * @param  string $condition 获取岗位数据的条件查询语句
     * @return array             处理后的岗位列表数据
     */
    private function handlePositionListDataByCondition($condition)
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $categoryList = PositionUtil::loadPositionCategory();
        $positionList = Position::model()->fetchAll(array(
            'condition' => $condition,
            'order' => '`sort`',
            'limit' => $length,
            'offset' => $start,
        ));
        if (empty($positionList)) {
            return array();
        }
        foreach ($positionList as $position) {
            $result[] = array(
                'posid' => $position['positionid'],
                'posname' => $position['posname'],
                'catname' => isset($categoryList[$position['catid']]) ? $categoryList[$position['catid']]['name'] : '',
                'num' => Position::model()->getPositionUserNumById($position['positionid']),
            );
        }
        return $result;
    }

    /**
     * 新增操作
     * @return void
     */
    public function actionAdd()
    {
        if (Env::submitCheck('posSubmit')) {
            // 获取基本数据
            if (isset($_POST['posname'])) {
                $data["posname"] = \CHtml::encode($_POST['posname']);
                $data["sort"] = intval($_POST['sort']);
                $data["catid"] = intval(Env::getRequest('catid'));
                $data["goal"] = ''; // 岗位说明，已去掉
                $data["minrequirement"] = ''; // 最低要求，已去掉
            }
            // 获取插入ID，以便后续处理
            $newId = Position::model()->add($data, true);
            CacheUtil::update('position');
            //$newId为真才执行后面的判断
            $newId && Org::update();
            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('position/edit', array('op' => 'member', 'id' => $newId)));
        } else {
            // 分类ID （如果有）
            $catid = intval(Env::getRequest('catid'));
            // 岗位分类缓存
            $catData = PositionUtil::loadPositionCategory();
            $data['category'] = StringUtil::getTree($catData, $this->selectFormat, $catid);
            $this->render('add', $data);
        }
    }

    /**
     * 岗位编辑
     * @return void
     */
    public function actionEdit()
    {
        $id = Env::getRequest('id');
        if (Env::getRequest('op') == 'member') {
            $this->member();
        } else {
            if (Env::submitCheck('posSubmit')) {
                if (isset($_POST['posname'])) {
                    $data["posname"] = \CHtml::encode($_POST['posname']);
                    $data["sort"] = intval($_POST['sort']);
                    $data["catid"] = intval(Env::getRequest('catid'));
                    $data["goal"] = ''; // 岗位说明，已去掉
                    $data["minrequirement"] = ''; // 最低要求，已去掉
                    Position::model()->modify($id, $data);
                }

                // 新增成员
                if (isset($_POST['member'])) {
                    UserUtil::setPosition($id, $_POST['member']);
                }
                Org::update();
                $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('position/index'));
            } else {
                $pos = Position::model()->fetchByPk($id);
                $data['id'] = $id;
                $data['pos'] = $pos;
                // 岗位分类缓存
                $catData = PositionUtil::loadPositionCategory();
                $data['category'] = StringUtil::getTree($catData, $this->selectFormat, $pos['catid']);

                $this->render('edit', $data);
            }
        }
    }

    /**
     * 删除操作
     * @return void
     */
    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $id = Env::getRequest('id');
            $ids = explode(',', trim($id, ','));
            foreach ($ids as $positionId) {
                // 删除岗位
                Position::model()->deleteByPk($positionId);
                // 删除岗位对应授权
                Ibos::app()->authManager->removeAuthItem($positionId);
                // 删除岗位职责
                PositionResponsibility::model()->deleteAll('`positionid` = :positionid', array(':positionid' => $positionId));
                // 删除辅助岗位关联
                PositionRelated::model()->deleteAll('positionid = :positionid', array(':positionid' => $positionId));
                $relatedIds = User::model()->fetchUidByPosId($positionId, true, true);
                $uidArray = array();
                foreach ($relatedIds as $id) {
                    $uidArray = array_merge($uidArray, $id);
                }
                // 更新用户岗位信息
                if (!empty($uidArray)) {
                    User::model()->updateByUids($uidArray, array('positionid' => 0));
                }
            }
            CacheUtil::update('position');
            // 更新组织架构
            Org::update();
            $this->ajaxReturn(array('isSuccess' => true), 'json');
        }
    }

    /**
     * 成员
     */
    public function member()
    {
        $id = Env::getRequest('id');
        if (!empty($id)) {
            if (Env::submitCheck('postsubmit')) {
                $member = Env::getRequest('member');
                UserUtil::setPosition($id, $member);
                $this->success(Ibos::lang('Save succeed', 'message'));
            } else {
                // 该岗位下人员
                $uids = User::model()->fetchAllUidByPositionIds($id, false, true);
                // 搜索处理
                if (Env::submitCheck('search')) {
                    $key = $_POST['keyword'];
                    $uidStr = implode(',', $uids);
                    $users = User::model()->fetchAll("`realname` LIKE '%{$key}%' AND FIND_IN_SET(`uid`, '{$uidStr}')");
                    $pageUids = Convert::getSubByKey($users, 'uid');
                } else {
                    $count = count($uids);
                    $pages = Page::create($count, self::MEMBER_LIMIT);
                    $offset = $pages->getOffset();
                    $limit = $pages->getLimit();
                    $pageUids = array_slice($uids, $offset, $limit);
                    $data['pages'] = $pages;
                }
                $data['id'] = $id;
                // for input
                $data['uids'] = $uids;
                // for js
                $data['uidString'] = '';
                foreach ($uids as $uid) {
                    $data['uidString'] .= "'u_" . $uid . "',";
                }
                $data['uidString'] = trim($data['uidString'], ',');
                // 当前页要显示的uid（只作显示，并不为实际表单提交数据）
                $data['pageUids'] = $pageUids;
                $this->render('member', $data);
            }
        } else {
            $this->error('该岗位不存在或已删除！');
        }
    }

    /**
     * 批量修改用户岗位 ajax 接口
     * @return ajax
     */
    public function actionBatchAlterUserPos()
    {
        $position = explode('_', Env::getRequest('id'));
        $member = Env::getRequest('member');
        $uids = StringUtil::getUidAByUDPX($member);
        $batchSetRes = UserUtil::batchSetUserPosition($position[1], $uids);
        if ($batchSetRes) {
            Org::update();
            $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Save succeed', 'message'),
            ));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Save failed', 'message'),
            ));
        }
    }

}
