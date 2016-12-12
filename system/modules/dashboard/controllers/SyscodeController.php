<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Syscode;

class SyscodeController extends BaseController
{

    public function actionIndex()
    {
        $formSubmit = Env::submitCheck('sysCodeSubmit');
        if ($formSubmit) {
            $codes = $_POST['codes'];
            $newCodes = isset($_POST['newcodes']) ? $_POST['newcodes'] : array();
            // 更新操作
            foreach ($codes as $id => $code) {
                $code['name'] = \CHtml::encode($code['name']);
                Syscode::model()->modify($id, $code);
            }
            // 新增操作
            foreach ($newCodes as $newCode) {
                $newCode['name'] = \CHtml::encode($newCode['name']);
                Syscode::model()->add($newCode);
            }
            // 删除操作
            $removeId = $_POST['removeId'];
            if (!is_null($removeId)) {
                Syscode::model()->deleteById($removeId);
            }
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $record = Syscode::model()->fetchAllByAllPid();
            $data = array('data' => $record);
            $this->render('index', $data);
        }
    }

}
