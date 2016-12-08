<?php

use application\core\utils\Api;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Model;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\message\core\co\CoApi;
use application\modules\role\model\Role;
use application\modules\user\model\User;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('PRC');
@set_time_limit(1000);
ini_set('memory_limit', '100M');
define('PATH_ROOT', dirname(__FILE__) . '/..');  //ibos根目录

defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('INSTALL_PAGE') || define('INSTALL_PAGE', true);
require PATH_ROOT . '/system/defines.php';
require './include/installLang.php';
require './include/installVar.php';
require './include/installFunction.php';
$yii = PATH_ROOT . '/library/yii.php';
require_once($yii);

if (get('p') == 'phpinfo') {
    phpinfo();
    exit();
}

$option = get('op', '');
if (!in_array($option, array('envCheck', 'configCheck', 'dbCheck', 'moduleCheck',
    'handleInstall', 'handleInstallAll', 'handleAfterInstallAll', 'handleUpdateCoinfo',
    'handleCheckSaas', 'handleUpdateData'))
) {
    $option = '';
}
//环境检查要提前，不然没有zend解密的话，就无法创建yii应用
if ($option == 'envCheck') {
    envCheckOp();
}
Yii::setPathOfAlias('application', PATH_ROOT . '/system');
if (in_array(ENGINE, array('LOCAL', 'SAE'))) {
    if ($option == 'dbCheck') {
        $extData = post('extData');
        $extraData = isset($extData) && !empty($extData) ? 1 : 0;
        setcookie('install_config', json_encode(array('extData' => $extraData)));
    }
    if (true === checkInstallLock()) {
        return InstallCheck();
    }
} else if (ENGINE == 'SAAS') {
    $saasConfigPath = PATH_ROOT . '/saas_config.php';
    if (file_exists($saasConfigPath)) {
        $saasConfig = include $saasConfigPath;
        $sign = get('sign', '');
        if (md5($saasConfig['saasSignCode'] . post('qycode')) != $sign) {
            echo '签名错误';
            die;
        }
    } else {
        echo '需要配置SAAS环境';
        die();
    }
    //SAAS
    if (!post('qycode')) {
        return ajaxReturn(array('isSuccess' => false, 'msg' => '必须带POST参数qycode（企业代码）'));
    } else {
        defined('CORP_CODE') || define('CORP_CODE', post('qycode'));
    }
} else {
    echo 'engine error';
    die;
}


//请求的各个接口
//必须参数[op]，值如下
//返回json，格式为isSuccess,msg,data
switch (1) {
    //检查是否有配置
    case $option == 'configCheck':
        configCheckOp();
        break;
    //检查数据库参数
    case $option == 'dbCheck':
        if (post('submitDbInit')) {
            dbCheckOp();
        } else {
            return ajaxReturn(array('isSuccess' => false, 'msg' => lang('Request tainting')));
        }
        break;
    //检测存在的模块
    case $option == 'moduleCheck':
        moduleCheckOp();
        break;
    //处理安装（循环）
    case $option == 'handleInstall':
        $config = getDbConfig();
        if (!empty($config)) {
            Yii::createWebApplication($config);
        } else {
            return ajaxReturn(array('isSuccess' => false, 'msg' => '请先请求dbCheck步骤'));
        }
        handleInstallOp();
        break;
    case $option == 'handleInstallAll':
        //只有saas版才会进这里
        handleInstallAllOp();
        break;
    case $option == 'handleAfterInstallAll':
        //只有saas版才会进这里
        handleAfterInstallAllOp();
        break;
    case $option == 'handleUpdateCoinfo':
        //只有saas版才会进这里
        handleUpdateCoinfoOp();
        break;
    case $option == 'handleCheckSaas':
        //只有saas版才会进这里
        handleCheckSaasOp();
        break;
    //处理数据更新
    case $option == 'handleUpdateData':
        // 初始化ibos，执行各个已安装模块有extention.php的安装文件，更新缓存
        $commonConfig = require CONFIG_PATH . 'common.php';
        Yii::createApplication('application\core\components\Application', $commonConfig);
        handleUpdateDataOp();
        break;

    //安装检查
    default:
        InstallCheck();
        break;
}

/**
 * 环境检查
 * @global array $envItems 环境相关
 * @global array $funcItems 函数相关
 * @global array $filesockItems 请求相关
 * @global array $dirfileItems 目录权限相关
 * @global array $extLoadedItems 扩展相关
 */
function envCheckOp()
{
    $isSuccess = true;
    $msg = '';
    global $envItems, $funcItems, $filesockItems, $dirfileItems, $extLoadedItems;
    $envCheck = envCheck($envItems);
    $funcCheck = funcCheck($funcItems);
    $filesorkCheck = filesorkCheck($filesockItems);
    $dirfileCheck = dirfileCheck($dirfileItems);
    $extLoadedCheck = extLoadedCheck($extLoadedItems);

    if (!$envCheck['envCheckRes'] ||
        !$funcCheck['funcCheckRes'] ||
        !$filesorkCheck['filesorkCheckRes'] ||
        !$dirfileCheck['dirfileCheckRes'] ||
        !$extLoadedCheck['extLoadedCheckRes']
    ) {
        $isSuccess = false;
    }
    $ajaxReturn = array(
        'isSuccess' => $isSuccess,
        'msg' => $msg,
        'data' => array(
            'envCheck' => $envCheck,
            'funcCheck' => $funcCheck,
            'filesorkCheck' => $filesorkCheck,
            'dirfileCheck' => $dirfileCheck,
            'extLoadedCheck' => $extLoadedCheck,
        ),
    );
    return ajaxReturn($ajaxReturn);
}

/**
 * 数据库配置检查
 * 如果存在配置则返回，否则给出默认的返回
 */
function configCheckOp()
{
    $isSuccess = true;
    $msg = '';
    // 创建数据库数据
    $configFile = CONFIG_PATH . 'config.php';
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
            'port' => '3306', // 端口
            'dbname' => 'ibos', // 数据库名
            'tableprefix' => 'ibos_', // 数据表前缀
            'adminAccount' => '', // 管理员账号
            'adminPassword' => ''  // 管理员密码
        );
    }
    $ajaxReturn = array(
        'isSuccess' => $isSuccess,
        'msg' => $msg,
        'data' => $dbInitData,
    );
    return ajaxReturn($ajaxReturn);
}

/**
 * 数据库相关检查
 * 接收参数：
 *        POST    dbHost 数据库主机（带端口）
 *                dbAccount 数据库账号
 *                dbPassword 数据库密码
 *                dbName 数据库名
 *                adminAccount 管理员账号
 *                adminPassword 管理员密码
 *                fullname 企业全称
 *                shortname 企业简称
 *                qycode 企业代码
 * 可能：
 * 返回账号密码等验证结果
 * 返回由企业代码确定的表前缀是否已经存在的验证结果
 * 返回数据库是否连接成功等信息的验证结果
 * 返回数据库能否创建的验证结果
 * 返回成功
 * 同时会：
 * 创建aeskey文件
 * 创建config.php文件，admin.php文件（之后需要删除）
 * @global array $adminInfo
 * @global array $moduleSql
 */
function dbCheckOp()
{
    global $adminInfo, $moduleSql;
    $dbHost = post('dbHost');
    $dbAccount = post('dbAccount');
    $dbPassword = post('dbPassword');
    $dbName = post('dbName');
    $adminName = post('adminName');
    $adminAccount = post('adminAccount');
    $adminPassword = post('adminPassword');
    $corpFullname = post('fullname');
    $corpShortname = post('shortname');
    $corpCode = strtolower(post('qycode'));
    //企业代码为表前缀
    $dbPre = $corpCode . '_';
    //设置aeskey
    $hostInfo = getHostInfo();

    // 强制安装
    $enforce = post('enforce', 0);

    $aeskeyPath = PATH_ROOT . '/data/aes.key';
    $aeskey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $hostInfo . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 14, 10) . StringUtil::random(33);
    $aeskeyCreatetime = TIMESTAMP;
    if (file_exists($aeskeyPath)) {
        $aeskeyContent = file_get_contents($aeskeyPath);
        if (!empty($aeskeyContent)) {
            $aeskeyArray = json_decode(base64_decode($aeskeyContent), true);
            $aeskey = $aeskeyArray['aeskey'];
            $aeskeyCreatetime = $aeskeyArray['time'];
        }
    }
    $aeskeyArray = array(
        'aeskey' => $aeskey,
        'time' => $aeskeyCreatetime,
    );
    file_put_contents(PATH_ROOT . '/data/aes.key', base64_encode(json_encode($aeskeyArray)));

    $postHost = explode(':', $dbHost);
    $host = isset($postHost[0]) ? $postHost[0] : '127.0.0.1';
    $port = isset($postHost[1]) ? $postHost[1] : '3306';
    // 检查表单各项
    if (empty($dbAccount)) { // 数据库用户名
        $msg = lang('Dbaccount not empty');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'dbAccount')));
    }
    if (empty($dbPassword)) { // 数据库密码
        $msg = lang('Dbpassword not empty');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'dbPassword')));
    }
    if (empty($adminAccount)) { // 管理员账号
        $msg = lang('Adminaccount not empty');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'adminAccount')));
    }
    if (!preg_match("/^1\\d{10}/", $adminAccount)) {
        $msg = lang('Mobile incorrect format');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'adminAccount')));
    }
    if (!preg_match("/^[a-zA-Z0-9]{5,32}$/", $adminPassword)) { // 管理员密码
        $msg = lang('Adminpassword incorrect format');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'adminPassword')));
    }
    if (!preg_match("/^[a-zA-Z0-9]{4,20}/", $corpCode)) { // 企业代码
        $msg = lang('Invalid corp code');
        return ajaxReturn(array('isSuccess' => false, 'msg' => $msg, 'data' => array('type' => 'qycode')));
    }
    try {
        $conn = new PDO("mysql:host={$host};port={$port}", $dbAccount, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "CREATE DATABASE IF NOT EXISTS {$dbName}";
        $conn->exec($sql);
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        $type = 'dbName';
        if ($e->getCode() == '2002') {
            $msg = '端口错误';
            $type = 'dbHost';
        }
        if ($e->getCode() == '1045') {
            $msg = '数据库账号或密码错误';
            $type = 'dbAccount';
        }
        if ($e->getCode() == '1130') {
            $msg = '数据库服务器错误';
            $type = 'dbHost';
        }
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => '数据库创建失败：' . $msg,
            'data' => array('type' => $type),
        ));
    }
    $pdo = pdo($dbHost, $port, $dbName, $dbAccount, $dbPassword);
    if (is_string($pdo)) {
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => $pdo,
            'data' => array('type' => 'dbAccount'),
        ));
    }
    if ($enforce != '1') {
        foreach ($pdo->query("SHOW TABLES FROM {$dbName}") as $table) {
            if (isset($table[0]) && $table[0] != $dbPre . 'module' && preg_match("/^{$dbPre}/", $table[0])) {
                return ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => lang('Dbinfo forceinstall invalid'),
                    'data' => array('type' => 'dbpre',)
                ));
            }
        }
    }
    $moduleSql = str_replace('{dbpre}', $dbPre, $moduleSql);
    try {
        $pdo->query($moduleSql);  // 提前创建module表，否则后续步骤不能初始化ibos
    } catch (PDOException $e) {
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => $e->getMessage(),
            'data' => array('type' => 'dbAccount'),
        ));
    }
    $pdo = null; //关闭pdo
    $defaultConfigfile = CONFIG_PATH . 'configDefault.php';
    // 获得用户输入的数据库配置数据，替换掉configDefault文件里的配置，用以生成config文件
    $configDefault = file_get_contents($defaultConfigfile);
    $authkey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $host . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 8, 6) . StringUtil::random(10);
    $cookiepre = StringUtil::random(4);
    $configReplace = array( //主配置文件要替换的参数
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
    $salt = StringUtil::random(6);
    $adminReplace = array( // 管理员账号替换信息
        '{username}' => $adminName,
        '{isadministrator}' => 1,
        '{password}' => md5(md5($adminPassword) . $salt),
        '{createtime}' => TIMESTAMP,
        '{salt}' => $salt,
        '{realname}' => '超级管理员',
        '{mobile}' => $adminAccount,
        '{email}' => '',
        '{corpcode}' => $corpCode,
        '{fullname}' => $corpFullname,
        '{shortname}' => $corpShortname,
        '{aeskey}' => $aeskey,
    );
    $administrator = str_replace(array_keys($adminReplace), array_values($adminReplace), $adminInfo);
    file_put_contents(CONFIG_PATH . 'admin.php', $administrator);
    return ajaxReturn(array('isSuccess' => true, 'msg' => ''));
}

/**
 * 模块检查
 * 返回核心模块和自定义模块的信息
 * @global array $sysModules 核心模块
 */
function moduleCheckOp()
{
    global $sysModules;
    $allModules = Module::getModuleDirs();
    $coreModulesParams = Module::initModuleParameters($sysModules);
    $customModules = array_diff($allModules, $sysModules);
    $customModulesParams = Module::initModuleParameters($customModules);
    $ajaxReturn = array(
        'isSuccess' => true,
        'msg' => '',
        'data' => array(
            'coreModule' => $coreModulesParams,
            'customModule' => $customModulesParams,
        ),
    );
    return ajaxReturn($ajaxReturn);
}

/**
 * 模块安装
 * 接收参数：
 *        POST    modules：模块英文名逗号字符串
 *                installingModule：当前安装的模块（如果不提供，默认会取核心模块第一个也就是main）
 * @global array $sysModules 核心模块
 */
function handleInstallOp()
{
    global $sysModules;
    $installModules = post('modules'); // 要安装的模块
    $modules = explode(',', $installModules);
    $customModules = array_diff($modules, $sysModules);

    $installModuleArray = !empty($customModules) ?
        array_merge($sysModules, $customModules) :
        $sysModules;
    $installingModule = post('installingModule', '');
    //至少一个模块开始
    if (empty($installingModule)) {
        $installingModule = $installModuleArray[0];
    }
    $moduleNums = count($installModuleArray);
    $isSuccess = Module::install($installingModule); // 执行安装模块
    if ($isSuccess) {
        foreach ($installModuleArray as $k => $module) {
            if ($module == $installingModule) {
                $index = $k + 1;
                if ($index < count($installModuleArray)) {
                    $nextModule = $installModuleArray[$index]; // 下一个要安装的模块
                    $nextModuleName = Module::getModuleName($nextModule); // 下一个要安装的模块名
                    $process = number_format(($index / $moduleNums) * 100, 1) . '%'; // 完成度
                    return ajaxReturn(array('isSuccess' => true,
                        'msg' => '',
                        'data' => array(
                            'complete' => 0,
                            'process' => $process,
                            'nextModule' => $nextModule,
                            'nextModuleName' => $nextModuleName
                        )));
                } else {
                    return ajaxReturn(array('isSuccess' => true,
                        'msg' => '',
                        'data' => array(
                            'complete' => 1,
                            'process' => '100%',
                        )));
                }
            }
        }
    } else {
        return ajaxReturn(array('isSuccess' => false,
            'msg' => $installingModule . lang('Install module failed'),
            'data' => array()));
    }
}

/**
 * 一次性安装全部（读作：选择了的）模块~~~
 * 接收：
 *        POST    modules 模块英文名逗号字符串
 * @global array $sysModules
 */
function handleInstallAllOp()
{
    global $sysModules, $saasConfig;

    $corpCode = strtolower(post('qycode'));

    $k = 0;
    if (strpos($corpCode, 'test') === 0) {
        $k = 1;
    }
    $saasdb = $saasConfig['saasdb'][$k];

    //不带端口的域名
    $dbHost = post('dbHost', $saasdb['dbHost']);
    $dbPort = post('dbPort', $saasdb['dbPort']);
    $dbAccount = post('dbAccount', $saasdb['dbAccount']);
    $dbPassword = post('dbPassword', $saasdb['dbPassword']);
    $dbName = post('dbName', $saasdb['dbName']);

    $adminName = post('adminName');
    $realNameTemp = post('realName');
    $realName = empty($realNameTemp) ? $adminName : $realNameTemp;
    $adminAccount = post('adminAccount');
    $adminPassword = post('adminPassword');
    $saltTemp = post('passwordsalt');
    $salt = empty($saltTemp) ? StringUtil::random(6) : $saltTemp;
    $passwordTemp = post('password');
    $password = empty($passwordTemp) ? md5(md5($adminPassword) . $salt) : $passwordTemp;

    $corpFullname = post('fullname');
    $corpShortname = post('shortname');
    $platformTemp = post('platform');
    $platform = !empty($platformTemp) ? $platformTemp : 'saas';
    $channel = post('channel', '');

    //企业代码为表前缀
    $dbPre = $corpCode . '_';
    $pdo = null;
    $corpRow = getCorpByCode($corpCode, $pdo);
    if (!empty($corpRow)) {
        if (!empty($corpRow['installed'])) {
            $admin = json_decode($corpRow['super'], true);
            $pdo = null;
            return ajaxReturn(array('isSuccess' => false, 'msg' => '已经安装了，无法使用该企业代码安装', 'data' => array(
                'installed' => 1,
                'aeskey' => $admin['aeskey']
            )
            ));
        } else {
            if (!empty($corpRow) && empty($corpRow['installed'])) {
                $query = $pdo->prepare(" DELETE FROM `config` WHERE (`corpcode` = :corpcode ) ");
                $query->bindParam(":corpcode", $corpCode, PDO::PARAM_STR);
                $query->execute();
            }
        }
    }

    $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ?: '';

    $aeskey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $dbHost . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 14, 10) . StringUtil::random(33);
    $authkey = substr(md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $dbHost . $dbName . $dbAccount . $dbPassword . $dbPre . time()), 8, 6) . StringUtil::random(10);
    $cookiepre = StringUtil::random(4);
    $config = array(
        // ----------------------------  CONFIG ENV  -----------------------------//
        'env' => array(
            'language' => 'zh_cn',
            'theme' => 'default'
        ),
        // ----------------------------  CONFIG DB  ----------------------------- //
        'db' => array(
            'host' => $dbHost,
            'port' => $dbPort,
            'dbname' => $dbName,
            'username' => $dbAccount,
            'password' => $dbPassword,
            'tableprefix' => $dbPre,
            'charset' => DBCHARSET
        ),
// -------------------------  CONFIG SECURITY  -------------------------- //
        'security' => array(
            'authkey' => $authkey,
        ),
// --------------------------  CONFIG COOKIE  --------------------------- //
        'cookie' => array(
            'cookiepre' => $cookiepre . '_',
            'cookiedomain' => '',
            'cookiepath' => '/',
        ),
        'cache' => array(
            'options' => array(
                'prefix' => $dbPre,
            )
        ),
    );
    // 创建管理员账号信息文件,安装完成后删除此文件

    $admin = array( // 管理员账号替换信息
        'username' => $adminName,
        'isadministrator' => 1,
        'password' => $password,
        'createtime' => TIMESTAMP,
        'salt' => $salt,
        'realname' => $realName,
        'mobile' => $adminAccount,
        'email' => '',
        'corpcode' => $corpCode,
        'fullname' => $corpFullname,
        'shortname' => $corpShortname,
        'aeskey' => $aeskey,
    );

    $installModules = post('modules'); // 要安装的模块
    $modules = explode(',', $installModules);
    $customModules = array_diff($modules, $sysModules);
    $moduleArray = !empty($customModules) ?
        array_filter(array_merge($sysModules, $customModules)) :
        $sysModules;
    $moduleString = implode(',', $moduleArray);
    $configString = addslashes(json_encode($config));
    $adminString = addslashes(json_encode($admin));
    $installtime = microtime(true);
    $query = $pdo->exec("INSERT INTO `config` ("
        . "`corpcode`, "
        . "`config`, "
        . "`super`, "
        . "`installtime`,"
        . "`module`,"
        . "`mobile`,"
        . "`channel`,"
        . "`platform` ) VALUES ("
        . "'{$corpCode}', "
        . "'{$configString}', "
        . "'{$adminString}', "
        . "'{$installtime}', "
        . "'{$moduleString}', "
        . "'{$adminAccount}', "
        . "'{$channel}', "
        . "'{$platform}' )");
    $pdo = null;
    $dbConfig = array(
        'basePath' => PATH_ROOT . '/system',
        'components' => array(
            'db' => array(
                'connectionString' => "mysql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['dbname']}",
                'emulatePrepare' => true,
                'username' => $config['db']['username'],
                'password' => $config['db']['password'],
                'charset' => $config['db']['charset'],
                'tablePrefix' => $config['db']['tableprefix'],
            )
        ),
    );
    Yii::createWebApplication($dbConfig);
    foreach ($moduleArray as $k => $module) {
        Module::install($module); // 执行安装模块
    }
    return ajaxReturn(array('isSuccess' => true, 'msg' => '注册安装数据完成'));
}

function handleCheckSaasOp()
{
    global $saasConfig;
    $corpCode = strtolower(post('qycode'));
    $keepCode = $saasConfig['keepcode'];
    if (in_array(strtolower($corpCode), $keepCode)) {
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => '不允许使用保留企业代码',
            'data' => array(
                'type' => 'keepcode',
            ),
        ));
    }
    if (!preg_match('/^[a-zA-Z\d]{4,20}$/', $corpCode)) {
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => '企业代码只能是英文和数字的4~20位的组合',
            'data' => array(
                'type' => 'format',
            ),
        ));
    }
    $pdo = null;
    $corpRow = getCorpByCode($corpCode, $pdo);
    if (!empty($corpRow)) {
        if (!empty($corpRow['installed'])) {
            $admin = json_decode($corpRow['super'], true);
            $pdo = null;

            return ajaxReturn(array(
                'isSuccess' => false,
                'msg' => '已经安装协同云',
                'data' => array(
                    'type' => 'saasExist',
                    'hasSaas' => true,
                    'aeskey' => $admin['aeskey'],
                    'fullname' => $admin['fullname'],
                    'shortname' => $admin['shortname'],
                )
            ));
        } else {
            if (!empty($corpRow) && empty($corpRow['installed'])) {
                $query = $pdo->prepare(" DELETE FROM `config` WHERE (`corpcode` = :corpcode ) ");
                $query->bindParam(":corpcode", $corpCode, PDO::PARAM_STR);
                $query->execute();
                $pdo = null;
            }
        }
    }
    $res = CoApi::getInstance()->searchCorp(strtolower($corpCode), true);
    if (isset($res['code']) && $res['code'] == '0') {
        if (!empty($res['data'])) {
            return ajaxReturn(array(
                'isSuccess' => false,
                'msg' => '企业代码已被注册',
                'data' => array(
                    'type' => 'coExist',
                ),
            ));
        }
    }

    return ajaxReturn(array(
        'isSuccess' => true,
        'msg' => '没有安装SAAS，可以开始新的安装',
        'data' => array(
            'hasSaas' => false,
        ),
    ));
}

function handleAfterInstallAllOp()
{
    global $saasConfig;
    $corpCode = strtolower(post('qycode'));
    $smsContent = post('sms');
    $pdo = null;
    $corpRow = getCorpByCode($corpCode, $pdo);
    // 检查数据库连接正确性

    if (!empty($corpRow)) {
        $admin = json_decode($corpRow['super'], true);
        $ibosApplication = PATH_ROOT . '/system/core/components/Application.php';
        require_once($ibosApplication);
        $commonConfig = require CONFIG_PATH . 'common.php';
        Yii::createApplication('application\core\components\Application', $commonConfig);
        //防止接口重复被调用导致n多个管理员的问题，嗯……这个情况吓我一跳
        $user1 = Yii::app()->db->createCommand()
            ->select()
            ->from('{{user}}')
            ->where(" `uid` = 1 ")
            ->queryRow();
        if (empty($user1)) {
            Yii::app()->db->createCommand()
                ->insert('{{user}}', array(
                    'username' => $admin['username'],
                    'isadministrator' => 1,
                    'password' => $admin['password'],
                    'createtime' => TIMESTAMP,
                    'salt' => $admin['salt'],
                    'realname' => $admin['realname'],
                    'mobile' => $admin['mobile'],
                    'email' => '',
                ));
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
        }

        //aeskey存入表里
        Yii::app()->db->createCommand()
            ->update('{{setting}}'
                , array('svalue' => $admin['aeskey'])
                , " `skey`= 'aeskey'");
        //更新Setting的unit
        if ($corpRow['channel'] == 'woqi') {
            $systemurl = 'http://' . $corpCode . '.kbg.unimip.cn';
        } else {
            $systemurl = 'http://' . $corpCode . '.saas.ibos.cn';
        }
        $unit = StringUtil::utf8Unserialize(
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
        $unit['fullname'] = $admin['fullname'];
        $unit['shortname'] = $admin['shortname'];
        $unit['corpcode'] = $admin['corpcode'];
        $unit['systemurl'] = $systemurl;
        $unit['logourl'] = 'static/image/logo.png';
        foreach ($unitConfig as $value) {
            if (!isset($unit[$value])) {
                $unit[$value] = '';
            }
        }
        Yii::app()->db->createCommand()
            ->update('{{setting}}'
                , array('svalue' => serialize($unit))
                , " `skey`= 'unit'");
        defined('IN_MODULE_ACTION') || define('IN_MODULE_ACTION', true);
        $moduleArray = explode(',', $corpRow['module']);
        foreach ($moduleArray as $module) {
            if (Module::getIsInstall($module)) {
                $installPath = Module::getInstallPath($module);
                $config = require $installPath . 'config.php';
                if (isset($config['authorization']) && isset($config['param']['category'])) {
                    Module::updateAuthorization($config['authorization'], $module, $config['param']['category']);
                }
                $extentionScript = $installPath . 'extention.php';
                // 执行模块扩展脚本(如果有)
                if (file_exists($extentionScript)) {
                    include_once $extentionScript;
                }
            }
        }
        // 为用户添加GUID
        $uidArray = User::model()->fetchUidA(true);
        foreach ($uidArray as $uid) {
            $guid = StringUtil::createGuid();
            Yii::app()->db->createCommand()->update("{{user}}", array('guid' => $guid), "`uid` = '{$uid}'");
        }
        // 角色默认权限
        Role::model()->defaultAuth();
        Cache::update();

        //重新打开config服务器
        //写入安装成功标识
        $installcost = microtime(true) - $corpRow['installtime'];


        $query = $pdo->prepare("UPDATE `config` SET "
            . "`installcost`= :installcost, "
            . "`installed`='1' WHERE ("
            . "`corpcode`= :corpCode )");
        $query->bindParam(":installcost", $installcost);
        $query->bindParam(":corpCode", $corpCode);
        $query->execute();


        $pdo = null;
        //发送短信

        $sms = "【酷办公】{$admin['realname']}, 你已成功开通【{$admin['fullname']}】酷办公OA，你的网址为：{$systemurl}，管理员账号密码与酷办公账号密码一致。";
        $message = !empty($smsContent) ? $smsContent : $sms;
        $url = $saasConfig['url'];
        $get = array(
            'account' => $saasConfig['account'],
            'pswd' => $saasConfig['pswd'],
            'mobile' => $corpRow['mobile'],
            'msg' => $message
        );

        $res = Api::getInstance()->fetchResult($url, $get);

        return ajaxReturn(
            array('isSuccess' => true, 'msg' => '安装成功', 'data' => array(
                'sms' => $res,
                'aeskey' => $admin['aeskey']
            )));
    } else {
        $pdo = null;
        return ajaxReturn(array('isSuccess' => false, 'msg' => '请确认执行了handleInstallAll请求'));
    }
}

function handleUpdateCoinfoOp()
{
    $corpCode = strtolower(post('qycode'));
    $pdo = null;
    $corpRow = getCorpByCode($corpCode, $pdo);
    // 检查数据库连接正确性
    if (!empty($corpRow)) {
        $ibosApplication = PATH_ROOT . '/system/core/components/Application.php';
        require_once($ibosApplication);
        $commonConfig = require CONFIG_PATH . 'common.php';
        Yii::createApplication('application\core\components\Application', $commonConfig);
        //防止接口重复被调用导致n多个管理员的问题，嗯……这个情况吓我一跳
        $user1 = Yii::app()->db->createCommand()
            ->select()
            ->from('{{user}}')
            ->where(" `uid` = 1 ")
            ->queryRow();
        if (!empty($user1)) {
            $coGuid = post('guid');
            $corpid = post('cocorpid');
            $shortname = post('shortname');
            $fullname = post('fullname');
            $mobile = post('mobile');
            $string = serialize(array(
                'guid' => $coGuid,
                'mobile' => $mobile,
                'corpid' => $corpid,
                'corpshortname' => $shortname,
                'corpname' => $fullname,
                'corplogo' => ''
            ));
            $uid = $user1['uid'];
            Yii::app()->db->createCommand()
                ->insert('{{user_binding}}', array('uid' => $uid, 'bindvalue' => $coGuid, 'app' => 'co'));
            Yii::app()->db->createCommand()
                ->update('{{setting}}', array(
                    'svalue' => $string,
                ), " `skey` = 'coinfo' ");
            Yii::app()->db->createCommand()
                ->update('{{setting}}', array(
                    'svalue' => 1,
                ), " `skey` = 'cobinding' ");
        }
        Cache::update();
        return ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '更新酷办公信息成功',));
    }
    return ajaxReturn(array(
        'isSuccess' => false,
        'msg' => '更新酷办公信息失败',
    ));
}

/**
 * 更新额外的数据
 * 接收：
 *        modules：模块英文名逗号字符串，这个模块名就是刚才选择的模块
 * 安装时由于核心模块没有全部安装好，所以模块脚本可能无法安装，所以需要拆开执行
 * 会：
 * 执行额外的脚本
 * 执行演示数据（如果选择了的话）
 * 添加用户的GUID
 * 更新角色权限节点
 * 更新系统缓存
 * @global array $sysModules
 */
function handleUpdateDataOp()
{
    global $sysModules;
    $adminfile = CONFIG_PATH . 'admin.php';
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
    //aeskey存入表里
    Yii::app()->db->createCommand()
        ->update('{{setting}}'
            , array('svalue' => $adminco['aeskey'])
            , " `skey`= 'aeskey'");
    //更新Setting的unit
    $systemurl = substr(Env::getSiteUrl(), 0, -9);
    $unit = StringUtil::utf8Unserialize(
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
        ->update('{{setting}}'
            , array('svalue' => serialize($unit))
            , " `skey`= 'unit'");
    @unlink($adminfile);
    $installModules = post('modules');
    $modules = explode(',', $installModules);
    $customModules = array_diff($modules, $sysModules);
    $moduleArray = !empty($customModules) ?
        array_merge($sysModules, $customModules) :
        $sysModules;
    defined('IN_MODULE_ACTION') || define('IN_MODULE_ACTION', true);
    foreach ($moduleArray as $module) {
        if (Module::getIsInstall($module)) {
            $installPath = Module::getInstallPath($module);
            $config = require $installPath . 'config.php';
            if (isset($config['authorization']) && isset($config['param']['category'])) {
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
    $cookie = $_COOKIE['install_config'];
    if (!empty($cookie)) {
        $cookieArray = json_decode($cookie, true);
        if (!empty($cookieArray['extData'])) {
            $sqlData = file_get_contents(PATH_ROOT . '/install/data/installExtra.sql');
            $search = array('{time}', '{time1}', '{time2}', '{date}', '{date+1}');
            $replace = array(time(), strtotime('-1 hour'), strtotime('+1 hour'), strtotime(date('Y-m-d')), strtotime('-1 day', strtotime(date('Y-m-d'))));
            $sql = str_replace($search, $replace, $sqlData);
            Model::executeSqls($sql);
        }
    }
    // 为用户添加GUID
    $uidArray = User::model()->fetchUidA(true);
    foreach ($uidArray as $uid) {
        $guid = StringUtil::createGuid();
        Yii::app()->db->createCommand()->update("{{user}}", array('guid' => $guid), "`uid` = '{$uid}'");
    }
    // 角色默认权限
    Role::model()->defaultAuth();
    Cache::update();
    file_put_contents(PATH_ROOT . '/data/install.lock', '');
    setcookie('install_config', null);
    return ajaxReturn(array('isSuccess' => true, 'msg' => ''));
}

/**
 * 安装锁定检测
 * 同时：
 * 返回一些必要的信息
 * @global string $lockfile lock文件
 */
function InstallCheck()
{
    global $lockfile;
    $checkInstall = checkInstallLock();
    return ajaxReturn(array(
        'isSuccess' => !$checkInstall,
        'msg' => $checkInstall ? lang('Install locked') . $lockfile : '',
        'data' => array(
            'version' => VERSION . ' ' . VERSION_DATE
        ),));
}

function checkInstallLock()
{
    global $lockfile;
    if (file_exists($lockfile)) {
        return true;
    } else {
        return false;
    }
}

/**
 * ajax返回json字符串
 * @param array $ajaxReturn
 */
function ajaxReturn($ajaxReturn)
{
    echo json_encode($ajaxReturn);
    exit();
}

function getDbConfig()
{
    if (ENGINE === 'SAAS') {
        $corpCode = post('qycode');
        $corpRow = getCorpByCode($corpCode);
        $ibosConfig = !empty($corpRow) ? json_decode($corpRow, true) : array();
    } else {
        $configfile = CONFIG_PATH . 'config.php';
        $ibosConfig = file_exists($configfile) ? require $configfile : array();
    }
    if (!empty($ibosConfig)) {
        $config = array(
            'basePath' => PATH_ROOT . '/system',
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
        return $config;
    } else {
        return array();
    }
}

function getCorpByCode($corpCode, &$pdo = null)
{
    global $saasConfig;
    $pdo = pdo($saasConfig['db']['host'], $saasConfig['db']['port']
        , $saasConfig['db']['dbname'], $saasConfig['db']['username']
        , $saasConfig['db']['password']);
    if (is_string($pdo)) {
        return ajaxReturn(array(
            'isSuccess' => false,
            'msg' => $pdo,
        ));
    }
    $query = $pdo->prepare(" SELECT * FROM `config` WHERE `corpcode` = :corpcode ");
    $query->bindParam(":corpcode", $corpCode, PDO::PARAM_STR);
    $query->execute();
    $corpRow = $query->fetch(PDO::FETCH_ASSOC);
    return $corpRow;
}

function post($param = null, $default = null)
{
    if (null === $param) {
        $temp = $_POST;
    } else {
        $temp = isset($_POST[$param]) ? $_POST[$param] : $default;
    }
    return addslashes($temp);
}

function get($param = null, $default = null)
{
    if (null === $param) {
        $temp = $_GET;
    } else {
        $temp = isset($_GET[$param]) ? $_GET[$param] : $default;
    }
    return addslashes($temp);
}

function pdo($host, $port, $dbname, $user, $password, $charset = 'utf8')
{
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
    $options = array();
    if (version_compare(PHP_VERSION, '5.3.6', '<')) {
        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $charset;
        }
    } else {
        $dsn .= ';charset=' . $charset;
    }
    static $db = null;
    if (null === $db) {
        try {
            $db = new PDO($dsn, $user, $password, $options);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    return $db;
}
