<?php
namespace application\modules\article\actions\index;

use application\core\utils\Ibos;
use application\modules\article\model\ArticleReader;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User;

/*
 * 获得已经阅读新闻的用户
 */

class GetReader extends Base
{

    public function run()
    {
        $data = $_POST;
        $articleid = $data['articleid'];
        $readerData = ArticleReader::model()->fetchAll('articleid=:articleid', array(':articleid' => $articleid));
        $departments = DepartmentUtil::loadDepartment();
        $res = $tempDeptids = $users = array();
        foreach ($readerData as $reader) {
            $user = User::model()->fetchByUid($reader['uid']);
            $users[] = $user;
            $deptid = $user['deptid'];
            $tempDeptids[] = $user['deptid'];
        }
        $deptids = array_unique($tempDeptids);
        foreach ($deptids as $deptid) {
            $deptName = isset($departments[$deptid]['deptname']) ? $departments[$deptid]['deptname'] : '--';
            foreach ($users as $k => $user) {
                if ($user['deptid'] == $deptid) {
                    $res[$deptName][] = $user;
                    unset($users[$k]);
                }
            }
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $res,
        ));
    }
}