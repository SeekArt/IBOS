<?php

namespace application\modules\main\components;

use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\main\model\Setting as SettingModel;
use CJSON;

/**
 * UEditor编辑器通用上传类
 */
class EditorUploader
{

    private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $type; //文件类型，普通文件还是图片
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param string $type base64编码或者上传类型
     */
    public function __construct($fileField, $config, $type = 'upload')
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->type = isset($this->config['type']) ? $this->config['type'] : '';
        $this->stateInfo = $this->stateMap[0];
        if ($type == "remote") {
            $this->saveRemote();
        } else if ($type == "base64") {
            $this->upBase64();
        } else {
            $this->upFile();
        }
    }

    private function saveRemote()
    {
        return false; // 暂时不支持
    }

    // 暂时不支持saas版
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);
        $this->oriName = "";
        $this->fileSize = strlen($img);
        $this->fileType = '.png';
        $this->fileName = time() . rand(1, 10000) . $this->fileType;
        $this->fullName = $this->getFolder() . '/' . $this->fileName;
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }
        if (!file_put_contents($this->fullName, $img)) {
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
            return;
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        //处理普通上传
        $this->file = $_FILES[$this->fileField];
        if (!$this->file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($this->file['error']);
            return;
        } else if (!file_exists($this->file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return;
        } else if (!is_uploaded_file($this->file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return;
        }

        $this->oriName = $this->file['name'];
        $this->fileSize = $this->file['size'];
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFolder() . '/' . $this->getName();
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }

        $isSuccess = $this->handleUpload();
        if (true === $isSuccess && $this->type == 'image') {
            $this->handleWater();
        }
    }

    private function handleUpload()
    {
        $url = Ibos::engine()->io()->file()->uploadFile($this->fullName, $this->file["tmp_name"]);
        if (false === $url) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
            return false;
        } else {
            $this->fullName = $this->type == 'image' ? File::imageName($this->fullName) : File::fileName($this->fullName);
            return true;
        }
    }

    private function handleWater()
    {
        if ($this->config['water']) {
            $waterModule = CJSON::decode(SettingModel::model()->fetchSettingValueByKey('watermodule'));
            if (in_array('baidu', $waterModule)) {
                $waterConfig = CJSON::decode(SettingModel::model()->fetchSettingValueByKey('waterconfig'));
                if ($waterConfig['watermarktype'] == 'text') {
                    $textConfig = $waterConfig['watermarktext'];
                    $size = ($textConfig['size'] > 0 && $textConfig['size'] <= 48) ? $textConfig['size'] : 16; //文字水印大小限制在1-48
                    $fontPath = !empty($textConfig['fontpath']) ? $textConfig['fontpath'] : 'msyh.ttf'; //字体默认是微软雅黑

                    File::waterString($textConfig['text'], $size, $this->fullName
                        , $this->fullName, $waterConfig['watermarkposition'], $waterConfig['watermarktrans'], $waterConfig['watermarkquality']
                        , $textConfig['color'], $fontPath);
                } else {
                    File::waterPic($this->fullName, $waterConfig['watermarkimg'], $this->fullName
                        , $waterConfig['watermarkposition'], $waterConfig['watermarktrans']
                        , $waterConfig['watermarkquality'], $waterConfig['watermarkminheight'], $waterConfig['watermarkminwidth']);
                }
            }
        }
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "originalName" => $this->oriName,
            "name" => $this->fileName,
            "url" => $this->fullName,
            "size" => $this->fileSize,
            "type" => $this->fileType,
            "state" => $this->stateInfo
        );
    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getName()
    {
        return $this->fileName = time() . rand(1, 10000000) . $this->getFileExt();
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"] * 1024);
    }

    /**
     * 按照日期自动创建存储文件夹
     * @return string
     */
    private function getFolder()
    {
        $pathStr = $this->config["savePath"];
        if (strrchr($pathStr, "/") != "/") {
            $pathStr .= "/";
        }
        $pathStr .= date("Ymd");
        if (LOCAL && !defined('SAE_TMP_PATH')) {
            if (!file_exists($pathStr)) {
                if (!mkdir($pathStr, 0777, true)) {
                    return false;
                }
            }
        }
        return $pathStr;
    }

}
