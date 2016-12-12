<?php

/**
 * 继承到 CAssetManager的 IBOS 资源管理文件.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2014 IBOS Inc
 */
/**
 * IBOS资源管理类
 *
 * @package application.core.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: AssetManager.php -1   $
 */

namespace application\core\components;

use application\core\utils\File;
use application\core\utils\Ibos;
use CAssetManager;
use CException;

class AssetManager extends CAssetManager
{

    /**
     * 静态资源文件夹的所在路径
     * @var string
     */
    private $_basePath;

    /**
     * 静态资源文件夹的访问地址
     * @var string
     */
    private $_baseUrl;

    /**
     * @var array published assets
     */
    private $_published = array();

    /**
     * 获取静态资源文件夹所在路径
     * @return type
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $request = Ibos::app()->getRequest();
            $this->setBasePath(dirname($request->getScriptFile()) . DIRECTORY_SEPARATOR . 'static');
        }
        return $this->_basePath;
    }

    /**
     * 设置静态资源文件夹基本路径
     * @param type $value
     * @throws CException
     */
    public function setBasePath($value)
    {
        if (($basePath = realpath($value)) !== false && is_dir($basePath) && is_writable($basePath)) {
            $this->_basePath = $basePath;
        } else {
            throw new CException(Ibos::t('yii', 'CAssetManager.basePath "{path}" is invalid. Please make sure the directory exists and is writable by the Web server process.', array('{path}' => $value)));
        }
    }

    /**
     * 设置asset URL
     * @param type $value
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl = rtrim($value, '/');
    }

    /**
     * 获取assetUrl
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $request = Ibos::app()->getRequest();
            $this->setBaseUrl($request->getBaseUrl() . '/' . 'static');
        }
        return $this->_baseUrl;
    }

    /**
     * 生成一个 CRC32的值用于当前的目录路径，这样生成的值会比MD5生成的更不易冲突。
     * 函数使用静态资源文件路径，Yii 版本号与ibos版本号作为生成hash的值
     * @param string $path 要hash的静态资源文件夹
     * @return string hash 后的字符串.
     */
    public function hash($path)
    {
        return sprintf('%x', crc32($path . Ibos::getVersion() . VERSION));
    }

    /**
     * 获取指定模块或当前模块的静态资源发布地址
     * @param string $module 模块名
     * @return string
     * @throws EnvException
     */
    public function getAssetsUrl($module = '')
    {
        $path = Ibos::getPathOfAlias("application.modules.{$module}.static");
        if (LOCAL) {
            // 本地环境，直接发布到公共资源文件夹目录
            $assetUrl = $this->publish($path);
        } else {
            // 云平台，返回物理路径直接访问。
            $assetUrl = str_replace('\\', '/', stristr($path, 'system'));
        }
        return $assetUrl;
    }

    /**
     * 重新发布所有静态资源文件夹
     * @return boolean
     */
    public function republicAll()
    {
        // 云平台因为都会限制本地IO，所以不作发布操作
        if (LOCAL) {
            $except = array('image', 'css', 'font', 'js', 'office', '.', '..', 'login');
            $basePath = $this->getBasePath();
            $dir = @opendir($basePath);
            while ($entry = @readdir($dir)) {
                $file = $basePath . DIRECTORY_SEPARATOR . $entry;
                if (!in_array($entry, array_merge($this->excludeFiles, $except))) {
                    if (is_dir($file)) {
                        File::clearDirs($file . '/');
                    }
                }
            }
            closedir($dir);
            $modules = Ibos::app()->getEnabledModule();
            foreach ($modules as $module) {
                $path = Ibos::getPathOfAlias("application.modules.{$module['module']}.static");
                if (is_dir($path)) {
                    $this->publish($path, false, -1, true);
                }
            }
        }
        return true;
    }

}
