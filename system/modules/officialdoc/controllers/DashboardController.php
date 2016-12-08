<?php

/**
 * 通知模块------ 后台控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 通知模块------  后台控制器类，继承DashboardBaseController
 * @package application.modules.officialDoc.controllers
 * @version $Id: DashboardController.php 639 2013-06-20 09:42:12Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;
use application\modules\officialdoc\model\RcType;

class DashboardController extends BaseController
{

    public function getAssetUrl($module = '')
    {
        $module = 'dashboard';
        return Ibos::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $docConfig = Ibos::app()->setting->get('setting/docconfig');
        //取出所有的套红数据
        $data = RcType::model()->fetchAll();
        $params = array(
            'commentSwitch' => $docConfig['doccommentenable'],
            'data' => $data
        );
        $this->render('index', $params);
    }

    /**
     * 保存提交
     */
    public function actionAdd()
    {
        $oldRcType = $newRcType = $delRcids = $oldRcids = array();
        foreach ($_POST as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                if (strpos($key, 'old_') === 0 || strpos($key, 'old_') !== false) {
                    list(, $rcid) = explode('_', $key);
                    $oldRcType[$rcid] = $value;
                    $oldRcids[] = $rcid;
                }
                if (strpos($key, 'new_') === 0 || strpos($key, 'new_') !== false) {
                    $newRcType[] = $value;
                }
            }
        }
        $rcTypes = RcType::model()->fetchAll(array('select' => array('rcid'), 'condition' => '', 'params' => array()));
        foreach ($rcTypes as $rcType) {
            if (!in_array($rcType['rcid'], $oldRcids)) {
                $delRcids[] = $rcType['rcid'];
            }
        }
        //后台配置
        $docConfig = array(
            'doccommentenable' => isset($_POST['commentSwitch']) ? 1 : 0
        );
        //修改配置信息
        Setting::model()->modify('docconfig', array('svalue' => serialize($docConfig)));
        //修改旧的套红
        foreach ($oldRcType as $key => $value) {
            RcType::model()->modify($key, array('name' => $value));
        }
        //增加新的套红
        foreach ($newRcType as $key => $value) {
            $rcType = array(
                'name' => $value
            );
            RcType::model()->add($rcType);
        }
        //删除旧的套红
        if (count($delRcids) > 0) {
            RcType::model()->deleteByPk($delRcids);
        }
        Cache::update('setting');
        $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('dashboard/index'));
    }

    /**
     * 套红编辑
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'update');
        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang('Can not find the path'), $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            $rcid = Env::getRequest('rcid');
            if (empty($rcid)) {
                $this->error(Ibos::lang('Request param', 'error'));
            }
            $data = RcType::model()->fetchByPk($rcid);
            $this->render('edit', array('data' => $data));
        } else {
            $this->$option();
        }
    }

    /**
     * 套红修改
     */
    private function update()
    {
        $rcid = $_POST['rcid'];
        $name = $_POST['name'];
        $content = $_POST['content_text'];
        $escapeContent = $_POST['content'];
        RcType::model()->modify($rcid, array('name' => $name, 'content' => $content, 'escape_content' => $escapeContent));
        $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('dashboard/index'));
    }

}
