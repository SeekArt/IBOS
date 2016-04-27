<?php

/**
 * 模块管理函数库文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 模块管理函数库类，提供安装模块，检测模块，协助模块一系列操作功能的实现
 * @package application.core.utils
 * @version $Id: module.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\core\model\Module as ModuleModel;
use application\modules\role\utils\Auth;
use CException;
use CJSON;

class Module {

    /**
     * 模块文件夹别名
     */
    const MODULE_ALIAS = 'modules';

    /**
     * 模块安装文件夹别名
     */
    const INSTALL_PATH_ALIAS = 'install';

    /**
     * 模块卸载文件夹别名
     */
    const UNINSTALL_PATH_ALIAS = 'uninstall';

    /**
     * 路径分隔常量
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 核心模块
     * @var array
     */
    private static $_coreModule = array(
        'main', 'user', 'department',
        'position', 'message', 'dashboard',
        'role'
    );

    /**
     * 核心依赖模块，相比于核心模块的不可关闭与不可卸载来说，它能关闭但不能卸载
     * 因为这种类型的模块在代码与视图层面上与系统核心模块有耦合性
     * @var array
     */
    private static $_sysDependModule = array(
        'weibo'
    );

    /**
     * 检查某个模块是否可用
     * @param string $moduleName 模块名
     * @return boolean 是否可用
     */
    public static function getIsEnabled($moduleName) {
        static $modules = array();
        if (empty($modules)) {
            $modules = IBOS::app()->getEnabledModule();
        }
        return isset($modules[$moduleName]);
    }

    /**
     * 获得核心模块数组
     * @return array
     */
    public static function getCoreModule() {
        return self::$_coreModule;
    }

    /**
     * 获得核心依赖模块数组
     * @return array
     */
    public static function getDependModule() {
        return self::$_sysDependModule;
    }

    /**
     * 执行模块安装
     * @param string $moduleName 模块名
     * @throws CException 检查安装文件夹时的异常
     * @return boolean 安装成功与否
     */
    public static function install($moduleName) {
        defined('IN_MODULE_ACTION') or define('IN_MODULE_ACTION', true);
        $checkError = self::check($moduleName);
        if (!empty($checkError)) {
            throw new CException($checkError);
        }
        $installPath = self::getInstallPath($moduleName);
        // 安装模块模型(如果有)
        $modelSqlFile = $installPath . 'model.sql';
        if (file_exists($modelSqlFile)) {
            $modelSql = file_get_contents($modelSqlFile);
            self::executeSql($modelSql);
        }
        /**
         * 执行额外的sql语句
         */
        $sqlFiles = glob($installPath . '*.sql');
        if (!empty($sqlFiles)) {
            foreach ($sqlFiles as $sqlFile) {
                if (file_exists($sqlFile) && $sqlFile != $installPath . 'model.sql') {
                    $modelSql = file_get_contents($sqlFile);
                    self::executeSql($modelSql);
                }
            }
        }

        // 处理模块配置，写入数据
        $config = require $installPath . 'config.php';
        // 是否有模块图标文件,有的话写入数据库标识避免以后的引用判断
        $icon = self::getModulePath() . $moduleName . '/static/image/icon.png';
        if (is_file($icon)) {
            $config['param']['icon'] = 1;
        } else {
            $config['param']['icon'] = 0;
        }
        // 是否有模块所属分类
        if (!isset($config['param']['category'])) {
            $config['param']['category'] = '';
        }
        // 是否有首页显示
        if (isset($config['param']['indexShow']) && isset($config['param']['indexShow']['link'])) {
            $config['param']['url'] = $config['param']['indexShow']['link'];
        } else {
            $config['param']['url'] = '';
        }
        $configs = CJSON::encode($config);
        $record = array(
            'module' => $moduleName,
            'name' => $config['param']['name'],
            'url' => $config['param']['url'],
            'category' => $config['param']['category'],
            'version' => $config['param']['version'],
            'description' => $config['param']['description'],
            'icon' => $config['param']['icon'],
            'config' => $configs,
            'installdate' => TIMESTAMP
        );
        if (in_array($moduleName, self::getCoreModule())) {
            $record['iscore'] = 1;
        } elseif (in_array($moduleName, self::getDependModule())) {
            $record['iscore'] = 2;
        } else {
            $record['iscore'] = 0;
        }
        $insertStatus = ModuleModel::model()->add($record);
        Cache::rm('module');
        if ($insertStatus && isset($config['authorization'])) {
            self::updateAuthorization($config['authorization'], $moduleName, $config['param']['category']);
        }
        $extentionScript = $installPath . 'extention.php';
        // 执行模块扩展脚本(如果有)
        if (file_exists($extentionScript)) {
            include_once $extentionScript;
        }
        return $insertStatus;
    }

    /**
     * 执行模块卸载
     * @param string $moduleName 模块名
     * @return boolean
     */
    public static function uninstall($moduleName) {
        defined('IN_MODULE_ACTION') or define('IN_MODULE_ACTION', true);
        $record = ModuleModel::model()->fetchByPk($moduleName);
        if (!empty($record)) {
            ModuleModel::model()->deleteByPk($moduleName);
            Cache::rm('module');
        }
        $uninstallPath = self::getUninstallPath($moduleName);
        $extentionScript = $uninstallPath . 'extention.php';
        $modelSqlFile = $uninstallPath . 'model.sql';
        // 卸载模块模型(如果有)
        if (file_exists($modelSqlFile)) {
            $modelSql = file_get_contents($modelSqlFile);
            self::executeSql($modelSql);
        }
        // 执行模块扩展脚本(如果有)
        if (is_file($extentionScript)) {
            include_once $extentionScript;
        }
        return true;
    }

    /**
     * 获取所有模块目录
     * @return array
     */
    public static function getModuleDirs() {
        $modulePath = self::getModulePath();
        $dirs = (array) glob($modulePath . '*');
        $moduleDirs = array();
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $d = basename($dir);
                $moduleDirs[] = $d;
            }
        }
        return $moduleDirs;
    }

    /**
     * 获取模块文件夹真实路径
     * @return string
     */
    public static function getModulePath() {
        static $path = null;
        if (!$path) {
            $path = IBOS::getPathOfAlias('application') . self::DS . self::MODULE_ALIAS . self::DS;
        }
        return $path;
    }

    /**
     * 获取模块安装文件夹路径
     * @param string $module 模块名
     * @return string
     */
    public static function getInstallPath($module) {
        return self::getModulePath() . $module . self::DS . self::INSTALL_PATH_ALIAS . self::DS;
    }

    /**
     * 获取模块安装文件夹路径
     * @param string $module 模块名
     * @return string
     */
    public static function getUninstallPath($module) {
        return self::getModulePath() . $module . self::DS . self::UNINSTALL_PATH_ALIAS . self::DS;
    }

    /**
     * 过滤掉已安装模块,返回未安装模块名
     * @param array $installedModule 已安装模块
     * @param array $moduleDirs 所有模块的文件夹数组
     * @return array
     */
    public static function filterInstalledModule(array $installedModule, array $moduleDirs) {
        $dirs = array();
        foreach ($moduleDirs as $index => $moduleName) {
            if (array_key_exists($moduleName, $installedModule)) {
                continue;
            } else {
                $dirs[] = $moduleName;
            }
        }
        return $dirs;
    }

    /**
     * 初始化模块配置文件 - 参数部分
     * @param string $moduleName 模块名称
     * @return array
     */
    public static function initModuleParameter($moduleName) {
        defined('IN_MODULE_ACTION') or define('IN_MODULE_ACTION', true);
        $param = array();
        $installPath = self::getInstallPath($moduleName);
        if (is_dir($installPath)) {
            $file = $installPath . 'config.php';
            if (is_file($file) && is_readable($file)) {
                $config = include_once $file;
            }
            if (isset($config) && is_array($config)) {
                $param = (array) $config['param'];
                // 处理模块ICON
                $icon = self::getModulePath() . $moduleName . '/static/image/icon.png';
                if (is_file($icon)) {
                    $param['icon'] = 1;
                } else {
                    $param['icon'] = 0;
                }
                // ------------------------------
            }
        }
        return $param;
    }

    /**
     * 初始化多个模块配置文件 - 参数部分,用于列表
     * @param array $moduleDirs
     * @return array
     */
    public static function initModuleParameters(array $moduleDirs) {
        $modules = array();
        foreach ($moduleDirs as $index => $moduleName) {
            $param = self::initModuleParameter($moduleName);
            if (!empty($param)) {
                $modules[$moduleName] = $param;
            }
        }
        return $modules;
    }

    /**
     * 更新模块配置文件
     * @param mixed $module 要更新的模块名，为空更新全部，可单个模块字符串也可数组格式
     * @param boolean $updateAuth 是否更新授权信息
     * @return boolean
     */
    public static function updateConfig($module = '', $updateAuth = true) {
        static $execute = false;
        if (!$execute) {
            defined('IN_MODULE_ACTION') or define('IN_MODULE_ACTION', true);
            $updateList = empty($module) ? array() : (is_array($module) ? $module : explode(',', $module));
            $modules = array();
            $installedModule = ModuleModel::model()->fetchAllEnabledModule();
            if (!$updateList) {
                foreach ($installedModule as $module) {
                    $modules[] = $module['module'];
                }
            } else {
                $modules = $updateList;
            }
            foreach ($modules as $name) {
                $installPath = self::getInstallPath($name);
                $file = $installPath . 'config.php';
                if (is_file($file) && is_readable($file)) {
                    $config = include_once $file;
                    if (isset($config) && is_array($config) && array_key_exists($name, $installedModule)) {
                        $icon = self::getModulePath() . $name . '/static/image/icon.png';
                        if (is_file($icon)) {
                            $config['param']['icon'] = 1;
                        } else {
                            $config['param']['icon'] = 0;
                        }
                        if (!isset($config['param']['category'])) {
                            $config['param']['category'] = '';
                        }
                        $data = array(
                            'updatedate' => time(),
                            'config' => json_encode($config),
                            'icon' => $config['param']['icon'],
                            'name' => $config['param']['name'],
                            'category' => $config['param']['category'],
                            'version' => $config['param']['version'],
                            'description' => $config['param']['description'],
                        );
                        ModuleModel::model()->modify($name, $data);
                        if (isset($config['authorization']) && $updateAuth) {
                            self::updateAuthorization($config['authorization'], $name, $config['param']['category']);
                        }
                    }
                }
            }
            Cache::rm('module');
            $execute = true;
        }
        return $execute;
    }

    /**
     * 更新授权认证项目
     * @param array $authItem 配置文件中的授权节点数组
     * @param string $moduleName 对应的模块名字
     * @return void
     */
    public static function updateAuthorization($authItem, $moduleName, $category) {
        return Auth::updateAuthorization($authItem, $moduleName, $category);
    }

    /**
     * 检查安装所需条件
     * @param string $moduleName 模块名
     * @return boolean 检查通过与否
     */
    private static function check($moduleName) {
        $error = '';
        // 检查是否已安装
        $record = ModuleModel::model()->findByPk($moduleName);
        if (!empty($record)) {
            $error = IBOS::lang('This module has been installed', 'error');
            return $error;
        }
        // 检查模块安装目录
        $installPath = self::getInstallPath($moduleName);
        if (!is_dir($installPath)) {
            $error = IBOS::lang('Install dir does not exists', 'error');
            return $error;
        }
        // 模块配置文件
        if (!file_exists($installPath . 'config.php')) {
            $error = IBOS::lang('Module config missing', 'error');
            return $error;
        }
        // 配置文件格式，目前只是粗略匹配
        $configFile = $installPath . 'config.php';
        $config = (array) include_once $configFile;
        $configFormatCorrect = isset($config['param']) && isset($config['config']);
        if (!$configFormatCorrect) {
            $error = IBOS::lang('Module config format error', 'error');
            return $error;
        }
        return $error;
    }

    /**
     * 执行mysql.sql文件，创建数据表等
     * @param string $sql sql语句
     */
    public static function executeSql($sql) {
        $sqls = String::splitSql($sql);
        $command = IBOS::app()->db->createCommand();
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '') {
                    $command->setText($sql)->execute();
                }
            }
        } else {
            $command->setText($sqls)->execute();
        }
        return true;
    }

}
