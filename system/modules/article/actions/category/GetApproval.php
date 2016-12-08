<?php
namespace application\modules\article\actions\category;

use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\dashboard\model\Approval;

/*
 * 获得所有审批流程接口
 */

class GetApproval extends Base
{

    public function run()
    {
        $approvals = Approval::model()->fetchAllApproval();
        Ibos::app()->controller->ajaxReturn(array('approvals' => $approvals), 'json');
    }
}