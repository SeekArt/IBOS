<?php

/**
 * 移动端新闻控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端新闻控制器文件
 *
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: settingController.php 2885 2014-03-24 08:19:47Z Aeolus $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\extensions\ThinkImage\ThinkImage;
use application\modules\main\components\CommonAttach;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class SettingController extends BaseController
{

    /**
     * 默认页,获取主页面各项数据统计
     * @return void
     */
    public function actionIndex()
    {

    }

    public function actionUpload()
    {
        $upload = new CommonAttach('avatar');
        $upload->upload();
        if (!$upload->getIsUpoad()) {
            echo "出错了";
        } else {
            $info = $upload->getUpload()->getAttach();
            $upload->updateAttach($info['aid']);
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::fileName($file);
            //$tempSize = File::imageSize( $fileUrl );
            //裁剪并存为3个头像
            //图片裁剪数据
            $uid = Ibos::app()->user->uid;
            //临时头像地址
            $tempAvatar = $file;
            // 存放路径
            $avatarPath = 'data/avatar/';
            // 三种尺寸的地址
            $avatarBig = UserUtil::getAvatar($uid, 'big');
            $avatarMiddle = UserUtil::getAvatar($uid, 'middle');
            $avatarSmall = UserUtil::getAvatar($uid, 'small');
            // 如果是本地环境，先确定文件路径要存在
            if (LOCAL) {
                File::makeDirs($avatarPath . dirname($avatarBig));
            }
            // 先创建空白文件
            File::createFile('data/avatar/' . $avatarBig, '');
            File::createFile('data/avatar/' . $avatarMiddle, '');
            File::createFile('data/avatar/' . $avatarSmall, '');
            // 加载类库
            $imgObj = new ThinkImage(THINKIMAGE_GD);
            $imgTemp = $imgObj->open($tempAvatar);
            //裁剪参数
            $params = array(
                "w" => $imgTemp->width(),
                "h" => $imgTemp->height(),
                "x" => "0",
                "y" => "0"
            );
            //转换一下，得到小于宽高最小值正常比例的图片
            if ($params["w"] > $params["h"]) {
                $params["x"] = ($params["w"] - $params["h"]) / 2;
                $params["w"] = $params["h"];
            } else {
                $params["y"] = ($params["h"] - $params["w"]) / 2;
                $params["h"] = $params["w"];
            }
            //裁剪原图
            $imgObj->open($tempAvatar)->crop($params['w'], $params['h'], $params['x'], $params['y'])->save($tempAvatar);
            //生成缩略图
            $imgObj->open($tempAvatar)->thumb(180, 180, 1)->save($avatarPath . $avatarBig);
            $imgObj->open($tempAvatar)->thumb(60, 60, 1)->save($avatarPath . $avatarMiddle);
            $imgObj->open($tempAvatar)->thumb(30, 30, 1)->save($avatarPath . $avatarSmall);
        }
    }

    public function actionUpdate()
    {
        // 如果不是本人操作，不能进行提交操作
//		if ( $uid !== Ibos::app()->user->uid ) {
//			throw new EnvException( Ibos::lang( 'Parameters error', 'error' ) );
//		}
        // 个人资料提交
        $profileField = array('birthday', 'bio', 'telephone', 'address', 'qq');
        $userField = array('mobile', 'email');
        $model = array();
        // 确定更新所使用MODEL
        foreach ($_POST as $key => $value) {
            if (in_array($key, $profileField)) {
                // 生日字段的转换处理
                if ($key == 'birthday' && !empty($value)) {
                    $value = strtotime($value);
                }
                $model['UserProfile'][$key] = StringUtil::filterCleanHtml($value);
            } else if (in_array($key, $userField)) {
                $model['User'][$key] = StringUtil::filterCleanHtml($value);
            }
        }
        // 更新操作
        foreach ($model as $modelObject => $value) {
            $modelObject::model()->modify(Ibos::app()->user->uid, $value);
        }
        // 更新缓存
        UserUtil::cleanCache(Ibos::app()->user->uid);
//			$this->success( Ibos::lang( 'Save succeed', 'message' ) );
//		echo "<script>parent.settingCallback('图片大小不能超过2M',false)</script>";
        exit();
    }

    public function actionChangePass()
    {
        //$user = User::model()->fetchByUid( $uid );
        $user['salt'] = Ibos::app()->user->salt;
        $user['password'] = Ibos::app()->user->password;

        $oldpass = $_REQUEST["oldpass"];
        $newpass = $_REQUEST["newpass"];
        $repass = $_REQUEST["repass"];

        $update = false;
        if ($oldpass == '') {
            // 没有填写原来的密码
            $errorMsg = Ibos::lang('Original password require');
            $this->ajaxReturn(array('isSuccess' => 'false', 'msg' => $errorMsg), Mobile::dataType());
        } else if (strcasecmp(md5(md5($oldpass) . $user['salt']), $user['password']) !== 0) {
            // 密码跟原来的对不上
            $errorMsg = Ibos::lang('Original password error');
            $this->ajaxReturn(array('isSuccess' => 'false', 'msg' => $errorMsg), Mobile::dataType());
        } else if (!empty($newpass) && strcasecmp($newpass, $repass) !== 0) {
            // 两次密码不一致
            $errorMsg = Ibos::lang('Confirm password is not correct');
            $this->ajaxReturn(array('isSuccess' => 'false', 'msg' => $errorMsg), Mobile::dataType());
        } else {
            $password = md5(md5($newpass) . $user['salt']);
            $update = User::model()->updateByUid(Ibos::app()->user->uid, array('password' => $password));
            $msg = Ibos::lang('Change password succeed');
            $this->ajaxReturn(array('isSuccess' => 'true', 'msg' => $msg, 'login' => "false"), Mobile::dataType());
        }
        exit();
    }

}
