<?php

/**
 * 信息中心后台控制器------ 后台控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 文章模块------ 后台控制器，继承DashboardBaseController
 * @package application.modules.comment.controllers
 * @version $Id: DashboardController.php 639 2013-06-20 09:42:12Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\controllers;

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;

class DashboardController extends BaseController
{

    public function getAssetUrl($module = '')
    {
        $module = 'dashboard';
        return Ibos::app()->assetManager->getAssetsUrl($module);
    }

    /**
     * 首页显示
     * @return void
     */
    public function actionIndex()
    {
        //取出所有的配置信息
        $result = array();
        $fields = array(
            'articlecommentenable',
            'articlevoteenable',
            'articlemessageenable',
            'articlethumbenable',
            'articlethumbwh'
        );
        foreach ($fields as $field) {
            $result[$field] = Ibos::app()->setting->get('setting/' . $field);
        }
        //缩略图设置
        $thumbOperate = $result['articlethumbwh'];
        list($result['articlethumbwidth'], $result['articlethumbheight']) = explode(',', $thumbOperate);

        $this->render('index', array('data' => $result));
    }

    /**
     * 更新数据
     */
    public function actionEdit()
    {
        $data = array();
        $fields = array(
            'articlecommentenable',
            'articlevoteenable',
            'articlemessageenable',
            'articlethumbenable',
            'articlethumbwidth',
            'articlethumbheight'
        );
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if (empty($_POST[$field])) {
                    $data[$field] = 0;
                } else {
                    $data[$field] = intval($_POST[$field]);
                }
            } else {
                $data[$field] = 0;
            }
        }
        $data['articlethumbwh'] = $data['articlethumbwidth'] . ',' . $data['articlethumbheight'];

        unset($data['articlethumbwidth']);
        unset($data['articlethumbhieght']);

        foreach ($data as $key => $value) {
            Setting::model()->updateAll(array('svalue' => $value), 'skey=:skey', array(':skey' => $key));
        }
        Cache::update('setting');
        $this->success(Ibos::lang('Update succeed'), $this->createUrl('dashboard/index'));
    }

}