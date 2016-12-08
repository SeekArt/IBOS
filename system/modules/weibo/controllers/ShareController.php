<?php

namespace application\modules\weibo\controllers;

use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\core\model\Source;

class ShareController extends BaseController
{

    public function actionIndex()
    {
        $shareInfo['sid'] = intval(Env::getRequest('sid'));
        $shareInfo['stable'] = StringUtil::filterCleanHtml(Env::getRequest('stable'));
        $shareInfo['initHTML'] = StringUtil::filterDangerTag(Env::getRequest('initHTML'));
        $shareInfo['curid'] = StringUtil::filterCleanHtml(Env::getRequest('curid'));
        $shareInfo['curtable'] = StringUtil::filterCleanHtml(Env::getRequest('curtable'));
        $shareInfo['module'] = StringUtil::filterCleanHtml(Env::getRequest('module'));
        $shareInfo['isrepost'] = intval(Env::getRequest('isrepost'));
        if (empty($shareInfo['stable']) || empty($shareInfo['sid'])) {
            echo '类型和资源ID不能为空';
            exit();
        }
        if (!$oldInfo = Source::getSourceInfo($shareInfo['stable'], $shareInfo['sid'], false, $shareInfo['module'])) {
            echo '此信息不可以被转发';
            exit();
        }
        empty($shareInfo['module']) && $shareInfo['module'] = $oldInfo['module'];
        if (empty($shareInfo['initHTML']) && !empty($shareInfo['curid'])) {
            //判断是否为转发的微博
            if ($shareInfo['curid'] != $shareInfo['sid'] && $shareInfo['isrepost'] == 1) {
//				$module = $curtable == $shareInfo['stable'] ? $shareInfo['module'] : 'weibo';
                $curInfo = Source::getSourceInfo($shareInfo['curtable'], $shareInfo['curid'], false, 'weibo');
                $userInfo = $curInfo['source_user_info'];
                // if($userInfo['uid'] != $this->uid){	//分享其他人的分享，非自己的
                $shareInfo['initHTML'] = ' //@' . $userInfo['realname'] . '：' . $curInfo['source_content'];
                // }
                $shareInfo['initHTML'] = str_replace(array("\n", "\r"), array('', ''), $shareInfo['initHTML']);
            }
        }
        $shareInfo['shareHtml'] = !empty($oldInfo['shareHtml']) ? $oldInfo['shareHtml'] : '';

        $shareInfo['initHTML'] = StringUtil::imgToExpression($shareInfo['initHTML']);
        $data = array(
            'shareInfo' => $shareInfo,
            'oldInfo' => $oldInfo
        );
        $this->renderPartial('index', $data);
    }

}
