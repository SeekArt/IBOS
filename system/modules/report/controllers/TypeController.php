<?php

/**
 * 工作总结与计划模块------工作总结与计划汇报类型控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------工作总结与计划汇报类型控制器，继承ReportBaseController
 * @package application.modules.report.components
 * @version $Id: TypeController.php 1897 2013-12-12 12:33:07Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\report\core\ReportType as ICReportType;
use application\modules\report\model\Report;
use application\modules\report\model\ReportRecord;
use application\modules\report\model\ReportType;

class TypeController extends BaseController
{

    /**
     * 添加汇报类型
     */
    public function actionAdd()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeData = Env::getRequest('typeData');
            $type = ICReportType::handleSaveData($typeData);
            if (empty($type['sort'])) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Sort can not be empty')));
            }
            if (empty($type['typename'])) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Typename can not be empty')));
            }
            $typeid = ReportType::model()->add($type, true);
            if ($typeid) {
                $return = ReportType::model()->fetchByPk($typeid);
                if ($return['intervaltype'] == 5) {
                    $return['intervalTypeName'] = $return['intervals'] . Ibos::lang('Day');
                } else {
                    $return['intervalTypeName'] = ICReportType::handleShowInterval($typeData['intervaltype']);
                }
                $return['url'] = Ibos::app()->urlManager->createUrl('report/default/index', array('typeid' => $typeid));
                $return['isSuccess'] = true;
                $return['msg'] = Ibos::lang('Add succeed');
            } else {
                $return['isSuccess'] = false;
                $return['msg'] = Ibos::lang('Add failed');
            }
            $this->ajaxReturn($return);
        }
    }

    /**
     * 编辑汇报类型
     */
    public function actionEdit()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = intval(Env::getRequest('typeid'));
            $typeData = Env::getRequest('typeData');
            $type = ICReportType::handleSaveData($typeData);
            if (empty($typeid)) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Parameters error', 'error')));
            }
            ReportType::model()->modify($typeid, $type);
            $return = ReportType::model()->fetchByPk($typeid);
            if ($return['intervaltype'] == 5) {
                $return['intervalTypeName'] = $return['intervals'] . Ibos::lang('Day');
            } else {
                $return['intervalTypeName'] = ICReportType::handleShowInterval($typeData['intervaltype']);
            }
            $return['url'] = Ibos::app()->urlManager->createUrl('report/default/index', array('typeid' => $typeid));
            $return['isSuccess'] = true;
            $return['msg'] = Ibos::lang('Update succeed', 'message');
            $this->ajaxReturn($return);
        }
    }

    /**
     * 删除汇报类型
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = intval(Env::getRequest('typeid'));
            if (empty($typeid)) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Parameters error', 'error')));
            }
            $removeSuccess = ReportType::model()->remove($typeid);
            if ($removeSuccess) {
                // 删除此汇报类型的所有总结与计划,包括附件
                $reports = Report::model()->fetchRepidAndAidByTypeids($typeid);
                if (!empty($reports)) {
                    if ($reports['aids']) {
                        Attach::delAttach($reports['aids']);
                    }
                    ReportRecord::model()->deleteAll("repid IN('{$reports['repids']}')");
                    Report::model()->deleteAll("repid IN('{$reports['repids']}')");
                }
                $return['isSuccess'] = true;
                $return['msg'] = Ibos::lang('Del succeed', 'message');
            } else {
                $return['isSuccess'] = false;
                $return['msg'] = Ibos::lang('Del failed', 'message');
            }
            $this->ajaxReturn($return);
        }
    }

}
