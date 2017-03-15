<?php

/**
 * main模块的百度编辑器控制器
 *
 * @version $Id: EditorController.php 2019 2013-12-28 11:36:58Z gzhzh $
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\components\EditorUploader;
use application\modules\main\model\Setting;

Class EditorController extends Controller
{

    public function actionIndex()
    {
        $request = Ibos::app()->request;
        $action = $request->getQuery('action');
        if (!in_array($action, array('uploadimage', 'listimage', 'uploadfile', 'config'))) {
            $action = 'uploadfile';
        }
        return $this->{'action' . ucfirst($action)}();
    }

    /**
     * 百度编辑器图片上传
     */
    public function actionUploadimage()
    {
        $water = Setting::model()->fetchSettingValueByKey('watermarkstatus');
        $configArray = $this->getUeditorConfig();
        //上传配置
        $config = array(
            "savePath" => 'data/editor/image/' . Ibos::app()->user->uid . '/' . ($water ? 'water/' : ''),
            "maxSize" => $configArray['imageMaxSize'],
            "allowFiles" => $configArray['imageAllowFiles'],
            'water' => $water,
            'type' => 'image',
        );
        // 上传图片框中的描述表单名称，
        $title = htmlspecialchars(Env::getRequest('pictitle'), ENT_QUOTES);
        // 生成上传实例对象并完成上传
        $up = new EditorUploader("upfile", $config);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )
         */
        $info = $up->getFileInfo();

        /**
         * 向浏览器返回数据json数据
         * {
         *   'url'      :'a.jpg',   //保存后的文件路径
         *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
         *   'original' :'b.jpg',   //原始文件名
         *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
         * }
         */
        return $this->ajaxReturn(array(
            'url' => $info['url'],
            'title' => $title,
            'original' => $info["originalName"],
            'state' => $info["state"],
        ));
    }

    /**
     * 百度编辑器图片管理
     *
     * @return string 输出图片
     */
    public function actionListimage()
    {
        $path = 'data/editor/image/' . Ibos::app()->user->uid;
        if (!defined('SAE_TMP_PATH')) {
            // 普通环境下
            $files = $this->getfiles($path);
            if (!$files) {
                return;
            }
            rsort($files, SORT_STRING);
            $str = "";
            foreach ($files as $file) {
                $str .= '../../../../../../' . $file . "ue_separate_ue";
            }
            echo $str;
        } else {
            // SAE环境下
            $st = new SaeStorage(); // 实例化
            /*
             *  getList:获取指定domain下的文件名列表
             *  return: 执行成功时返回文件列表数组，否则返回false
             *  参数：存储域，路径前缀，返回条数，起始条数
             */
            $num = 0;
            while ($ret = $st->getList('data', $path, 100, $num)) {
                foreach ($ret as $file) {
                    if (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file)) {
                        echo $st->getUrl('data', $file) . "ue_separate_ue";
                    }
                    $num++;
                }
            }
        }
    }

    /**
     * 遍历获取目录下的指定类型的文件
     *
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles($path, &$files = array())
    {
        if (!is_dir($path)) {
            return null;
        }
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . '/' . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $files);
                } else {
                    if (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file)) {
                        $files[] = $path2;
                    }
                }
            }
        }
        return $files;
    }

    /**
     * 百度编辑器附件上传
     */
    public function actionUploadfile()
    {
        $configArray = $this->getUeditorConfig();
        //上传配置
        $config = array(
            "savePath" => 'data/editor/file/' . Ibos::app()->user->uid . '/', //保存路径
            "allowFiles" => $configArray['fileAllowFiles'],
            "maxSize" => $configArray['fileMaxSize'],
            'type' => 'file'
        );
        //生成上传实例对象并完成上传
        $up = new EditorUploader("upfile", $config);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )
         */
        $info = $up->getFileInfo();

        /**
         * 向浏览器返回数据json数据
         * {
         *   'url'      :'a.rar',        //保存后的文件路径
         *   'fileType' :'.rar',         //文件描述，对图片来说在前端会添加到title属性上
         *   'original' :'编辑器.jpg',   //原始文件名
         *   'state'    :'SUCCESS'       //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
         * }
         */
        echo '{"url":"' . $info["url"] . '","fileType":"' . $info["type"] . '","original":"' . $info["originalName"] . '","state":"' . $info["state"] . '"}';
    }

    public function actionConfig()
    {
        $ueditorConfig = $this->getUeditorConfig();
        return $this->ajaxReturn($ueditorConfig);
    }


    /**
     * 返回百度编辑器配置
     *
     * @return array
     */
    public function getUeditorConfig()
    {
        $ueditorConfig = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "",
            file_get_contents("static/js/lib/ueditor/php/config.json")), true);

        $ueditorConfig = $this->updateAttachMaxSize($ueditorConfig);
        $ueditorConfig = $this->updateAttachFileType($ueditorConfig);

        return $ueditorConfig;
    }

    /**
     * 更新百度编辑器文件大小限制配置
     *
     * @param array $ueditorConfig
     * @return array
     */
    protected function updateAttachMaxSize($ueditorConfig)
    {
        // 单位：MB
        $maxAttachSize = Setting::model()->fetchSettingValueByKey('attachsize');
        if (empty($maxAttachSize)) {
            // 默认文件大小限制为：2MB
            $maxAttachSize = 2;
        }
        // 单位：B
        $maxAttachSize = $maxAttachSize * 1024 * 1024;
        $replaceArr = array_fill_keys(array(
            'imageMaxSize',
            'scrawlMaxSize',
            'catcherMaxSize',
            'videoMaxSize',
            'fileMaxSize',
        ), $maxAttachSize);
        $ueditorConfig = array_merge($ueditorConfig, $replaceArr);

        return $ueditorConfig;
    }

    /**
     * 更新百度编辑器上传文件类型限制配置
     *
     * @param array $ueditorConfig
     * @return array
     */
    protected function updateAttachFileType($ueditorConfig)
    {
        $allowUploadFileType = Setting::model()->fetchSettingValueByKey('filetype');

        // csv, chm, pdf, zip, rar
        // -> array('.csv', '.chm', '.pdf', '.zip', '.rar')
        $allowUploadFileType = explode(',', $allowUploadFileType);
        foreach ($allowUploadFileType as &$fileType) {
            $fileType = sprintf('.%s', trim($fileType));
        }

        $ueditorConfig['fileAllowFiles'] = $allowUploadFileType;

        return $ueditorConfig;
    }

}
