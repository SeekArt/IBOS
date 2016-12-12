<?php
namespace application\modules\article\actions\data;

use application\core\utils\Attach;
use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;

/*
 *新闻预览接口
 */

class Preview extends Base
{

    public function run()
    {

        $data = $_POST;
        $type = $data['type'];
        $subject = $data['subject'];
        if (!isset($type) || !isset($subject)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('No type or no subject'),
                'data' => '',
            ));
        }
        $output['type'] = $type;
        $output['subject'] = $subject;
        if ($type == self::ARTICLE_TYPE_PICTURE) {//图片类型
            $picids = $data['picids'];
            $pictureData = Attach::getAttachData($picids, false);
            $output['pictureData'] = array_values($pictureData);
        } else {
            $output['content'] = $data['content'];
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $output,
        ));

    }
}