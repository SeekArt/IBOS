<?php

/**
 * SAE 文件处理
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * SAE文件处理类,实现SAE平台文件IO处理
 *
 * @package ext.enginedriver.sae
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: SaeFile.php 1930 2013-12-14 08:05:27Z gzhzh $
 */

namespace application\core\engines\sae;

use application\core\engines\FileOperationInterface;
use application\core\utils\File;
use application\core\utils\Image;
use application\core\utils\StringUtil;
use application\modules\main\model\Attachment;

class SaeFile implements FileOperationInterface
{

    private static $_instance;

    /**
     *
     * @var array
     */
    protected $filesInfo = array();

    /**
     * sae 所用的 domain,默认为 data
     * @var string
     */
    private $_domain;

    /**
     * sae 封装的 storage 对象
     * @var object
     */
    private $_storage;

    /**
     * 基本文件mime类型
     * @var array
     */
    private $_mimeTypes = array(
        //applications
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'exe' => 'application/octet-stream',
        'doc' => 'application/vnd.ms-word',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pps' => 'application/vnd.ms-powerpoint',
        'pdf' => 'application/pdf',
        'xml' => 'application/xml',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'swf' => 'application/x-shockwave-flash',
        // archives
        'gz' => 'application/x-gzip',
        'tgz' => 'application/x-gzip',
        'bz' => 'application/x-bzip2',
        'bz2' => 'application/x-bzip2',
        'tbz' => 'application/x-bzip2',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar',
        'tar' => 'application/x-tar',
        '7z' => 'application/x-7z-compressed',
        // texts
        'txt' => 'text/plain',
        'php' => 'text/x-php',
        'html' => 'text/html',
        'htm' => 'text/html',
        'js' => 'text/javascript',
        'css' => 'text/css',
        'rtf' => 'text/rtf',
        'rtfd' => 'text/rtfd',
        'py' => 'text/x-python',
        'java' => 'text/x-java-source',
        'rb' => 'text/x-ruby',
        'sh' => 'text/x-shellscript',
        'pl' => 'text/x-perl',
        'sql' => 'text/x-sql',
        // images
        'bmp' => 'image/x-ms-bmp',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tga' => 'image/x-targa',
        'psd' => 'image/vnd.adobe.photoshop',
        //audio
        'mp3' => 'audio/mpeg',
        'mid' => 'audio/midi',
        'ogg' => 'audio/ogg',
        'mp4a' => 'audio/mp4',
        'wav' => 'audio/wav',
        'wma' => 'audio/x-ms-wma',
        // video
        'avi' => 'video/x-msvideo',
        'dv' => 'video/x-dv',
        'mp4' => 'video/mp4',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'wm' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'mkv' => 'video/x-matroska'
    );

    /**
     * 构造函数,初始化 domain 以及 storage 对象
     * @param string $domain storage的名称
     * @param string $ak accessKey
     * @param string $sk secretKey
     * @return void
     */
    public function __construct($domain = 'data', $ak = '', $sk = '')
    {
        $this->_domain = $domain;
        $this->_storage = new \SaeStorage($ak, $sk);
    }

    public static function getInstance($domain = 'data', $ak = '', $sk = '')
    {
        if (self::$_instance == null) {
            self::$_instance = new self($domain, $ak, $sk);
        }
        return self::$_instance;
    }

    /**
     * 获取文件列表 默认会将文件做筛选，不显示子目录的文件
     * .txt 表示搜索 所有*.txt
     * / 表示搜索 所有目录 （假设以/结尾则是目录）
     * aaa/ 表示搜索 aaa目录下的文件
     * @param string $dirName
     * @param boolean $showAll
     * @param boolean $showDir
     * @return array
     */
    public function getList($dirName, $showAll = false, $showDir = false)
    {
        if (substr($dirName, 0, 1) == DIRECTORY_SEPARATOR) {
            $dirName = substr($dirName, 1, strlen($dirName));
        }

        $prefix = $dirName;
        $prefix = str_replace(' ', '', $prefix);
        if (empty($prefix)) {
            $prefix = '*';
        }

        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $ls = $s->getList($domain, $prefix);
        if (!empty($ls)) {
            //排除本身
            if (!$showAll) {
                foreach ($ls as $key => $one) {
                    //不显示子目录
                    $tmp = str_replace($ls[0], '', $one);

                    $bo = strpos($tmp, '/');
                    $lenth = strlen($tmp);
                    if ($bo > 1 && $lenth > $bo + 1) {
                        unset($ls[$key]);
                    }
                }
            }
            unset($ls[0]);

            if ($showDir == false) {
                foreach ($ls as $key => $one) {
                    if (substr($one, -1) == DIRECTORY_SEPARATOR) {
                        unset($ls[$key]);
                        continue;
                    }
                }
            }
        }
        return $ls;
    }

    /**
     * 获取指定目录下的所有文件名
     * @param string $dirName 目录名
     * @param boolean $fold 为 true 则不遍历子目录
     * @return array
     *  array(
     * 'dirNum'=>''
     * 'dirNum'=>''
     * 'dirs'=>array()
     * 'files'=>array()
     * );
     */
    public function getFiles($dirName, $fold = true)
    {
        $dirName = $this->formatDir($dirName);
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();

        $ls = $s->getListByPath($domain, $dirName, 1000, 0, $fold);
        $rs = $ls['files'];
        if (!empty($rs)) {
            $arr = explode("/", $dirName);
            $autoSinaName = $dirName . "/" . end($arr);

            foreach ($rs as $key => $tmp) {
                $fullName = $tmp['fullName'];
                if (substr($fullName, -1) == DIRECTORY_SEPARATOR || $fullName == $autoSinaName) {
                    unset($rs[$key]);
                    continue;
                }
                # 和谐掉新浪自创的文件名 - -
                $mine = $this->getMimeType($tmp['Name']);
                if ($tmp['length'] == 26 && empty($mine)) {
                    unset($rs[$key]);
                    continue;
                }

                $rs[$key]['fileName'] = $tmp['fullName'];
            }
        }
        return $rs;
    }

    /**
     * 获得文件夹列表
     * @param string $dirName
     * @param boolean $fold
     * @return array
     */
    public function getDirs($dirName, $fold = true)
    {
        $dirName = $this->formatDir($dirName);
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $ls = $s->getListByPath($domain, $dirName, 1000, 0, $fold);
        $rs = $ls['dirs'];
        if (!empty($rs)) {
            foreach ($rs as $key => $tmp) {
                $rs[$key]['fileName'] = $tmp['fullName'];
            }
        }
        return $rs;
    }

    /**
     * 上传文件
     * @param type $destFileName
     * @param type $srcFileName
     * @param type $attr
     * @return boolean
     */
    public function uploadFile($destFileName, $srcFileName, $attr)
    {
        if (empty($srcFileName)) {
            return false;
        }
        if (empty($attr['type'])) {
            //根据后缀名获得文件类型
            $attr['type'] = $this->getMimeType($destFileName);
        }
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $rs = $s->upload($domain, $destFileName, $srcFileName, $attr);
        return $rs;
    }

    /**
     * 获得文件资料
     * 目前支持的文件属性
     *  - expires: 浏览器缓存超时，功能与Apache的Expires配置相同
     *  - encoding: 设置通过Web直接访问文件时，Header中的Content-Encoding。
     *  - type: 设置通过Web直接访问文件时，Header中的Content-Type。
     * @param string $fileName
     * <code>
     *   $attr = array (
     * 'type'=>'文件类型',
     * 'length'=>文件长度,
     * 'datetime'=>'添加时间'
     * )
     * Array (
     * [fileName] => bbb/222.txt #文件名
     * [length] => 1 #文件长度
     * [datetime] => 1307091828 #添加时间
     * [type] => text/plain #文件类型
     * )
     * </code>
     * @return type
     */
    public function getFileInfo($fileName)
    {
        $info = $this->filesInfo[$fileName];
        if (empty($info)) {
            $domain = $this->getAssetsDomain();
            $s = $this->getDiskStorage();
            $info = $s->getAttr($domain, $fileName);
            if (!empty($info) && empty($info['type'])) {
                $info['type'] = $this->getMimeType($fileName);
            }
            $this->filesInfo[$fileName] = $info;
        }

        return $info;
    }

    /**
     * 简单字符串检测是否目录
     * @param string $dirName
     * @return boolean
     */
    public function isDir($dirName)
    {
        if ($dirName == '' || $dirName == '/') {
            return true;
        }
        if (substr($dirName, -1) == DIRECTORY_SEPARATOR) {
            return true;
        }
        return false;
    }

    /**
     * 创建 storage 目录
     * @param string $dirName 目录名
     * @return boolean
     */
    public function createDir($dirName)
    {
        if (empty($dirName)) {
            return false;
        }
        $attr = array('type' => 'good');
        #目录名加上 / 则为目录
        $fileName = $dirName . DIRECTORY_SEPARATOR;
        $content = 'this is a sae dir and automatic create by ibos2';
        $rs = $this->createFile($fileName, $content, $attr);
        return $rs;
    }

    /**
     * 创建文件
     * @param string $fileName 文件名
     * @param string $content 写入的内容
     * @return type
     */
    public function createFile($file, $content = ' ', $attr = array())
    {
        if (empty($file)) {
            return false;
        }
        if (substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
            $file = substr($file, 1, strlen($file));
        }
        $file = str_replace('//', '/', $file);

        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        if (!isset($attr['type'])) {
            $attr['type'] = $this->getMimeType($file);
        }

        $rs = $s->write($domain, $file, $content, -1, $attr);
        return $rs;
    }

    /**
     * 复制文件
     * @param string $old 旧文件
     * @param string $new 新文件
     * @return boolean
     */
    public function copyFile($old, $new)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        //读取数据
        $attr = $s->getAttr($domain, $old);
        $content = $this->readFile($old);
        $exists = $this->createFile($new, $content, $attr);
        return $exists;
    }

    /**
     * 重命名文件
     * @param string $old 旧文件
     * @param string $new 新文件
     * @return boolean
     */
    public function renameFile($old, $new)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        // 读取数据
        $attr = $s->getAttr($domain, $old);
        $content = $this->readFile($old);
        $exists = $this->createFile($new, $content, $attr);
        if ($exists) {
            $exists = $this->deleteFile($old);
        }
        return $exists;
    }

    /**
     * 重命名文件夹
     * @param string $old 原来的名称
     * @param string $new 新名称
     * @return boolean
     */
    public function renameDir($old, $new)
    {
        $old = $this->formatDir($old);
        $new = $this->formatDir($new);
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $list = $s->getList($domain, $old);
        if (!empty($list)) {
            $length = strlen($old);
            foreach ($list as $oldName) {
                $relName = substr($oldName, $length);
                $newName = $new . $relName;
                $exists = $this->renameFile($oldName, $newName);
            }
        }
        return $exists;
    }

    /**
     * 移动文件
     * @param string $old 旧文件
     * @param string $new 新文件
     * @return type
     */
    public function moveFile($old, $new)
    {
        return $this->renameFile($old, $new);
    }

    /**
     * 读取文件内容
     * @param string $file 文件名
     * @return type
     */
    public function readFile($file)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $content = $s->read($domain, $file);
        return $content;
    }

    /**
     * 文件是否存在在storage中
     * @param string $file 文件名
     * @return boolean
     */
    public function fileExists($file)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $exists = $s->fileExists($domain, $file);
        return $exists;
    }

    /**
     * 文件夹是否存在
     * @param string $dir 文件夹名
     * @return boolean
     */
    public function dirExists($dir = '')
    {
        $dirName = $this->formatDir($dir);
        $dir = $dirName . "/";
        $exists = $this->fileExists($dir);
        return $exists;
    }

    /**
     * 清空指定目录里文件 （不包含子目录）
     * @param string $dir 指定目录
     * @return boolean
     */
    public function clearDir($dir)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $dirName = $this->formatDir($dir);

        $list = $s->getList($domain, $dirName);
        $exists = true;
        if (!empty($list)) {
            foreach ($list as $one) {
                $exists = $this->deleteFile($one);
            }
        }
        return $exists;
    }

    /**
     * 删除文件
     * @param string $file 文件名
     * @return boolean 删除成功与否
     */
    public function deleteFile($file)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $exists = $s->delete($domain, $file);
        return $exists;
    }

    /**
     * 文件夹是否有子目录
     * @param type $dirName
     * @return type
     */
    public function hasChildren($dirName)
    {
        $dirName = $this->formatDir($dirName);
        $s = $this->getDiskStorage();
        $domain = $this->getAssetsDomain();
        $ls = $s->getListByPath($domain, $dirName);
        return $ls['dirNum'] > 0;
    }

    /**
     * 根据文件名获得 mime信息
     * @param type $file
     * @return type
     */
    public function getMimeType($file)
    {
        //获得后缀名
        $hx = '';
        $extend = explode(".", $file);
        if (!empty($extend)) {
            $va = count($extend) - 1;
            $hx = $extend[$va];
        }
        $type = '';
        //根据后缀名获得 mimetype
        if (!empty($this->_mimeTypes[$hx])) {
            $type = $this->_mimeTypes[$hx];
        }
        return $type;
    }

    /**
     * 获得web访问路径
     * @param string $path 要读取的文件名
     * @return string
     */
    public function fileName($path = '', $suffix = false)
    {
        $domain = $this->getAssetsDomain();
        $s = $this->getDiskStorage();
        $url = $s->getUrl($domain, $path);
        $urls = parse_url($url);
        if (!isset($urls['scheme'])) {
            $url = 'http://' . $url;
        }
        $string = '';
        if (true === $suffix) {
            $string = '?' . VERHASH;
        }
        return $url . $string;
    }

    /**
     * 获取错误代码
     * @return integer
     */
    public function errmsg()
    {
        return $this->getDiskStorage()->errmsg();
    }

    /**
     * 格式化目录名
     * @param string $dirName 目录名
     * @return string
     */
    public function formatDir($dirName)
    {
        $dirName = trim($dirName, DIRECTORY_SEPARATOR);
        return $dirName;
    }

    /**
     * 图形规格
     * @param string $image
     * @return array
     */
    public function imageSize($image)
    {
        if (!is_readable($image)) {
            $sufffix = StringUtil::getFileExt($image);
            $image = $this->fetchTemp($image, $sufffix);
        }
        return getimagesize($image);
    }

    /**
     * 获取新浪临时目录路径
     * @return string
     */
    public function getTempPath()
    {
        return SAE_TMP_PATH;
    }

    /**
     * 获取文件大小
     * @param string $file 文件名
     * @return integer
     */
    public function fileSize($file)
    {
        if (!is_readable($file)) {
            $file = $this->fetchTemp($file);
        }
        return sprintf('%u', filesize($file));
    }

    /**
     * 读取一个网络文件到临时目录并生成临时文件
     *
     * @param string $file 要读取的文件URL
     * @param string $suffix 生成临时文件的后缀，为空则自动读取
     * @return string 临时文件地址
     */
    public function fetchTemp($file, $suffix = '')
    {
        if (empty($suffix)) {
            $suffix = pathinfo($file, PATHINFO_EXTENSION);
        }
        $tmp = SAE_TMP_PATH . 'tmp.' . $suffix;
        $fetch = new SaeFetchurl();
        $fileContent = $fetch->fetch($file);
        file_put_contents($tmp, $fileContent);
        return $tmp;
    }

    public function download($attach, $downloadInfo)
    {
        $attachUrl = File::getAttachUrl();
        $attachment = File::fileName($attachUrl . '/' . $attach['attachment']);
        Attachment::model()->updateDownload($attach['aid']);
        header("Location:{$attachment}");
    }

    /**
     * 获得domain
     * @return string
     */
    private function getAssetsDomain()
    {
        return $this->_domain;
    }

    /**
     * 获得 STORAGE 对象
     * @return object
     */
    private function getDiskStorage()
    {
        return $this->_storage;
    }

    public function thumbnail($fromFileName, $toFileName, $thumbWidth = 96, $thumbHeight = 96)
    {
        return Image::thumb($fromFileName, $toFileName, $thumbWidth, $thumbHeight);
    }

}
