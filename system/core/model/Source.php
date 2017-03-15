<?php

namespace application\core\model;

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\message\model\Comment;
use application\modules\message\model\Feed;
use application\modules\user\model\User;

class Source
{

    /**
     * 获取数据源
     * @param string $table 数据源的表
     * @param integer $rowId 数据源记录的ID
     * @param boolean $forApi 是否用于API获取
     * @param strign $moduleName 模块名
     * @return array 数据源信息
     */
    public static function getSourceInfo($table, $rowId, $forApi = false, $moduleName = 'weibo')
    {
        static $_forApi = '0';
        $_forApi == '0' && $_forApi = intval($forApi);
        $key = $_forApi ? $table . $rowId . '_api' : $table . $rowId;
        $info = Cache::get('source_info_' . $key);
        if ($info) {
            return $info;
        }
        switch ($table) {
            case 'feed' :
                $info = self::getInfoFromFeed($table, $rowId, $_forApi);
                break;
            case 'comment' :
                $info = self::getInfoFromComment($table, $rowId, $_forApi);
                break;
            default:
                // 单独的内容，通过此路径获取资源信息
                $table = ucfirst($table);
                $tableAlia = 'application\modules\\' . $moduleName . '\model\\' . $table;
                $model = $tableAlia::model();
                if (method_exists($model, 'getSourceInfo')) {
                    $info = $model->getSourceInfo($rowId, $_forApi);
                }
                unset($model);
                break;
        }
        $info['source_table'] = $table;
        $info['source_id'] = $rowId;
        Cache::set('source_info_' . $key, $info);
        return $info;
    }

    /**
     * 从动态中提取资源数据
     * @param type $table
     * @param type $rowId
     * @param type $forApi
     * @return type
     */
    private static function getInfoFromFeed($table, $rowId, $forApi)
    {
        $info = Feed::model()->getFeedInfo($rowId, $forApi);
        $info['source_user_info'] = User::model()->fetchByUid($info['uid']);
        $info['source_user'] = $info['uid'] == Ibos::app()->user->uid ? Ibos::lang('Me', 'message.default') : '<a href="' . $info['source_user_info']['space_url'] . '" class="anchor" target="_blank">' . $info['source_user_info']['realname'] . '</a>'; // 我
        $info['source_type'] = '微博';
        $info['source_title'] = ''; //$forApi ? StringUtil::parseForApi( $info['user_info']['space_url'] ) : $info['user_info'] ['space_url']; // 微博title暂时为空
        $info['source_url'] = Ibos::app()->urlManager->createUrl('weibo/personal/feed', array(
            'feedid' => $rowId,
            'uid' => $info ['uid']
        ));;
        $info['source_content'] = StringUtil::parseHtml($info['content']);
        $info['ctime'] = $info['ctime'];
//		unset( $info['content'] );
        return $info;
    }

    /**
     * 从动态评论中提取资源数据
     *
     * @param integer $rowId 资源ID
     * @param boolean $forApi 是否提供API，默认为false
     * @return array 格式化后的资源数据
     */
    private static function getInfoFromComment($table, $rowId, $forApi)
    {
        $_info = Comment::model()->getCommentInfo($rowId, true);
        $info['uid'] = $_info['moduleuid'];
        $info['rowid'] = $_info['rowid'];
        $info['source_user'] = $info ['uid'] == Ibos::app()->user->uid ? Ibos::lang('Me', 'message.default') : $_info['user_info'] ['space_url']; // 我
        $info['comment_user_info'] = User::model()->fetchByUid($_info['user_info']['uid']);
        $forApi && $info ['source_user'] = StringUtil::parseForApi($info['source_user']);
        $info['source_user_info'] = User::model()->fetchByUid($info['uid']);
        $info['source_type'] = Ibos::lang('Comment', 'message.default'); // 评论
        $info['source_content'] = $forApi ? parseForApi($_info ['content']) : $_info['content'];
        $info['source_url'] = $_info['sourceInfo']['source_url'];
        $info['ctime'] = $_info['ctime'];
        $info['module'] = $_info['module'];
        $info['sourceInfo'] = $_info['sourceInfo'];
        // 微博title暂时为空
        $info['source_title'] = $forApi ? StringUtil::parseForApi($_info['user_info']['space_url']) : $_info['user_info']['space_url'];
        return $info;
    }

    /**
     *
     * @param type $data
     * @param type $forApi
     * @return type
     */
    public static function getCommentSource($data, $forApi = false)
    {
        if ($data['table'] == 'feed' || $data['table'] == 'comment' || $forApi) {
            $info = self::getSourceInfo($data['table'], $data['rowid'], $forApi, $data['module']);
            return $info;
        }
        $info['source_user_info'] = User::model()->fetchByUid($data['moduleuid']);
        $info['source_url'] = $data['url']; //$data['module_detail_url'];
        $info['source_content'] = isset($data['content']) ? $data['content'] : ''; //$data['module_detail_summary'];
        return $info;
    }

}
