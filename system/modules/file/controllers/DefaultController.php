<?php

/**
 * 文件柜模块------ 文件柜首页控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承FileBaseController
 * @package application.modules.file.controllers
 * @version $Id: DefaultController.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\file\model\File;
use application\modules\file\model\FileDynamic;
use application\modules\file\model\FileShare;
use application\modules\file\utils\FileData;
use application\modules\user\model\User;

class DefaultController extends BaseController
{

    /**
     * 文件柜首页
     */
    public function actionIndex()
    {
        $this->setPageTitle(Ibos::lang('Folder page'));
        $userSize = FileData::getUserSize($this->uid) . 'm'; // 单位M
        $params = array(
            'userSize' => implode('', StringUtil::ConvertBytes($userSize)), // 用户容量
            'usedSize' => File::model()->getUsedSize($this->uid, $this->cloudid), // 已用容量
            'hasNewShare' => FileShare::model()->chkHasNewShare($this->uid)  //是否有新共享
        );
        $this->render('index', $params);
    }

    /**
     * 获取动态
     */
    public function actionGetDynamic()
    {
        $offset = intval(Env::getRequest('offset'));
        if ($offset < 0) {
            $offset = 0;
        }
        $dynamic = FileDynamic::model()->fetchDynamic($this->uid, $offset);
        $left = FileDynamic::model()->fetchDynamic($this->uid, $offset + FileDynamic::LIMIT); // 剩余的动态
        $params = array(
            'datas' => $this->handleDynamic($dynamic),
            'offset' => $offset,
            'remind' => count($left)
        );
        $this->ajaxReturn($params);
    }

    /**
     * 处理动态显示数据
     * @param array $dynamic 动态数据
     * @return array
     */
    private function handleDynamic($dynamic)
    {
        foreach ($dynamic as &$d) {
            $user = User::model()->fetchByUid($d['uid']);
            $d['avatar'] = $user['avatar_middle'];
            $d['content'] = Ibos::lang('Realname', '', array('{realname}' => $user['realname'])) . $d['content'];
        }
        return $dynamic;
    }

}
