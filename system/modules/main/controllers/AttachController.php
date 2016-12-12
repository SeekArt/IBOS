<?php

/**
 * main模块的附件控制器
 *
 * @version $Id$
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils as util;
use application\core\utils\File;
use application\modules\main\model\AttachmentN;
use application\modules\main\model\AttachmentUnused;

class AttachController extends Controller
{

    /**
     * 上传控制器
     * @return mixed
     */
    public function actionUpload()
    {
        //会议管理》会议申请》文件上传 的bug
        //检查$_FILES是否为空，检查$_FILES['Filedata']['error']是否非0
        if (empty($_FILES) || $_FILES['Filedata']['error'] != 0) {
            //TODO 这里不知道返回什么错误提示
            $echo = array('icon' => '', 'aid' => -1, 'name' => '上传失败', 'url' => '');
            $this->ajaxReturn(json_encode($echo), 'eval');
        }

        // 安全验证
        // 附件类型，可指定可不指定，不指定为普通类型
        $attachType = util\Env::getRequest('type');
        if (empty($attachType)) {
            $attachType = 'common';
        }
        $module = util\Env::getRequest('module');
        $object = '\application\modules\main\components\CommonAttach';
        if (class_exists($object)) {
            $attach = new $object('Filedata', $module);
            $return = $attach->upload();
            $this->ajaxReturn($return, 'eval');
        }
    }

    /*
     * 获取上传弹框视图
     */

    public function actionGetView()
    {
        $alias = 'application.views.upload';
        $views = $this->renderPartial($alias, array(), true);
        echo $views;
    }

    /**
     * 下载控制
     * @return type
     */
    public function actionDownload()
    {
        $data = $this->getData();
        $data['attach']['attachment'] = util\File::getAttachUrl() . '/' . $data['attach']['attachment'];
        if (!empty($data)) {
            return util\File::download($data['attach'], $data['decodeArr']);
        }
        $this->setPageTitle(util\Ibos::lang('Filelost'));
        $this->setPageState('breadCrumbs', array(
            array('name' => util\Ibos::lang('Filelost'))
        ));
        $this->render('filelost');
    }

    /**
     *
     */
    public function actionOffice()
    {
        if (util\Env::submitCheck('formhash')) {
            $widget = util\Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\main\widgets\Office', array());
            echo $widget->handleRequest();
        } else {
            $data = $this->getData();
            $data['decodeArr']['op'] = util\Env::getRequest('op');
            $widget = $this->createWidget('application\modules\main\widgets\Office', array('param' => $data['decodeArr'], 'attach' => $data['attach']));
            echo $widget->run();
        }
    }

    private function getData()
    {
        $id = util\Env::getRequest('id');
        $aidString = base64_decode(rawurldecode($id));
        if (empty($aidString)) {
            $this->error(util\Ibos::lang('Parameters error', 'error'), '', array('autoJump' => 0));
        }
        // 解码
        $salt = util\Ibos::app()->user->salt;
        $decodeString = util\StringUtil::authCode($aidString, 'DECODE', $salt);
        $decodeArr = explode('|', $decodeString);
        $count = count($decodeArr);
        if ($count < 3) {
            $this->error(util\Ibos::lang('Data type invalid', 'error'), '', array('autoJump' => 0));
        } else {
            $aid = $decodeArr[0];
            $tableId = $decodeArr[1];
            if ($tableId >= 0 && $tableId < 10) {
                $attach = AttachmentN::model()->fetch($tableId, $aid);
            }
            $return = array('decodeArr' => $decodeArr, 'attach' => array());
            if (!empty($attach)) {
                $return['attach'] = $attach;
            }
            return $return;
        }
    }

}
