<?php

namespace application\modules\user\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\modules\main\components\CommonAttach;
use application\modules\message\model\UserData;
use application\modules\user\model\User;
use application\modules\user\model\UserProfile;
use application\modules\user\utils\User as UserUtil;
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
			0 => 'o-pm-online',
			1 => 'o-pm-online',
			2 => 'o-pm-offline',
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
			$avatarArray = IBOS::engine()->io()->file()->createAvatar( $params['src'], $params );
			$uid = IBOS::app()->user->uid;
			UserProfile::model()->updateAll( $avatarArray, "uid = {$uid}" );
			UserUtil::wrapUserInfo( $uid, true, true, true );
			IBOS::app()->user->setState( 'avatar_big', $avatarArray['avatar_big'] );
			IBOS::app()->user->setState( 'avatar_middle', $avatarArray['avatar_middle'] );
			IBOS::app()->user->setState( 'avatar_small', $avatarArray['avatar_small'] );
			return $this->success( IBOS::lang( 'Upload avatar succeed' ), $this->createUrl( 'home/personal', array( 'op' => 'avatar' ) ) );
		}
	}

	/**
	 * 上传头像操作
	 */
	public function actionUploadAvatar() {
		// 获取上传域并上传到临时目录
		$upload = new CommonAttach( 'Filedata' );
		$upload->upload();
		if ( !$upload->getIsUpoad() ) {
			return $this->ajaxReturn( array( 'msg' => IBOS::lang( 'Save failed', 'message' ), 'IsSuccess' => false ) );
		} else {
			$info = $upload->getUpload()->getAttach();
			$file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
			$fileUrl = File::imageName( $file );
			$tempSize = File::imageSize( $fileUrl );
			//判断宽和高是否符合头像要求
			if ( $tempSize[0] < 180 || $tempSize[1] < 180 ) {
				$this->ajaxReturn( array( 'msg' => IBOS::lang( 'Avatar size error' ), 'IsSuccess' => false ), 'json' );
			}
			return $this->ajaxReturn( array( 'data' => $file, 'file' => $fileUrl, 'IsSuccess' => true ) );
		}
	}

}
