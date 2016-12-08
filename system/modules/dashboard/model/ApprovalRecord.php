<?php
namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\ArrayUtil;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\user\model\User;

class ApprovalRecord extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{approval_record}}';
    }

    /*
     * 获取审核数据的最后一步
     * @param integer $relateid 模型关联ID
     */
    public function fetchLastStep($relateid)
    {
        $module  = $this->getCurrentModule();
        $record = $this->fetch(array(
            'condition' => "module = '{$module}' AND relateid = {$relateid}",
            'order' => 'time DESC',
        ));
        return $record;
    }

    /*
     * 记录签收步骤
     * @param integer $relateid 关联模型id
     * @param integer $uid 审核uid
     * @parma integer $status 审核状态
     * @param string $reason 审核原因，通过可以不填，退回一定要填
     */
    public function recordStep($relateid, $uid, $status, $reason="")
    {
        $module = $this->getCurrentModule();
        $lastApproval = $this->fetchLastStep($relateid);
        if (empty($lastApproval)|| $status == 3) {
            $step = 0;
        } else {
            $step = $lastApproval['step'] + 1;
        }
        Ibos::app()->db->createCommand()->insert($this->tableName(),array(
            'module' => "{$module}",
            'relateid' => $relateid,
            'uid' => $uid,
            'step' => $step,
            'time' => TIMESTAMP,
            'status' => $status,
            'reason' => $reason,
        ));
    }

    /*
     * 获得关联模型ID的待审核
     * @param integer $relateid 关联模型id
     * @param integer $aid  审核表ID
     * @return array 以未审核步骤为键，uids为值，如果没有直接返回空的数组
     */
    public function getNotAllow( $relateid, $aid)
    {
        $array = array();
        $approval = Approval::model()->fetchByPk($aid);
        $level = $approval['level'];
        $lastApproval = $this->fetchLastStep($relateid);
        if ($lastApproval['step'] < $level){//流程还没有结束
            $restStep = $lastApproval['step'] + 1;//下一个步骤
            for ($i=$restStep; $i<=$level; $i++){
                $array[$i] = ApprovalStep::model()->getApprovalerStr($aid, $i);
            }
        }
        return $array;
    }

    /*
     * 查询当前模型中关联ID的审核步骤
     * @param integer $relateid 关联ID
     */
    public function flowLog($relateid)
    {
        $module = $this->getCurrentModule();
        $flowLog =  $this->fetchAll(array(
            'condition' => "module = '{$module}' AND relateid = {$relateid}",
            'order' => 'time ASC',
        ));
        return $flowLog;
    }

    /*
     * 获得当前模型中关联ID的所有审核步骤，包括通过，退回状态的数据
     * @param integer $relateid 关联ID
     * @return array 格式为审核用户真实名，审核状态，原因，时间
     */
    public function getFlowLog($relateid)
    {
        $module = $this->getCurrentModule();
        $flowLog =  $this->fetchAll(array(
            'condition' => "module = '{$module}' AND relateid = {$relateid} AND status != 3",
            'order' => 'time ASC',
        ));
        $record = array();
        for ($i=0;$i<count($flowLog);$i++){
            $username = User::model()->fetchRealnameByUid($flowLog[$i]['uid']);
            $status = $flowLog[$i]['status'];
            if ($status == 3){
                $msg = Ibos::lang('Publish');
            } elseif ($status == 1 || $status == 2){
                $msg = Ibos::lang('Pass');
            } elseif ($status == 0){
                $msg = Ibos::lang('Back');
            }
            $record[] = array(
                'author' => $username,
                'status' => $msg,
                'reason' => $flowLog[$i]['reason'],
                'time' => date('Y-m-d h:i:s',$flowLog[$i]['time']),
            );
        }
        return $record;
    }

    /**
     * 返回当前模块（如，article）
     * @return array
     */
    public function getCurrentModule()
    {
        $correctModuleName = Ibos::app()->setting->get('correctModuleName');
        if (empty($correctModuleName)) {
            $correctModuleName = Ibos::getCurrentModuleName();
        }
        return $correctModuleName;
    }

    /*
     * 得到最后退回的步骤,以step为键，真实名为值
     * @param integer $relateid 关联ID
     */
    public function getLastBack($relateid)
    {
        $module = $this->getCurrentModule();
        $back = $this->fetch(array(
            'condition' => "module = '{$module}' AND relateid = {$relateid} AND status = 0",
            'order' => 'time DESC',
        ));
        return $back;
    }

    /*
     * 删除关联模型对应关联id的所有审核记录
     * @param integer $relateid 关联ID
     */
    public function deleteByRelateid($relateid){
        $module = $this->getCurrentModule();
        $this->deleteAll("module = :module AND relateid IN ($relateid)", array(':module' => $module));
    }

    /*
     * 关联ID得到已经通过的uid
     * @param integer $relateid 关联ID
     */
    public function getPassedUid($relateid){
        $module = $this->getCurrentModule();
        $result = $this->fetchAll('module = :module AND relateid = :relateid AND status = 1', array(
            ':module' => $module,
            'relateid' => $relateid,
        ));
        $passedUid = ArrayUtil::getColumn($result, 'uid');
        return $passedUid;
    }

    /*
     * 删除所有审核数据
     * @param integer $relateid
     */
    public function deleteApproval($relateid)
    {
        $module = $this->getCurrentModule();
        $this->deleteAll('module = :module AND relateid = :relateid', array(
            ':module' => $module,
            ':relateid' => $relateid,
        ));
    }

    //获得已经走完审核流程的关联id
    public function fetchAllGroupByArtId()
    {
        $module = $this->getCurrentModule();
        $result = array();
        $records = $this->fetchAll("step > 0 AND module = '{$module}' AND status != 0");
        if (!empty($records)) {
            foreach ($records as $record) {
                $artId = $record['relateid'];
                $result[$artId][] = $record;
            }
        }
        return $result;
    }

    //获得所有被退回的ID
    public function fetchAllBackArtId()
    {
        $module = $this->getCurrentModule();
        $record = $this->fetchAll("module = '{$module}' AND status = 0");
        return Convert::getSubByKey($record, 'relateid');
    }
}
