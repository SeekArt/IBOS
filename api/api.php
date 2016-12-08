<?php

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module as ModuleUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\UserBinding;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../');
$defines = PATH_ROOT . '/system/defines.php';
require_once($defines);
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('YII_DEBUG') || define('YII_DEBUG', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($yii);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 接收信息处理
$result = trim(file_get_contents("php://input"), " \t\n\r");
$signature = Ibos::app()->getRequest()->getQuery('signature');
$timestamp = Ibos::app()->getRequest()->getQuery('timestamp');
$aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
if (strcmp($signature, md5($aeskey . $timestamp)) != 0) {
    Env::iExit("签名错误");
}
if (!empty($result)) {
    $msg = CJSON::decode($result, true);
    switch ($msg['op']) {
        case 'access':
            $return = 'success';
            break;
        case 'version':
            $return = strtolower(implode(',', array(ENGINE, VERSION, VERSION_DATE)));
            break;
        case 'module':
            $returnArray = Ibos::app()->db->createCommand()
                ->select('name,disabled,version,installdate')
                ->from(Module::model()->tableName())
                ->queryAll();
            $return = CJSON::encode($returnArray);
            break;
        case 'installModule':
            if (empty($msg['module'])) {
                $return = CJSON::encode(
                    array(
                        'isSuccess' => false,
                        'msg' => '缺少module参数',
                    )
                );
                break;
            }
            $notInstallModuleArray = ModuleUtil::getNotInstallModule();
            if (empty($notInstallModuleArray)) {
                $return = CJSON::encode(
                    array(
                        'isSuccess' => false,
                        'msg' => '全部模块已经安装',
                    )
                );
                break;
            }
            $moduleArray = is_array($msg['module']) ? $msg['module'] : explode(',', $msg['module']);
            $moduleToInstall = array_intersect($moduleArray, $notInstallModuleArray);
            foreach ($moduleToInstall as $module) {
                ModuleUtil::install($module);
            }
            Cache::update();
            $return = CJSON::encode(
                array(
                    'isSuccess' => true,
                    'msg' => '',
                )
            );
            break;
        case 'bindThird':
            $uid = $msg['uid'];
            $app = $msg['app'];
            $bindValue = $msg['bindValue'];
            $data = array(
                'uid' => $uid,
                'app' => $app,
                'bindvalue' => $bindValue,
            );
            $checkbinding = UserBinding::model()->find(sprintf(" `uid` = '%s' AND `app` = '%s'", $uid, $app));
            if (empty($checkbinding)) {
                $res = UserBinding::model()->add($data);
            } else {
                $res = UserBinding::model()->modify($checkbinding['id'], $data);
            }
            $return = CJSON::encode(
                array(
                    'isSuccess' => true,
                    'msg' => ''
                )
            );
            break;
        default:
            $return = '不予受理的请求类型';
    }
} else {
    $return = '请求数据不允许为空';
}
Env::iExit($return);

