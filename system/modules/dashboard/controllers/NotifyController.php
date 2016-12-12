<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\message\model\Notify;

class NotifyController extends BaseController
{

    public function actionSetup()
    {
        $formSubmit = Env::submitCheck('formhash');
        if ($formSubmit) {
            $data = &$_POST;
            foreach (array('sendemail', 'sendsms', 'sendmessage') as $field) {
                if (!empty($data[$field])) {
                    $ids = array_keys($data[$field]);
                    $idstr = implode(',', $ids);
                    Notify::model()->updateAll(array($field => 1), sprintf("FIND_IN_SET(id,'%s')", $idstr));
                    Notify::model()->updateAll(array($field => 0), sprintf("NOT FIND_IN_SET(id,'%s')", $idstr));
                } else {
                    Notify::model()->updateAll(array($field => 0));
                }
            }
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $nodeList = Notify::model()->getNodeList();
            foreach ($nodeList as &$node) {
                $node['moduleName'] = Module::model()->fetchNameByModule($node['module']);
            }
            $this->render('setup', array('nodeList' => $nodeList));
        }
    }

}
