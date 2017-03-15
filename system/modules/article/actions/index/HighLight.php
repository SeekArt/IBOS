<?php
namespace application\modules\article\actions\index;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\model\Article;
use application\modules\article\utils\Article as ArticleUtil;

/*
 * 高亮新闻
 */

class HighLight extends Base
{

    public function run()
    {
        $data = $_POST;
        $articleids = trim($data['articleids'], ',');
        if (empty($articleids)){
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Select one'),
                'data' => '',
            ));
        }
        $highLight = array();
        $highLight['endTime'] = $data['highlightEndTime'];
        $highLight['bold'] = $data['highlight_bold'];
        $highLight['color'] = $data['highlight_color'];
        $highLight['italic'] = $data['highlight_italic'];
        $highLight['underline'] = $data['highlight_underline'];
        $filter = ArticleUtil::processHighLightRequestData($highLight);
        if (empty($filter['highlightendtime']) || $filter['highlightendtime'] <= TIMESTAMP) {
            Article::model()->updateHighlightStatus($articleids, 0, '', '');
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Unhighlighting success'),
                'data' => '',
            ));
        } else {
            Article::model()->updateHighlightStatus($articleids, 1, $filter['highlightstyle'],
                $filter['highlightendtime']);
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Highlight succeed'),
                'data' => ''
            ));
        }
    }
}