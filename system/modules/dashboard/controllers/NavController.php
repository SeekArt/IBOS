<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\Nav;
use application\modules\dashboard\model\Page;

class NavController extends BaseController
{

    public function actionIndex()
    {
        $formSubmit = Env::submitCheck('navSubmit');
        if ($formSubmit) {
            // 删除旧数据
            Nav::model()->deleteAll();
            $navs = $_POST['data'];
            foreach ($navs as $pnav) {
                $pnav['name'] = \CHtml::encode($pnav['name']);
                $pnav['pid'] = 0;
                $id = $this->runAdd($pnav);
                if ($id && isset($pnav['child']) && !empty($pnav['child'])) {
                    foreach ($pnav['child'] as $cnav) {
                        $cnav['name'] = \CHtml::encode($cnav['name']);
                        $cnav['pid'] = $id;
                        $this->runAdd($cnav);
                    }
                }
            }
            Cache::update('nav');
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $navs = Nav::model()->fetchAllByAllPid();
            $this->render('index', array('navs' => $navs));
        }
    }

    /**
     * 执行添加导航操作
     * @param array $nav 导航数据
     * @return integer
     */
    private function runAdd($nav)
    {
        if (!isset($nav['disabled'])) {
            $nav['disabled'] = 1;
        }
        if (!isset($nav['system'])) {
            $nav['system'] = 0;
        }
        if (!isset($nav['targetnew'])) {
            $nav['targetnew'] = 0;
        }
        if (!isset($nav['type']) || $nav['type'] == 0) {
            // 没有设置类型或者类型是超链接的，将pageid改为0
            $nav['pageid'] = 0;
        } else if ($nav['type'] == 1 && $nav['pageid'] == 0) {
            // 判断到新增的单页图文，则插入空的单页图文数据
            $nav['pageid'] = Page::model()->add(array('template' => 'index', 'content' => ''), true);
        }
        if (isset($nav['type']) && $nav['type'] == 1) {
            $nav['url'] = "main/page/index&pageid={$nav['pageid']}&name={$nav['name']}";
        }
        $navid = Nav::model()->add($nav, true);
        return $navid;
    }

}
