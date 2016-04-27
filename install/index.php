<?php

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Module;
use application\core\utils\String;
use application\modules\dashboard\utils\Wx;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;
use application\modules\user\model\User;

/**
 * 安装
 */
session_start();

error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('PRC');
@set_time_limit(1000);
ini_set('memory_limit', '100M');
@set_magic_quotes_runtime(0);
define('PATH_ROOT', dirname(__FILE__) . '/../');  //ibos根目录
require PATH_ROOT . './system/version.php';
require './include/installLang.php';
require './include/installVar.php';
require './include/installFunction.php';

if (isset($_GET['p']) && $_GET['p'] == 'phpinfo') {
    phpinfo();
    exit();
}

$allowOptions = array('envCheck', 'dbInit', 'moduleCustom',
    'installing', 'installResult', 'tablepreCheck', 'updateCache',
    'coLogin', 'coRegister', 'coRegisterAccount', 'coAfreshCode', 'coVerifyCode', 'coVerifyCorpCode');

$option = $_GET['op'];

if (empty($option) || !in_array($option, $allowOptions)) {
    $option = 'envCheck';
}

if (in_array($option, array('coLogin', 'coRegister', 'coRegisterAccount', 'coAfreshCode', 'coVerifyCode', 'coVerifyCorpCode'))) {
    $yii = PATH_ROOT . 'library/yii.php';
    require_once ( $yii );
    Yii::setPathOfAlias('application', PATH_ROOT . 'system');
    $option();
}

//酷办公登录
function coLogin() {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $tokenRes = CoApi::getInstance()->getCoToken($mobile, $password);
    if ($tokenRes['code'] == CodeApi::SUCCESS) {
        $param = afterLogin($tokenRes);
    } else {
        $param = array(
            'isSuccess' => false,
            'msg' => $tokenRes['message'],
        );
    }
    exit(json_encode($param));
}

/**
 * 登录之后的处理数据
 * @param array $tokenRes 获取的用户的信息
 * @return array 拼出给前端的数据
 */
function afterLogin($tokenRes) {
    $isSuccess = true;
    $userInfo = CoApi::getInstance()->getUserInfo($tokenRes['data']['accesstoken']);
    if ($userInfo['code'] == CodeApi::SUCCESS) {
        $param = array(
            'isSuccess' => $isSuccess,
            'status' => true,
            'isadmin' => !strcmp($userInfo['data']['role'], 'admin') ? true : false,
            'mobile' => $userInfo['data']['mobile'],
            'corpcode' => $userInfo['data']['corpcode'],
            'shortname' => $userInfo['data']['corpshortname'],
            'realname' => $userInfo['data']['realname'], //把这个放出来因为前端无法反序列化
            'extraData' => serialize(array(
                'username' => $userInfo['data']['username'],
                'email' => $userInfo['data']['email'],
                'accesstoken' => $tokenRes['data']['accesstoken'],
                'fullname' => $userInfo['data']['corpname'],
                'corptoken' => isset($tokenRes['data']['corptoken']) ? $tokenRes['data']['corptoken'] : '',
                'guid' => $tokenRes['data']['guid'],
                'realname' => $userInfo['data']['realname'], //这里也写上是因为前端用realname只是用来显示，如果要提交还需要再加一个input:hidden，太多input:hidden不好
            ))
        );
    } else {
        $param = array(
            'isSuccess' => $isSuccess,
            'status' => false,
            'msg' => $userInfo['message'],
        );
    }
    return $param;
}

/**
 * 酷办公注册用户
 * 先检测手机是否已经注册，再发验证码
 */
function coRegisterAccount() {
    $mobile = $_POST['mobile'];
    checkMobile($mobile);
    coCode($mobile);
}

/**
 * 重新发送验证码
 */
function coAfreshCode() {
    $mobile = $_POST['mobile'];
    coCode($mobile);
}

/**
 * 检测手机是否注册
 * @param type $mobile
 */
function checkMobile($mobile) {
    $res = CoApi::getInstance()->checkMobile($mobile);
    if ($res['code'] == CodeApi::SUCCESS) {
        if ($res['data']['isexist'] == 1) {
            exit(json_encode(array(
                'isSuccess' => false,
                'msg' => '手机已经注册'
            )));
        }
    } else {
        exit(json_encode(array(
            'isSuccess' => false,
            'msg' => $res['message']
        )));
    }
}

/**
 * 发送验证码
 * @param type $mobile
 */
function coCode($mobile) {
    $post = array(
        'mobile' => $mobile,
    );
    $res = CoApi::getInstance()->getVerifyCode($post);
    $res['code'] = CodeApi::SUCCESS;
    if ($res['code'] == CodeApi::SUCCESS) {
        $param = array(
            'isSuccess' => true,
            'status' => $res['data']['issend'],
        );
    } else {
        $param = array(
            'isSuccess' => false,
            'msg' => $res['message']
        );
    }
    exit(json_encode($param));
}

/**
 * 验证验证码
 */
function coVerifyCode() {
    $code = $_POST['code'];
    $mobile = $_POST['mobile'];
    $post = array(
        'mobile' => $mobile,
        'code' => $code,
    );
    $res = CoApi::getInstance()->checkVerifyCode($post);
    $res['code'] = CodeApi::SUCCESS;
    if ($res['code'] == CodeApi::SUCCESS) {
        $param = array(
            'isSuccess' => true,
            'status' => $res['data']['checked'],
        );
    } else {
        $param = array(
            'isSuccess' => false,
            'msg' => $res['message'],
        );
    }
    exit(json_encode($param));
}

/**
 * 注册酷办公用户
 */
function coRegister() {
    $param = array(
        'mobile' => $_POST['mobile'],
        'password' => $_POST['password'],
    );
    $res = CoApi::getInstance()->registerUser($param);
    if ($res['code'] == CodeApi::SUCCESS) {
        $param = afterLogin($res);
    } else {
        $param = array(
            'isSuccess' => false,
            'msg' => $res['message']
        );
    }
    exit(json_encode($param));
}

/**
 * 检测企业代码是否重复
 */
function coVerifyCorpCode() {
    $code = $_POST['code'];
    $res = CoApi::getInstance()->searchCorp($code, true);
    if ($res['code'] == CodeApi::SUCCESS) {
        $isAvailable = empty($res['data']['corps']) ? true : false;
        $param = array(
            'isSuccess' => true,
            'available' => $isAvailable,
        );
    } else {
        $param = array(
            'isSuccess' => false,
            'msg' => $res['message'],
        );
    }
    exit(json_encode($param));
}

if (isset($_GET['init']) && $_GET['init']) {

    // 定义驱动引擎
    define('ENGINE', 'LOCAL');
    define('YII_DEBUG', TRUE);
    $yii = PATH_ROOT . '/library/yii.php';
    $ibosConfig = require( PATH_ROOT . '/system/config/config.php' );
    require_once ( $yii );
    $config = array(
        'basePath' => PATH_ROOT . 'system',
        'components' => array(
            'db' => array(
                'connectionString' => "mysql:host={$ibosConfig['db']['host']};port={$ibosConfig['db']['port']};dbname={$ibosConfig['db']['dbname']}",
                'emulatePrepare' => true,
                'username' => $ibosConfig['db']['username'],
                'password' => $ibosConfig['db']['password'],
                'charset' => $ibosConfig['db']['charset'],
                'tablePrefix' => $ibosConfig['db']['tableprefix'],
            )
        ),
    );
    Yii::createWebApplication($config);
}

// 是否已安装过
if (file_exists($lockfile) && $option != 'extData') {
    $errorMsg = $lang['Install locked'] . str_replace(PATH_ROOT, '', $lockfile);
    include 'errorInfo.php';
    exit();
}

/**
 * 环境检查
 * @param array $lang 语言变量
 * @param array $envItems 要检测的环境
 * @param array $funcItems 要检测的函数
 * @param array $filesockItems 要检测的函数
 * @param array $dirfileItems 要检测的文件、文件夹权限
 * @param array $extLoadedItems 要检测的扩展
 */
function envCheckAll($lang, $envItems, $funcItems, $filesockItems, $dirfileItems, $extLoadedItems) {
    $envCheck = envCheck($envItems);
    $funcCheck = funcCheck($funcItems);
    $filesorkCheck = filesorkCheck($filesockItems);
    $dirfileCheck = dirfileCheck($dirfileItems);
    $extLoadedCheck = extLoadedCheck($extLoadedItems);
    if (!$envCheck['envCheckRes'] || !$funcCheck['funcCheckRes'] || !$filesorkCheck['filesorkCheckRes'] || !$dirfileCheck['dirfileCheckRes'] || !$extLoadedCheck['extLoadedCheckRes']) {
        include 'envCheck.php';
        exit();
    } else {
        //通过环境检查，继续执行
    }
}

if ($option == 'envCheck' or $option == 'dbInit') { // 检测环境
    envCheckAll($lang, $envItems, $funcItems, $filesockItems, $dirfileItems, $extLoadedItems);
    // 创建数据库数据
    if (isset($_SESSION['extData'])) {
        unset($_SESSION['extData']);
    }
    $configFile = CONFIG_PATH . 'config.php';
    $defaultConfigfile = CONFIG_PATH . 'configDefault.php';
    if (!file_exists($defaultConfigfile)) { // 检测configDefault.php文件是否存在
        exit('configDefault.php was lost, please reupload this file.');
    }
    if (isset($_POST['submitDbInit'])) {
        if (isset($_POST['extData'])) {
            $_SESSION['extData'] = md5('extData');
        }
        $yii = PATH_ROOT . 'library/yii.php';
        require_once ( $yii );
        Yii::setPathOfAlias('application', PATH_ROOT . 'system');
        $dbHost = $_POST['dbHost'];
        $dbAccount = $_POST['dbAccount'];
        $dbPassword = $_POST['dbPassword'];
        $dbName = $_POST['dbName'];
        $adminAccount = $_POST['adminAccount'];
        $adminPassword = $_POST['adminPassword'];
        //酷办公相关
        $extraData = String::utf8Unserialize($_POST['extraData'] ? $_POST['extraData'] : array() );
        $adminUsername = empty($extraData['username']) ? 'admin' : $extraData['username'];
        $adminEmail = $extraData['email'];
        $adminRealname = empty($extraData['realname']) ? 'admin' : $extraData['realname'];
        $adminAccesstoken = $extraData['accesstoken'];
        $corpToken = $extraData['corptoken'];
        $guid = $extraData['guid'];
        $corpFullname = $_POST['fullname'];
        $corpShortname = $_POST['shortname'];
        $corpCode = $_POST['qycode'];
        //企业代码为表前缀
        $dbPre = $corpCode . '_';
        //设置aeskey并注册酷办公
        $host = getHostInfo();
        $aeskey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $host . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 14, 10) . random(33);

        $postHost = explode(':', $dbHost);
        list($host, $port) = $postHost;
        $port = $port ? $port : 3306;

        // 检查表单各项
        if (empty($dbAccount)) { // 数据库用户名
            $errorMsg = $lang['Dbaccount not empty'];
            include 'errorInfo.php';
            exit();
        }
        if (empty($dbPassword)) { // 数据库密码
            $errorMsg = $lang['Dbpassword not empty'];
            include 'errorInfo.php';
            exit();
        }
        if (empty($adminAccount)) { // 管理员账号
            $errorMsg = $lang['Adminaccount not empty'];
            include 'errorInfo.php';
            exit();
        }
        if (!preg_match("/^[a-zA-Z0-9]{5,32}$/", $adminPassword)) { // 管理员密码
            $errorMsg = $lang['Adminpassword incorrect format'];
            include 'errorInfo.php';
            exit();
        }
        if ( !preg_match( "/^[a-zA-Z0-9]{4,20}/", $corpCode ) ) { // 企业代码
            $errorMsg = $lang['Invalid corp code'];
            include 'errorInfo.php';
            exit();
        }
        // 检查数据库连接正确性
        $link = @mysql_connect($dbHost, $dbAccount, $dbPassword);
        if (!$link) {
            $errno = mysql_errno();
            $error = mysql_error();
            if ($errno == 1045) {
                $errnoMsg = $lang['Database errno 1045'];
            } elseif ($errno == 2003) {
                $errnoMsg = $lang['Database errno 2003'];
            } else {
                $errnoMsg = $lang['Database connect error'];
            }
            $errorMsg = $errnoMsg . $lang['Database error info'] . $error;
            include 'errorInfo.php';
            exit();
        }
        // 判断数据库能否创建
        if (mysql_get_server_info() > '4.1') {
            mysql_query("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET " . DBCHARSET, $link);
        } else {
            mysql_query("CREATE DATABASE IF NOT EXISTS `$dbName`", $link);
        }
        @mysql_select_db($dbName, $link);
        $moduleSql = str_replace('{dbpre}', $dbPre, $moduleSql);
        mysql_query($moduleSql);  // 提前创建module表，否则后续步骤不能初始化ibos
        if (mysql_errno()) {
            $errorMsg = $lang['Database errno ' . mysql_errno()];
            include 'errorInfo.php';
            exit();
        }
        mysql_close($link);

        // 获得用户输入的数据库配置数据，替换掉configDefault文件里的配置，用以生成config文件
        $configDefault = file_get_contents($defaultConfigfile);
        $authkey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $host . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 8, 6) . random(10);
        $cookiepre = random(4);
        $configReplace = array(//主配置文件要替换的参数
            '{installed}' => 1,
            '{host}' => trim($host),
            '{port}' => trim($port),
            '{dbname}' => trim($dbName),
            '{username}' => trim($dbAccount),
            '{password}' => trim($dbPassword),
            '{tableprefix}' => trim($dbPre),
            '{charset}' => DBCHARSET,
            '{authkey}' => $authkey,
            '{cookiepre}' => $cookiepre
        );
        // 创建config文件
        $config = str_replace(array_keys($configReplace), array_values($configReplace), $configDefault);
        file_put_contents(CONFIG_PATH . 'config.php', $config);
        // 创建管理员账号信息文件,安装完成后删除此文件
        $salt = random(6);
        $adminReplace = array(// 管理员账号替换信息
            '{username}' => $adminUsername,
            '{isadministrator}' => 1,
            '{password}' => md5(md5($adminPassword) . $salt),
            '{createtime}' => time(),
            '{salt}' => $salt,
            '{realname}' => $adminRealname,
            '{mobile}' => $adminAccount,
            '{email}' => $adminEmail,
            '{accesstoken}' => $adminAccesstoken,
            '{corptoken}' => $corpToken,
            '{guid}' => $guid,
            '{corpcode}' => $corpCode,
            '{fullname}' => $corpFullname,
            '{shortname}' => $corpShortname,
            '{aeskey}' => $aeskey,
        );
        $administrator = str_replace(array_keys($adminReplace), array_values($adminReplace), $adminInfo);
        file_put_contents(CONFIG_PATH . 'admin.php', $administrator);
        if (isset($_POST['custom'])) { // 如果自定义模块
            header("Location: index.php?op=moduleCustom&init=1");
        } else { // 不是自定义模块，直接开始安装所有模块
            header("Location: index.php?op=installing&init=1");
        }
    } else { // 渲染视图,数据库设置默认值
        if (file_exists($configFile)) {
            $configData = include($configFile);
            $dbInitData = $configData['db'];
            $dbInitData['adminAccount'] = '';
            $dbInitData['adminPassword'] = '';
        } else {
            $dbInitData = array(
                'username' => 'root', // 数据库用户名
                'password' => 'root', // 数据库密码
                'host' => '127.0.0.1', // 数据库服务器
                'port' => '', // 端口
                'dbname' => 'ibos', // 数据库名
                'tableprefix' => 'ibos_', // 数据表前缀
                'adminAccount' => '', // 管理员账号
                'adminPassword' => ''  // 管理员密码
            );
        }
        include 'dbInit.php';
    }
} elseif ($option == 'moduleCustom') { // 自定义模块
    $allModules = getModuleDirs();
    $coreModulesParams = initModuleParameters($sysModules);
    $customModules = array_diff($allModules, $sysModules);
    $customModulesParams = initModuleParameters($customModules);
    include 'moduleCustom.php';
} elseif ($option == 'installing') { // 开始安装模块与数据库，注：模块安装有顺序要求，不然插入数据可能会报错
    if (isset($_GET['installBegin']) && $_GET['installBegin'] == 1) { // 异步开始安装
        $installModules = $_POST['installModules']; // 要安装的模块
        $installModules = CJSON::decode($installModules);
        $installingModule = $_POST['installingModule'];
        if (empty($installingModule)) {
            $installingModule = $installModules[0];
        }
        $moduleNums = count($installModules);
        $isSuccess = install($installingModule); // 执行安装模块
        if ($isSuccess) {
            foreach ($installModules as $k => $module) {
                if ($module == $installingModule) {
                    $index = $k + 1;
                    if ($index < count($installModules)) {
                        $nextModule = $installModules[$index]; // 下一个要安装的模块
                        $nextModuleName = getModuleName($nextModule); // 下一个要安装的模块名
                        $process = number_format(($index / $moduleNums) * 100, 1) . '%'; // 完成度
                        echo json_encode(array('complete' => 0, 'isSuccess' => 1, 'process' => $process, 'nextModule' => $nextModule, 'nextModuleName' => $nextModuleName));
                    } else {
                        echo json_encode(array('complete' => 1, 'process' => '100%'));
                    }
                    exit();
                }
            }
        } else {
            echo json_encode(array('complete' => 0, 'isSuccess' => 0, 'msg' => $installingModule . $lang['Install module failed']));
            exit();
        }
    }
    if (isset($_POST['submitInstallModule'])) { // 自定义安装
        $customModules = $_POST['customModules'];
    } else { // 非自定义安装
        $allModules = getModuleDirs();
        $customModules = array_diff($allModules, $sysModules);
    }
    // 组合模块安装顺序，系统模块置前
    $installModules = !empty($customModules) ? array_merge($sysModules, $customModules) : $sysModules;
    include 'installing.php';
} elseif ($option == 'installResult') {
    $res = $_GET['res'];
    if ($res == 1) {
        $adminfile = CONFIG_PATH . 'admin.php';
        $orgJs = PATH_ROOT . './data/org.js';
        require $adminfile; // 引入刚才写入的管理员信息文件
        Yii::app()->db->createCommand()
                ->insert('{{user}}', $admin);
        $newId = Yii::app()->db->createCommand()
                ->select("last_insert_id()")
                ->from("{{user}}")
                ->queryScalar();
        $uid = intval($newId);
        Yii::app()->db->createCommand()
                ->insert('{{user_count}}', array('uid' => $uid));
        $ip = Yii::app()->request->userHostAddress;
        Yii::app()->db->createCommand()
                ->insert('{{user_status}}', array('uid' => $uid, 'regip' => $ip, 'lastip' => $ip));
        Yii::app()->db->createCommand()
                ->insert('{{user_profile}}', array('uid' => $uid, 'remindsetting' => '', 'bio' => ''));
        //酷办公信息
        //aeskey存入表里
        Yii::app()->db->createCommand()
                ->update('{{setting}}', array('svalue' => $adminco['aeskey']), " `skey`= 'aeskey'");
        //更新Setting的unit
        $systemurl = substr(Env::getSiteUrl(), 0, -9);
        $coip = Env::getClientIp();
        //更新本地的Setting.unit
        $unit = String::utf8Unserialize(
                        Yii::app()->db->createCommand()
                                ->select('svalue')
                                ->from('{{setting}}')
                                ->where("`skey` = 'unit'")
                                ->queryRow()
        );
        $unitConfig = array(
            'logourl', 'phone', 'fullname',
            'shortname', 'fax', 'zipcode',
            'address', 'adminemail', 'systemurl', 'corpcode'
        );
        $unit['fullname'] = $adminco['fullname'];
        $unit['shortname'] = $adminco['shortname'];
        $unit['corpcode'] = $adminco['corpcode'];
        $unit['systemurl'] = $systemurl;
        foreach ($unitConfig as $value) {
            if (!isset($unit[$value])) {
                $unit[$value] = '';
            }
        }
        Yii::app()->db->createCommand()
                ->update('{{setting}}', array('svalue' => serialize($unit)), " `skey`= 'unit'");

        //只有登录才会进行下面的，登录了accesstoken才不会是空的
        if (!empty($adminco['accesstoken'])) {
            //更新本地的coinfo和是否创建企业
            $coinfo = String::utf8Unserialize(
                            Yii::app()->db->createCommand()
                                    ->select('svalue')
                                    ->from('{{setting}}')
                                    ->where("`skey` = 'coinfo'")
                                    ->queryRow()
            );
            if (empty($adminco['corptoken'])) {//没有加入企业，则创建企业
                $post = array(
                    'name' => $adminco['shortname'],
                    'shortname' => $adminco['shortname'],
                    'code' => $adminco['corpcode'],
                    'aeskey' => $adminco['aeskey'],
                    'regip' => $coip,
                    'systemurl' => $systemurl,
                );
                $res = CoApi::getInstance()->createCorpByToken($adminco['accesstoken'], $post);
                if ($res['code'] == CodeApi::SUCCESS) {
                    $adminco['corptoken'] = $res['data']['corptoken'];
                    Yii::app()->db->createCommand()
                            ->update('{{setting}}', array('svalue' => 1), " `skey`= 'cobinding'");
                }
            } else {//如果已经加入企业，更新酷办公企业的信息，成功则更新本地coinfo
                $post = array(
                    'name' => $adminco['shortname'],
                    'shortname' => $adminco['shortname'],
                    'aeskey' => $adminco['aeskey'],
                    'regip' => $coip,
                    'systemurl' => $systemurl,
                );
                $res = CoApi::getInstance()->updateCorpByCorpToken($adminco['corptoken'], $post);
            }
            if ($res['code'] == CodeApi::SUCCESS) {
                $coinfo['accesstoken'] = $adminco['accesstoken'];
                $coinfo['corptoken'] = $adminco['corptoken'];
                $coinfo['guid'] = $adminco['guid'];
                $coinfo['mobile'] = $admin['mobile'];
            }
            Yii::app()->db->createCommand()
                    ->update('{{setting}}', array('svalue' => serialize($coinfo)), " `skey`= 'coinfo'");
        }


        @unlink($adminfile);
        @unlink($orgJs);
        header("Location: index.php?op=updateCache");
    } else {
        $errorMsg = $_GET['msg'];
        include 'errorInfo.php';
        exit();
    }
} elseif ($option == 'updateCache') {
    // 初始化ibos，执行各个已安装模块有extention.php的安装文件，更新缓存
    file_put_contents(PATH_ROOT . './data/install.lock', '');
    define('ENGINE', 'LOCAL');
    $yii = PATH_ROOT . 'library/yii.php';
    $ibosApplication = PATH_ROOT . 'system/core/components/Application.php';
    require_once ( $yii );
    require_once ( $ibosApplication );
    $commonConfig = require CONFIG_PATH . 'common.php';
    Yii::setPathOfAlias('application', PATH_ROOT . 'system');
    Yii::createApplication('application\core\components\Application', $commonConfig);
    //设置绑定地址
    $host = getHostInfo();
    $aeskey = Yii::app()->db->createCommand()
            ->select('svalue')
            ->from('{{setting}}')
            ->where("`skey` = 'aeskey'")
            ->queryRow();
    $bindingSrc = Wx::getInstance()->getBindingSrc($aeskey, $host, true);
    Cache::rm('module');
    $allModules = getModuleDirs();
    $customModules = array_diff($allModules, $sysModules);
    $modules = !empty($customModules) ? array_merge($sysModules, $customModules) : $sysModules;
    defined('IN_MODULE_ACTION') or define('IN_MODULE_ACTION', true);
    foreach ($modules as $module) {
        if (getIsInstall($module)) {
            $installPath = getInstallPath($module);
            $config = require $installPath . 'config.php';
            if (isset($config['authorization'])) {
                Module::updateAuthorization($config['authorization'], $module, $config['param']['category']);
            }
            $extentionScript = $installPath . 'extention.php';
            // 执行模块扩展脚本(如果有)
            if (file_exists($extentionScript)) {
                include_once $extentionScript;
            }
        }
    }
    // 安装演示数据
    if (isset($_SESSION['extData']) && $_SESSION['extData'] == md5('extData')) {
        $sqlData = file_get_contents(PATH_ROOT . './install/data/installExtra.sql');
        $search = array('{time}', '{time1}', '{time2}', '{date}', '{date+1}');
        $replace = array(time(), strtotime('-1 hour'), strtotime('+1 hour'), strtotime(date('Y-m-d')), strtotime('-1 day', strtotime(date('Y-m-d'))));
        $sql = str_replace($search, $replace, $sqlData);
        executeSql($sql);
        unset($_SESSION['extData']);
    }

    // 安装工作流数据
    if (getIsInstall('workflow')) {
        $sqlFlowData = file_get_contents(PATH_ROOT . './install/data/installFlow.sql');
        executeSql($sqlFlowData);
    }
    // 为用户添加GUID
    $uidArray = User::model()->fetchUidA();
    foreach ( $uidArray as $uid ) {
        $guid = String::createGuid();
        Yii::app()->db->createCommand()->update( "{{user}}", array( 'guid' => $guid ), "`uid` = '{$uid}'" );
    }
    Cache::update();
    $configfile = CONFIG_PATH . 'config.php';
    $config = require $configfile;
    include 'extInfo.php';
    exit();
} else if ($option == 'tablepreCheck') {
    $dbHost = $_POST['dbHost'];
    $dbAccount = $_POST['dbAccount'];
    $dbPassword = $_POST['dbPassword'];
    $dbName = $_POST['dbName'];
    $tablePre = $_POST['tablePre'];
    if (!function_exists('mysql_connect')) {
        $ret['isSuccess'] = false;
        $ret['msg'] = 'mysql_connect' . $lang['func not exist'];
        echo json_encode($ret);
        exit();
    }
    $link = @mysql_connect($dbHost, $dbAccount, $dbPassword);
    if (!$link) {
        $errno = mysql_errno();
        $error = mysql_error();
        if ($errno == 1045) {
            $errnoMsg = $lang['Database errno 1045'];
        } elseif ($errno == 2003) {
            $errnoMsg = $lang['Database errno 2003'];
        } else {
            $errnoMsg = $lang['Database connect error'];
        }
        $ret['isSuccess'] = false;
        $ret['msg'] = $errnoMsg . $lang['Database error info'] . $error;
        echo json_encode($ret);
        exit();
    } else {
        if ($query = @mysql_query("SHOW TABLES FROM $dbName")) {
            while ($row = mysql_fetch_row($query)) {
                if (preg_match("/^$tablePre/", $row[0])) {
                    $ret['isSuccess'] = false;
                    $ret['tableExist'] = true;
                    $ret['msg'] = $lang['Dbinfo forceinstall invalid'];
                    echo json_encode($ret);
                    exit();
                }
            }
        }
    }
    $ret['isSuccess'] = true;
    echo json_encode($ret);
    exit();
}