<?php

namespace application\modules\user\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\File;
use application\modules\user\utils\User as UserUtil;
use application\modules\user\model\User;
use application\modules\message\model\UserData;
use application\modules\weibo\model\Follow;

class InfoController extends Controller {

    /**
     * 用户资料卡
     * @return string
     */
    public function actionUserCard() {
        $uid = Env::getRequest( 'uid' );
        $user = User::model()->fetchByUid( $uid );
        $onlineStatus = UserUtil::getOnlineStatus( $uid );
        $styleMap = array(
            -1 => 'o-pm-offline',
            1 => 'o-pm-online'
        );
        if ( empty( $user ) ) {
            $this->error( IBOS::lang( 'Request tainting', 'error' ) );
        } else {
            $weiboExists = Module::getIsEnabled( 'weibo' );
            $data = array(
                'user' => $user,
                'status' => $styleMap[$onlineStatus],
                'lang' => IBOS::getLangSources(),
                'weibo' => $weiboExists,
            );
            if ( $weiboExists ) {
                $data['userData'] = UserData::model()->getUserData( $user['uid'] );
                $data['states'] = Follow::model()->getFollowState( IBOS::app()->user->uid, $user['uid'] );
            }
            $content = $this->renderPartial( 'userCard', $data, true );
            echo $content;
            exit();
        }
    }

    /**
     * 裁剪头像操作
     * @return type
     */
    public function actionCropImg() {
        if ( Env::submitCheck( 'userSubmit' ) ) {
            set_time_limit( 120 );
            //图片裁剪数据
            $params = $_POST;   //裁剪参数
            if ( !isset( $params ) && empty( $params ) ) {
                return;
            }
            //临时头像地址
            $tempAvatar = $params['src'];
            // 存放路径
            $avatarPath = 'data/avatar/';
            // 三种尺寸的地址
            $avatarBig = UserUtil::getAvatar( $params['uid'], 'big' );
            $avatarMiddle = UserUtil::getAvatar( $params['uid'], 'middle' );
            $avatarSmall = UserUtil::getAvatar( $params['uid'], 'small' );
            $random = rand( 1000, 9999 );
            IBOS::app()->user->setState( 'avatar_big', $avatarPath . $avatarBig . '?' . $random );
            IBOS::app()->user->setState( 'avatar_middle', $avatarPath . $avatarMiddle . '?' . $random );
            IBOS::app()->user->setState( 'avatar_small', $avatarPath . $avatarSmall . '?' . $random );
            // 如果是本地环境，先确定文件路径要存在
            if ( LOCAL ) {
                File::makeDirs( $avatarPath . dirname( $avatarBig ) );
            }
            // 先创建空白文件
            File::createFile( $avatarPath . $avatarBig, '' );
            File::createFile( $avatarPath . $avatarMiddle, '' );
            File::createFile( $avatarPath . $avatarSmall, '' );
            // 加载类库
            IBOS::import( 'ext.ThinkImage.ThinkImage', true );
            $imgObj = new \application\extensions\ThinkImage\ThinkImage( THINKIMAGE_GD );
            //裁剪原图
            $imgObj->open( $tempAvatar )->crop( $params['w'], $params['h'], $params['x'], $params['y'] )->save( $tempAvatar );
            //生成缩略图
            $imgObj->open( $tempAvatar )->thumb( 180, 180, 1 )->save( $avatarPath . $avatarBig );
            $imgObj->open( $tempAvatar )->thumb( 60, 60, 1 )->save( $avatarPath . $avatarMiddle );
            $imgObj->open( $tempAvatar )->thumb( 30, 30, 1 )->save( $avatarPath . $avatarSmall );

            $this->success( IBOS::lang( 'Upload avatar succeed' ), $this->createUrl( 'home/personal', array( 'op' => 'avatar' ) ) );
            exit();
        }
    }

    /**
     * 上传头像操作
     */
    public function actionUploadAvatar() {
        // 获取上传域并上传到临时目录
        $upload = File::getUpload( $_FILES['Filedata'] );
        if ( !$upload->save() ) {
            $this->ajaxReturn( array( 'msg' => IBOS::lang( 'Save failed', 'message' ), 'IsSuccess' => false ) );
        } else {
            $info = $upload->getAttach();
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::fileName( $file );
            $tempSize = File::imageSize( $fileUrl );
            //判断宽和高是否符合头像要求
            if ( $tempSize[0] < 180 || $tempSize[1] < 180 ) {
                $this->ajaxReturn( array( 'msg' => IBOS::lang( 'Avatar size error' ), 'IsSuccess' => false ), 'json' );
            }
            $this->ajaxReturn( array( 'data' => $fileUrl, 'file' => $file, 'IsSuccess' => true ) );
        }
    }

}
