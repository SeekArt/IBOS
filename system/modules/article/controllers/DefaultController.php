<?php
namespace application\modules\article\controllers;

use application\core\utils\Ibos;
use application\modules\article\model\Article;

class DefaultController extends BaseController
{
    //首页
    public function actionIndex()
    {
        Article::model()->cancelTop();
        Article::model()->updateIsOverHighLight();
        Ibos::app()->controller->setPageTitle(Ibos::lang('Information center'));
        Ibos::app()->controller->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => Ibos::app()->controller->createUrl('default/index')),
            array('name' => Ibos::lang('Article list'))
        ));
        return Ibos::app()->controller->render('index');
    }

    //预览
    public function actionPreview()
    {
        Ibos::app()->controller->setPageTitle(Ibos::lang('Preview Acticle'));
        Ibos::app()->controller->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => Ibos::app()->controller->createUrl('default/preview')),
            array('name' => Ibos::lang('Preview Acticle'))
        ));
        $this->render('preview');
    }

    //查看
    public function actionShow()
    {
        Ibos::app()->controller->setPageTitle(Ibos::lang('Show Article'));
        Ibos::app()->controller->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => Ibos::app()->controller->createUrl('default/show')),
            array('name' => Ibos::lang('Show Article'))
        ));
        $param = $this->getDashboardConfig();
        $this->render('show', array('config' => $param));
    }

    //添加页面
    public function actionAdd()
    {
        Ibos::app()->controller->setPageTitle(Ibos::lang('Add Article'));
        Ibos::app()->controller->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => Ibos::app()->controller->createUrl('default/add')),
            array('name' => Ibos::lang('Add Article'))
        ));
        $param = $this->getDashboardConfig();
        $this->render('form', array('config' => $param));
    }

    //编辑页面
    public function actionEdit()
    {
        Ibos::app()->controller->setPageTitle(Ibos::lang('Edit Article'));
        Ibos::app()->controller->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => Ibos::app()->controller->createUrl('default/edit')),
            array('name' => Ibos::lang('Edit Article'))
        ));
        $param = $this->getDashboardConfig();
        $this->render('form', array('config' => $param));
    }

    public function actions()
    {
        $actions = array(
            'base' => 'application\modules\article\actions\index\Base',
            'top' => 'application\modules\article\actions\index\Top',
            'highlight' => 'application\modules\article\actions\index\HighLight',
            'getmove' => 'application\modules\article\actions\index\GetMove',
            'move' => 'application\modules\article\actions\index\Move',
            'getreader' => 'application\modules\article\actions\index\GetReader',
            'delete' => 'application\modules\article\actions\index\Delete',
            'getcount' => 'application\modules\article\actions\index\GetCount',
            'read' => 'application\modules\article\actions\index\Read',
            'submit' => 'application\modules\article\actions\index\Submit',
            'vote' => 'application\modules\article\actions\index\Vote',
        );
        return $actions;
    }
}