<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Image;
use application\core\utils\StringUtil;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\components\CommonAttach;
use application\modules\main\model\MenuCommon;
use CHtml;

/**
 * 后台模块 快捷导航设置控制器文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */

/**
 * 后台快捷导航设置控制器
 *
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: QuicknavController.php 2052 2014-04-24 10:05:11Z gzhzh $
 */
class QuicknavController extends BaseController
{

    // icon路径
    private $_iconPath = 'data/icon/';
    private $_iconTempPath = 'data/icon/temp/';

    const TTF_FONT_File = 'data/font/msyh.ttf'; // 雅黑字体路径

    public function actionIndex()
    {
        $menus = MenuCommon::model()->fetchAll(array('order' => 'sort ASC'));
        foreach ($menus as $k => $menu) {
            if ($menu['iscustom']) {
                $menus[$k]['icon'] = $menu['icon'];
            } else {
                $menus[$k]['icon'] = Ibos::app()->assetManager->getAssetsUrl($menu['module']) . '/image/icon.png';
            }
        }
        $this->render('index', array('menus' => $menus));
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $id = intval(Env::getRequest('id'));
            MenuCommon::model()->deleteByPk($id);
            $this->ajaxReturn(array('isSuccess' => true));
        }
    }

    /**
     * 添加快捷导航
     */
    public function actionAdd()
    {
        if (Env::getRequest('formhash')) {
            $data = $this->beforeSave();
            // 查询出最大的sort
            $cond = array('select' => 'sort', 'order' => "`sort` DESC");
            $sortRecord = MenuCommon::model()->fetch($cond);
            if (empty($sortRecord)) {
                $sortId = 0;
            } else {
                $sortId = $sortRecord['sort'];
            }
            // 排序号默认在最大的基础上加1
            $data['name'] = CHtml::encode($data['name']);
            $data['url'] = urlencode($data['url']);
            $data['sort'] = $sortId + 1;
            $data['module'] = '';
            $data['iscommon'] = 0;
            $data['iscustom'] = 1;
            $data['disabled'] = 0;
            $data['openway'] = 0;
            MenuCommon::model()->add($data);
            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('quicknav/index'));
        } else {
            $this->render('add');
        }
    }

    /**
     * 添加提交数据前处理
     * @return array
     */
    protected function beforeSave()
    {
        $name = StringUtil::filterStr(Env::getRequest('name'));
        $url = StringUtil::filterStr(Env::getRequest('url'));
        $icon = StringUtil::filterStr(Env::getRequest('quicknavimg'));
        // 生成文件夹
        if (LOCAL) {
            File::makeDirs($this->_iconPath);
        }
        $saveName = StringUtil::random(16) . '.png'; // 生成的图片名
        if (!empty($icon)) {
            // 有传递图片
            $this->createImgIcon($icon, $saveName);
        } else {
            // 纯色图片
            $val = Env::getRequest('fontvalue');  // 写入图片文字
            $this->createColorImg($saveName, $val);
        }

        $data = array(
            'name' => $name,
            'url' => $url,
            'description' => '',
            'icon' => File::imageName($this->_iconPath . $saveName)
        );
        return $data;
    }

    /**
     * 编辑快捷导航
     */
    public function actionEdit()
    {
        if (Env::getRequest('formhash')) {
            $id = intval(Env::getRequest('id'));
            $name = StringUtil::filterStr(Env::getRequest('name'));
            $url = StringUtil::filterStr(Env::getRequest('url'));
            $icon = StringUtil::filterStr(Env::getRequest('quicknavimg'));
            if (!empty($icon)) {
                File::copyToDir($icon, $this->_iconPath);
                $info = pathinfo($icon);
                $saveName = $info['basename'];
            } else {
                $saveName = StringUtil::random(16) . '.png';
                $val = Env::getRequest('fontvalue');  // 写入图片文字
                $this->createColorImg($saveName, $val);
            }
            $data = array(
                'name' => $name,
                'url' => $url,
                'description' => '',
                'icon' => File::imageName($this->_iconPath . $saveName),
            );
            MenuCommon::model()->modify($id, $data);
            $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('quicknav/index'));
        } else {
            $op = Env::getRequest('op');
            if (empty($op)) {
                $id = intval(Env::getRequest('id'));
                $menu = MenuCommon::model()->fetchByPk($id);
                if (empty($menu)) {
                    $this->error(Ibos::lang('Quicknav not fount tip'), $this->createUrl('quicknav/index'));
                }
                $menu['icon'] = File::imageName($menu['icon']);
                $this->render('edit', array('menu' => $menu));
            } else {
                $this->$op();
            }
        }
    }

    /**
     * 创建纯色icon图片
     * @param string $saveName 输出icon名称
     * @param string $val 要写入icon的文字
     * @param integer $fontsize 字体大小
     */
    private function createColorImg($saveName, $val, $fontsize = 15)
    {
        // 根据上传的16进制色获取相应色板
        $hexColor = Env::getRequest('quicknavcolor');
        $tempFile = $this->getTempByHex($hexColor);
        if (!$tempFile) {
            $this->error(Ibos::lang('Quicknav add faild'), $this->createUrl('quicknav/index'));
        }
        $outputFile = $this->_iconPath . $saveName;
        // 生成水印
        File::waterString($val, $fontsize, $tempFile, $outputFile, 5, 100, 100, '#FFFFFF', self::TTF_FONT_File);
        return true;
    }

    /**
     * 保存自定义上传图片
     * @param string $tempFile 文件地址
     * @param string $outputName 保存的icon名称
     */
    private function createImgIcon($tempFile, $outputName)
    {
        // 生成圆角
        $outputFile = $this->_iconPath . $outputName;
        File::copyFile($tempFile, $outputFile, true);
        return true;
    }

    /**
     * 上传临时图片
     */
    public function actionUploadIcon()
    {
        // 获取上传域并上传到临时目录
        $upload = new CommonAttach('Filedata');
        $upload->upload();
        if (!$upload->getIsUpoad()) {
            $this->ajaxReturn(array('msg' => Ibos::lang('Save failed', 'message'), 'isSuccess' => false));
        } else {
            $info = $upload->getUpload()->getAttach();
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::imageName($file);
            $tempSize = File::imageSize($fileUrl);
            //判断宽和高是否符合要求
            if ($tempSize[0] < 64 || $tempSize[1] < 64) {
                $this->ajaxReturn(array('msg' => Ibos::lang('Icon size error'), 'isSuccess' => false));
            }
            $this->ajaxReturn(array(
                'path' => $file,
                'imgurl' => $fileUrl,
                'aid' => $info['aid'],
                'name' => $info['name'],
                'isSuccess' => true));
        }
    }

    /**
     * 更改启用状态
     */
    private function changeEnabled()
    {
        $id = intval(Env::getRequest('id'));
        $type = StringUtil::filterStr(Env::getRequest('type'));
        if ($type == 'disabled') {
            $disabled = 1;
        } else {
            $disabled = 0;
        }
        MenuCommon::model()->modify($id, array('disabled' => $disabled));
        $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
    }

    /**
     * 更改打开方式，新窗口打开还是原窗口打开
     */
    private function changeOpenWay()
    {
        $id = intval(Env::getRequest('id'));
        $type = StringUtil::filterStr(Env::getRequest('type'));
        if ($type == 'disabled') {
            $openway = 1;
        } else {
            $openway = 0;
        }
        MenuCommon::model()->modify($id, array('openway' => $openway));
        $this->ajaxReturn(array('openway' => $openway, 'isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
    }

    /**
     * 通过16进制色获得icon色彩模板
     * @param type $hex
     * @return type
     */
    protected function getTempByHex($hex)
    {
        $res = false;
        // TODO:差个紫色
        $allTemp = array(
            '#E47E61' => 'red.png',
            '#F09816' => 'orange.png',
            '#D29A63' => 'yellow.png',
            '#7BBF00' => 'green.png',
            '#3497DB' => 'blue.png',
            '#82939E' => 'gray.png',
            '#8EABCD' => 'inky.png',
            '#AD85CC' => 'purple.png',
            '#58585C' => 'black.png'
        );
        if (in_array($hex, array_keys($allTemp))) {
            //不管是saas还是本地，都必须是判断本地的
            $file = $this->_iconTempPath . $allTemp[$hex];
            if (file_exists($file)) {
                $res = $file;
            }
        }
        return $res;
    }

}
