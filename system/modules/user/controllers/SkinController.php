<?php

/**
 * user模块皮肤控制器
 * @package application.modules.user.controllers
 * @version $Id: UserSkinController.php 3093 2014-04-10 10:39:51Z gzhzh $
 */

namespace application\modules\user\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\main\components\CommonAttach;
use application\modules\user\model\BgTemplate;
use application\modules\user\model\UserProfile;
use application\modules\user\utils\User;

class SkinController extends HomebaseController
{

    /**
     * 修改背景图
     * @return type
     */
    public function actionCropBg()
    {
        if (Env::submitCheck('bgSubmit') && !empty($_POST['src'])) {
            //图片裁剪数据
            $params = $_POST;   //裁剪参数
            if (!isset($params) && empty($params)) {
                return;
            }
            if (!empty($params['noCrop'])) {
                $base = substr($params['src'], 0, strlen($params['src']) - 10);
                $bgArray = array(
                    'bg_big' => $base . 'bg_big.jpg',
                    'bg_small' => $base . 'bg_small.jpg',
                );
            } else {
                $bgArray = Ibos::engine()->io()->file()->createBg($params['src'], $params);
            }
            $uid = Ibos::app()->user->uid;
            UserProfile::model()->updateAll($bgArray, "uid = {$uid}");
            User::wrapUserInfo($uid, true, true, true);
            Ibos::app()->user->setState('bg_big', $bgArray['bg_big']);
            //Ibos::app()->user->setState( 'bg_middle', $avatarArray['bg_middle']  );
            Ibos::app()->user->setState('bg_small', $bgArray['bg_small']);

            return $this->ajaxReturn(array('isSuccess' => true));
        }
    }

    /**
     * 上传背景图操作
     */
    public function actionUploadBg()
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
            //判断宽和高是否符合头像要求
            if ($tempSize[0] < 1000 || $tempSize[1] < 300) {
                $this->ajaxReturn(array('msg' => Ibos::lang('Bg size error'), 'isSuccess' => false), 'json');
            }
            $this->ajaxReturn(array('data' => $file, 'file' => $fileUrl, 'isSuccess' => true));
        }
    }

    /**
     * 删除模板
     */
    public function actionDelBg()
    {
        $id = intval(Env::getRequest('id'));
        $bg = BgTemplate::model()->findByPk($id);
        if ($bg) {
            $name = $bg->image;
            File::deleteFile($name);
            BgTemplate::model()->deleteByPk($id);
        }
        $this->ajaxReturn(array('isSuccess' => true));
    }

}
