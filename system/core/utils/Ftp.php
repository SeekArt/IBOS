<?php

/**
 * FTP操作工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * FTP工具类，提供FTP操作的所有方法
 *
 * @package application.core.utils
 * @version $Id: ftp.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

class Ftp
{

    // 错误常量定义

    const FTP_ERR_SERVER_DISABLED = -100;
    const FTP_ERR_CONFIG_OFF = -101;
    const FTP_ERR_CONNECT_TO_SERVER = -102;
    const FTP_ERR_USER_NO_LOGGIN = -103;
    const FTP_ERR_CHDIR = -104;
    const FTP_ERR_MKDIR = -105;
    const FTP_ERR_SOURCE_READ = -106;
    const FTP_ERR_TARGET_WRITE = -107;

    /**
     * 是否使用
     * @var boolean
     */
    private $_enabled = false;

    /**
     * 配置
     * @var array
     */
    private $_config = array();

    /**
     * 方法
     * @var function
     */
    private $_func;

    /**
     *
     * @var int
     */
    private $_connectId;

    /**
     * 错误标识
     * @var int
     */
    private $_error;

    /**
     *
     * @staticvar self $object
     * @param type $config
     * @return \self
     */
    public static function getInstance($config = array())
    {
        static $object;
        if (empty($object)) {
            $object = new self($config);
        }
        return $object;
    }

    /**
     * 构造器
     * @param array $config 配置数组
     * @return void
     */
    public function __construct($config = array())
    {
        $ftp = Ibos::app()->setting->get('setting/ftp');
        $this->setError(0);
        $this->_config = !$config ? $ftp : $config;
        $this->_enabled = false;
        if (empty($this->_config['on']) || empty($this->_config['host'])) {
            $this->setError(self::FTP_ERR_CONFIG_OFF);
        } else {
            $this->_func = isset($this->_config['ftpssl']) && function_exists('ftp_ssl_connect') ? 'ftp_ssl_connect' : 'ftpConnect';
            if ($this->_func == 'ftpConnect' && !function_exists('ftpConnect')) {
                $this->setError(self::FTP_ERR_SERVER_DISABLED);
            } else {
                $this->_config['host'] = $this->clear($this->_config['host']);
                $this->_config['port'] = intval($this->_config['port']);
                $this->_config['ssl'] = intval($this->_config['ssl']);
                $this->_config['host'] = $this->clear($this->_config['host']);
                $autoKey = md5(Ibos::app()->setting->get('config/security/authkey'));
                $this->_config['password'] = StringUtil::authCode($this->_config['password'], 'DECODE', $autoKey);
                $this->_config['timeout'] = intval($this->_config['timeout']);
                $this->_enabled = true;
            }
        }
    }

    /**
     * 上传
     * @param string $source
     * @param string $target
     * @return integer
     */
    public function upload($source, $target)
    {
        if ($this->error()) {
            return 0;
        }
        $oldDir = $this->ftpPwd();
        $dirName = dirname($target);
        $fileName = basename($target);
        if (!$this->ftpChdir($dirName)) {
            if ($this->ftpMkdir($dirName)) {
                $this->ftpChmod($dirName);
                if (!$this->ftpChdir($dirName)) {
                    $this->setError(self::FTP_ERR_CHDIR);
                }
                $attachDir = Ibos::app()->setting->get('setting/attachdir');
                $this->ftpPut('index.htm', $attachDir . '/index.htm', FTP_BINARY);
            } else {
                $this->setError(self::FTP_ERR_MKDIR);
            }
        }
        $res = 0;
        if (!$this->error()) {
            $fp = @fopen($source, 'rb');
            if ($fp) {
                $res = $this->ftpFput($fileName, $fp, FTP_BINARY);
                @fclose($fp);
                !$res && $this->setError(self::FTP_ERR_TARGET_WRITE);
            } else {
                $this->setError(self::FTP_ERR_SOURCE_READ);
            }
        }
        $this->ftpChdir($oldDir);
        return $res ? 1 : 0;
    }

    /**
     * 链接
     * @return integer
     */
    public function connect()
    {
        if (!$this->_enabled || empty($this->_config)) {
            return 0;
        } else {
            return $this->ftpConnect(
                $this->config['host'], $this->config['username'], $this->config['password'], $this->config['attachdir'], $this->config['port'], $this->config['timeout'], $this->config['ssl'], $this->config['pasv']
            );
        }
    }

    /**
     * FTP连接
     * @param string $ftpHost
     * @param string $userName
     * @param string $password
     * @param string $ftpPath
     * @param string $ftpPort
     * @param integer $timeout
     * @param integer $ftpssl
     * @param string $ftpPasv
     * @return integer
     */
    public function ftpConnect($ftpHost, $userName, $password, $ftpPath, $ftpPort = 21, $timeout = 30, $ftpssl = 0, $ftpPasv = 0)
    {
        $res = 0;
        $fun = $this->func;
        if ($this->_connectId = $fun($ftpHost, $ftpPort, 20)) {
            $timeout && $this->setOption(FTP_TIMEOUT_SEC, $timeout);
            if ($this->ftpLogin($userName, $password)) {
                $this->ftpPasv($ftpPasv);
                if ($this->ftpChdir($ftpPath)) {
                    $res = $this->_connectId;
                } else {
                    $this->setError(self::FTP_ERR_CHDIR);
                }
            } else {
                $this->setError(self::FTP_ERR_USER_NO_LOGGIN);
            }
        } else {
            $this->setError(self::FTP_ERR_CONNECT_TO_SERVER);
        }
        if ($res > 0) {
            $this->setError();
            $this->_enabled = 1;
        } else {
            $this->_enabled = 0;
            $this->ftpClose();
        }
        return $res;
    }

    /**
     * 获取错误
     * @return string
     */
    public function error()
    {
        return $this->_error;
    }

    /**
     * 过滤字符串
     * @param string $str
     * @return string
     */
    private function clear($str)
    {
        return str_replace(array("\n", "\r", '..'), '', $str);
    }

    /**
     * 设置各种 FTP 运行时选项
     * @param string $cmd FTP_TIMEOUT_SEC 或 FTP_AUTOSEEK
     * @param string $value 本参数取决于要修改哪个 cmd。
     * @return boolean 如果选项能够被设置，返回 true，否则返回 false。
     */
    private function setOption($cmd, $value)
    {
        if (function_exists('ftp_set_option')) {
            return @ftp_set_option($this->_connectId, $cmd, $value);
        }
    }

    /**
     * 建立新目录
     * @param string $directory 要建立的目录路径
     * @return mixed $return 如果成功返回新建的目录名，否则返回 false。
     */
    private function ftpMkdir($directory)
    {
        $directory = $this->clear($directory);
        $ePath = explode('/', $directory);
        $dir = '';
        $comma = '';
        foreach ($ePath as $path) {
            $dir .= $comma . $path;
            $comma = '/';
            $return = @ftp_mkdir($this->_connectId, $dir);
            $this->ftpChmod($dir);
        }
        return $return;
    }

    /**
     * 删除 FTP 服务器上的一个目录
     * @param string $directory 要删除的目录，必须是一个空目录的绝对路径或相对路径。
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpRmdir($directory)
    {
        $directory = $this->clear($directory);
        return @ftp_rmdir($this->_connectId, $directory);
    }

    /**
     * 上传文件到 FTP 服务器
     * @param string $remoteFile 远程文件
     * @param string $localFile 本地文件
     * @param string $mode 传输模式，必须为FTP_BINARY 或 FTP_ASCII
     * @return void
     */
    private function ftpPut($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        $remoteFile = $this->clear($remoteFile);
        $localFile = $this->clear($localFile);
        $mode = intval($mode);
        return @ftp_put($this->_connectId, $remoteFile, $localFile, $mode);
    }

    /**
     * 上传一个已经打开的文件到 FTP 服务器
     * @param string $remoteFile 远程文件
     * @param resource $sourcefp 本地文件打开指针
     * @param integer $mode 传输模式，必须为FTP_BINARY 或 FTP_ASCII
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpFput($remoteFile, $sourcefp, $mode = FTP_BINARY)
    {
        $remoteFile = $this->clear($remoteFile);
        $mode = intval($mode);
        return @ftp_fput($this->_connectId, $remoteFile, $sourcefp, $mode);
    }

    /**
     * 返回指定文件的大小
     * @param string $remoteFile
     * @return integer 获取成功返回文件大小，否则返回 -1。
     */
    private function ftpSize($remoteFile)
    {
        $remoteFile = $this->clear($remoteFile);
        return @ftp_size($this->_connectId, $remoteFile);
    }

    /**
     * 关闭一个 FTP 连接
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpClose()
    {
        return @ftp_close($this->_connectId);
    }

    /**
     * 删除 FTP 服务器上的一个文件
     * @param string $path 要删除的文件路径
     * @return boolean 成功时返回 true， 或者在失败时返回 false
     */
    private function ftpDelete($path)
    {
        $path = $this->clear($path);
        return @ftp_delete($this->_connectId, $path);
    }

    /**
     * 从 FTP 服务器上下载一个文件
     * @param string $localFile 本地文件
     * @param string $remoteFile 远程文件
     * @param string $mode 传输模式
     * @param integer $resumePos 开始传输的位置
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpGet($localFile, $remoteFile, $mode, $resumePos = 0)
    {
        $remoteFile = $this->clear($remoteFile);
        $localFile = $this->clear($localFile);
        $mode = intval($mode);
        $resumePos = intval($resumePos);
        return @ftp_get($this->_connectId, $localFile, $remoteFile, $mode, $resumePos);
    }

    /**
     * 登录 FTP 服务器
     * @param string $userName 用户名
     * @param string $password 密码
     * @return boolean 成功时返回 true， 或者在失败时返回 false
     */
    private function ftpLogin($userName, $password)
    {
        $userName = $this->clear($userName);
        $password = str_replace(array("\n", "\r"), array('', ''), $password);
        return @ftp_login($this->_connectId, $userName, $password);
    }

    /**
     * 返回当前 FTP 被动模式是否打开
     * 如果参数 pasv 为 TRUE，打开被动模式传输 (PASV MODE) ，否则则关闭被动传输模式。
     * @param string $pasv
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpPasv($pasv)
    {
        return @ftp_pasv($this->_connectId, $pasv ? true : false);
    }

    /**
     * 在 FTP 服务器上改变当前目录
     * @param string $directory 目标目录。
     * @return boolean 成功时返回 true， 或者在失败时返回 false
     */
    private function ftpChdir($directory)
    {
        $directory = $this->clear($directory);
        return @ftp_chdir($this->_connectId, $directory);
    }

    /**
     * 向服务器发送 SITE 命令
     * @param string $cmd 命令
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    private function ftpSite($cmd)
    {
        $cmd = $this->clear($cmd);
        return @ftp_site($this->_connectId, $cmd);
    }

    /**
     * Set permissions on a file via FTP
     * @param string $fileName
     * @param integer $mod
     * @return void
     */
    private function ftpChmod($fileName, $mod = 0777)
    {
        $fileName = $this->clear($fileName);
        if (function_exists('ftp_chmod')) {
            return @ftp_chmod($this->_connectId, $mod, $fileName);
        } else {
            return @ftp_site($this->_connectId, 'CHMOD ' . $mod . ' ' . $fileName);
        }
    }

    /**
     * 返回当前目录名
     * @return mixed 返回当前目录名称，发生错误则返回 false。
     */
    private function ftpPwd()
    {
        return @ftp_pwd($this->_connectId);
    }

    /**
     * 设置错误
     * @param integer $code
     */
    private function setError($code = 0)
    {
        $this->_error = $code;
    }

}
