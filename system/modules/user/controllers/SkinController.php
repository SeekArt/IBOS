<?php

/**
 * user模块皮肤控制器
 * @package application.modules.user.controllers
 * @version $Id: UserSkinController.php 3093 2014-04-10 10:39:51Z gzhzh $
 */

namespace application\modules\user\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\String;
use application\modules\user\utils\User as UserUtil;
use application\modules\user\model\BgTemplate;
use application\extensions\ThinkImage\ThinkImage;

class SkinController extends HomeBaseController {

    /**
     * 修改背景图
     * @return type
     */
    public function actionCropBg() {
        if ( Env::submitCheck( 'bgSubmit' ) && !empty( $_POST['src'] ) ) {
            //图片裁剪数据
            $params = $_POST;   //裁剪参数
            if ( !isset( $params ) && empty( $params ) ) {
                return;
            }
            //临时头像地址
            $tempBg = $params['src'];
            // 存放路径
            $bgPath = 'data/home/';
            // 三种尺寸的地址
            $bgBig = UserUtil::getBg( $params['uid'], 'big' );
            $bgMiddle = UserUtil::getBg( $params['uid'], 'middle' );
            $bgSmall = UserUtil::getBg( $params['uid'], 'small' );
            // 如果是本地环境，先确定文件路径要存在
            if ( LOCAL ) {
                File::makeDirs( $bgPath . dirname( $bgBig ) );
            }
            // 先创建空白文件
            File::createFile( 'data/home/' . $bgBig, '' );
            File::createFile( 'data/home/' . $bgMiddle, '' );
            File::createFile( 'data/home/' . $bgSmall, '' );
            // 加载类库
            $imgObj = new ThinkImage( THINKIMAGE_GD );
            //裁剪原图(系统的背景图不需要裁剪)
            if ( !isset( $params['noCrop'] ) ) {
                $imgObj->open( $tempBg )->crop( $params['w'], $params['h'], $params['x'], $params['y'], 1000, 300 )->save( $tempBg );
            }
            //生成缩略图
            $imgObj->open( $tempBg )->thumb( 1000, 300, 1 )->save( $bgPath . $bgBig );
            $imgObj->open( $tempBg )->thumb( 520, 156, 1 )->save( $bgPath . $bgMiddle );
            $imgObj->open( $tempBg )->thumb( 400, 120, 1 )->save( $bgPath . $bgSmall );
            // 设置为公用模板
            if ( isset( $params['commonSet'] ) && $params['commonSet'] ) {
                $this->setCommonBg( $bgPath . $bgBig );
            }
            $this->ajaxReturn( array( 'isSuccess' => true ) );
            exit();
        }
    }

    /**
     * 上传背景图操作
     */
    public function actionUploadBg() {
        // 获取上传域并上传到临时目录
        $upload = File::getUpload( $_FILES['Filedata'] );
        if ( !$upload->save() ) {
            $this->ajaxReturn( array( 'msg' => Ibos::lang( 'Save failed', 'message' ), 'isSuccess' => false ) );
        } else {
            $info = $upload->getAttach();
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::fileName( $file );
            $tempSize = File::imageSize( $fileUrl );
            //判断宽和高是否符合头像要求
            if ( $tempSize[0] < 1000 || $tempSize[1] < 300 ) {
                $this->ajaxReturn( array( 'msg' => Ibos::lang( 'Bg size error' ), 'isSuccess' => false ), 'json' );
            }
            $this->ajaxReturn( array( 'data' => $fileUrl, 'file' => $file, 'isSuccess' => true ) );
        }
    }

    /**
     * 删除模板
     */
    public function actionDelBg() {
        $id = intval( Env::getRequest( 'id' ) );
        BgTemplate::model()->deleteByPk( $id );
        $this->ajaxReturn( array( 'isSuccess' => true ) );
    }

    /**
     * 设置公用模板
     * @param string $src 图片路径
     * @return boolean
     */
    private function setCommonBg( $src ) {
        // 存放路径
        $bgPath = 'data/home/';
        // 三种尺寸的地址
        $random = String::random( 16 );
        $bgBig = $random . '_big.jpg';
        $bgMiddle = $random . '_middle.jpg';
        $bgSmall = $random . '_small.jpg';
        // 先创建空白文件
        File::createFile( $bgPath . $bgBig, '' );
        File::createFile( $bgPath . $bgMiddle, '' );
        File::createFile( $bgPath . $bgSmall, '' );
        // 加载类库
        $imgObj = new ThinkImage( THINKIMAGE_GD );
        //生成缩略图
        $imgObj->open( $src )->thumb( 1000, 300, 1 )->save( $bgPath . $bgBig );
        $imgObj->open( $src )->thumb( 520, 156, 1 )->save( $bgPath . $bgMiddle );
        $imgObj->open( $src )->thumb( 400, 120, 1 )->save( $bgPath . $bgSmall );
        // 添加公用模板数据到数据库
        $data = array(
            'desc' => '',
            'status' => 0,
            'system' => 0,
            'image' => $random
        );
        $addRes = BgTemplate::model()->add( $data );
        return $addRes;
    }

}
