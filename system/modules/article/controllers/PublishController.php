<?php
namespace application\modules\article\controllers;

use application\core\utils\Ibos;

class PublishController extends BaseController
{
    //我的投稿首页->草稿箱
    public function actionIndex()
    {
        $this->setPageTitle(Ibos::lang('My Publish'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => $this->createUrl('publish/index')),
            array('name' => Ibos::lang('Article list'))
        ));
        $this->render('index');
    }

    //添加新闻
    public function actionAdd()
    {
        $this->setPageTitle(Ibos::lang('Add Article'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => $this->createUrl('publish/add')),
            array('name' => Ibos::lang('Add Article'))
        ));
        $this->render('add');
    }

    public function actions()
    {
        $actions = array(
            'call' => 'application\modules\article\actions\publish\Call',
            'cancel' => 'application\modules\article\actions\publish\Cancel',
        );
        return $actions;
    }
}