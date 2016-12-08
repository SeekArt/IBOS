<?php

/**
 * main模块的单页图文控制器
 *
 * @version $Id: PageController.php 2019 2014-4-24 11:36:58Z gzhzh $
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Page;
use application\modules\main\utils\SinglePage;

Class PageController extends Controller
{

    private $_tplPath = 'data/page/'; //模板目录
    private $_suffix = '.php'; // 模板后缀

    /**
     * 单页图文视图
     */

    public function actionIndex()
    {
        $pageid = intval(Env::getRequest('pageid'));
        if (empty($pageid)) {
            $this->error(Ibos::lang('Parameters error'));
        }
        $name = Env::getRequest('name');
        $pageTitle = empty($name) ? Ibos::lang('Single page') : $name;
        $page = Page::model()->fetchByPk($pageid);
        if (empty($page)) {
            $this->error(Ibos::lang('Page not fount'));
        }
        $this->checkTplIsExist($page['template']);
        $params = array(
            'page' => $page,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main'),
            'pageTitle' => $pageTitle,
            'breadCrumbs' => $this->getBreadCrumbs($pageTitle)
        );
        $view = 'data.page.' . $page['template'];
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $replace = $jsLoaded . $page['content'];
        $ret = SinglePage::parse($html, $replace);
        if (!$ret) {
            $this->error(Ibos::lang('Template illegal'));
        }
        echo $ret;
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $pageid = intval(Env::getRequest('pageid'));
        if (empty($pageid)) {
            $this->error(Ibos::lang('Parameters error'));
        }
        if (Env::getRequest('op') == 'switchTpl') {
            // 切换模板
            $tpl = Env::getRequest('tpl');
            $file = File::fileName($this->_tplPath . $tpl . $this->_suffix);
            $content = SinglePage::getTplEditorContent($file); // 读取模板中id=page_content的div里面的html
            $page = array('id' => $pageid, 'template' => $tpl, 'content' => $content);
        } else {
            $page = Page::model()->fetchByPk($pageid);
        }
        if (empty($page)) {
            $this->error(Ibos::lang('Page not fount'));
        }
        $this->checkTplIsExist($page['template']);
        $name = Env::getRequest('name');
        $pageTitle = empty($name) ? Ibos::lang('Single page') : $name;
        $params = array(
            'page' => $page,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main'),
            'pageTitle' => $pageTitle,
            'breadCrumbs' => $this->getBreadCrumbs($pageTitle)
        );
        $view = 'data.page.' . $page['template'];
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $editor = $this->getEditorHtml($page);
        $replace = $jsLoaded . $editor;
        $ret = SinglePage::parse($html, $replace);
        if (!$ret) {
            $this->error(Ibos::lang('Template illegal'));
        }
        echo $ret;
    }

    /**
     * 保存
     */
    public function actionSave()
    {
        if (Env::submitCheck('saveSubmit')) {
            $pageid = intval(Env::getRequest('pageid'));
            $attributes = array(
                'template' => Env::getRequest('tpl'),
                'content' => Env::getRequest('content')
            );
            if (!empty($pageid)) {
                Page::model()->modify($pageid, $attributes);
            }
            $this->ajaxReturn(array('isSuccess' => true, 'pageid' => $pageid));
        }
    }

    /**
     * 预览
     */
    public function actionPreview()
    {
        $name = Env::getRequest('name');
        $tpl = Env::getRequest('tpl');
        $content = Env::getRequest('content');
        $pageTitle = empty($name) ? Ibos::lang('Single page') : $name;
        $params = array(
            'content' => $content,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main'),
            'pageTitle' => $pageTitle,
            'breadCrumbs' => $this->getBreadCrumbs($pageTitle)
        );
        $this->checkTplIsExist($tpl);
        $view = 'data.page.' . $tpl;
        $html = $this->renderPartial($view, $params, true);
        $jsLoaded = $this->getLoadedHtlm();
        $replace = $jsLoaded . $content;
        $ret = SinglePage::parse($html, $replace);
        if (!$ret) {
            $this->error(Ibos::lang('Template illegal'));
        }
        echo $ret;
    }

    /**
     * 获取编辑器代码
     * @param array $page 单页图文信息,为了渲染内容
     * @param string $curTpl 当前选中模板
     * @return string
     */
    private function getEditorHtml($page)
    {
        $tpls = SinglePage::getAllTpls();
        $params = array(
            'page' => $page,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main'),
            'tpls' => $tpls
        );
        $alias = 'application.modules.main.views.page.editor';
        $viewHtml = $this->renderPartial($alias, $params, true);
        return $viewHtml;
    }

    /**
     * 获取要加载的css和js文件html
     * @return string
     */
    private function getLoadedHtlm()
    {
        $alias = 'application.modules.main.views.page.loaded';
        $params = array(
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main')
        );
        $viewHtml = $this->renderPartial($alias, $params, true);
        return $viewHtml;
    }

    /**
     * 获取底部面包屑
     * @return array
     */
    private function getBreadCrumbs($pageTitle)
    {
        $breadCrumbs = array(
            array('name' => $pageTitle),
        );
        return $breadCrumbs;
    }

    /**
     * 检测模板是否存在，有可能会被删除
     * @param string $tpl 模板名，不包含后缀
     * @return boolean
     */
    protected function checkTplIsExist($tpl)
    {
        $file = File::fileName($this->_tplPath . $tpl . $this->_suffix);
        $ret = File::fileExists($file);
        if (!$ret) {
            $this->error(Ibos::lang('Template not fount', '', array('{file}' => $tpl . $this->_suffix)));
        }
    }

}
