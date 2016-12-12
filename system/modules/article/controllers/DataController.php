<?php
namespace application\modules\article\controllers;

/*
 * 数据返回控制器，数据以json数据返回
 */
class DataController extends BaseController
{

    public function actions()
    {
        $actions = array(
            'index' => 'application\modules\article\actions\data\Index',
            'call' => 'application\modules\article\actions\data\Call',
            'preview' => 'application\modules\article\actions\data\Preview',
            'show' => 'application\modules\article\actions\data\Show',
            'edit' => 'application\modules\article\actions\data\Edit',
            'option' => 'application\modules\article\actions\data\Option',
        );
        return $actions;
    }
}