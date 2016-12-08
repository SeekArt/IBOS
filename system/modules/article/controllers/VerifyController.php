<?php
namespace application\modules\article\controllers;

use application\core\utils\Ibos;

class VerifyController extends BaseController
{

    public function actionIndex()
    {
        $this->setPageTitle(Ibos::lang('My Approval'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Article'), 'url' => $this->createUrl('verify/index')),
            array('name' => Ibos::lang('Verify Article'))
        ));
        $this->render('index');
    }

    public function actions()
    {
        $actions = array(
            'verify' => 'application\modules\article\actions\verify\Verify',
            'back' => 'application\modules\article\actions\verify\Back',
            'flowlog' => 'application\modules\article\actions\verify\FlowLog',
            'cancel' => 'application\modules\article\actions\verify\Cancel',
            'test' => 'application\modules\article\actions\verify\Test',
        );
        return $actions;
    }

}