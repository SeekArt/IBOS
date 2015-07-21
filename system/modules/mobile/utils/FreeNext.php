<?php

/**
 * ICWfShowNextFree class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 自由流程下一步显示与处理挂件
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.workflow.widgets
 * $Id$
 */

namespace application\modules\mobile\utils;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\message\model\Notify;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\workflow\core\FlowType as ICFlowType;
use application\modules\workflow\model\FlowRun;
use application\modules\workflow\model\FlowRunProcess;
use application\modules\workflow\utils\Common;
use application\modules\workflow\utils\Form;
use application\modules\workflow\utils\Handle;
use application\modules\workflow\widgets\Base;
use CJSON;

class FreeNext extends Base {
	
	public function ajaxReturn( $data, $type = '' ) {
        if ( empty( $type ) ) {
            $type = 'json';
        }
        
        switch ( strtoupper( $type ) ) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header( 'Content-Type:application/json; charset=' . CHARSET );
                exit( CJSON::encode( $data ) );
                break;
            case 'XML' :
                // 返回xml格式数据
                header( 'Content-Type:text/xml; charset=' . CHARSET );
                exit( xml_encode( $data ) );
                break;
            case 'JSONP':
                // 返回JSONP数据格式到客户端 包含状态信息
                header( 'Content-Type:text/html; charset=' . CHARSET );
                $handler = isset( $_GET['callback'] ) ? $_GET['callback'] : self::DEFAULT_JSONP_HANDLER;
                exit( $handler . '(' . (!empty( $data ) ? CJSON::encode( $data ) : '') . ');' );
                break;
            case 'EVAL' :
                // 返回可执行的js脚本
                header( 'Content-Type:text/html; charset=' . CHARSET );
                exit( $data );
                break;
            default :
                exit( $data );
                break;
        }
    }

    /**
     * 视图要用到的变量
     * @var array 
     */
    private $_var = array();

    /**
     * 额外操作 $op='manage'
     * @var string 
     */
    private $_op = null;

    /**
     * 没有完成当前步骤的人员
     * @var string 
     */
    private $_notFinished = '';

    /**
     *
     * @var type 
     */
    private $_topflag = '';

    /**
     * 初始化办理参数、语言与流程实例对象
     */
    public function init() {
        $key = $this->getKey();
        $flow = new ICFlowType( intval( $key['flowid'] ), true );
        $this->_var = array_merge( $key, array(
            'lang' => IBOS::getLangSources(),
            'flow' => $flow,
            'key' => $this->makeKey( $key ),
            'flowName' => $flow->name,
            'freePreset' => $flow->freepreset,
            'runName' => FlowRun::model()->fetchNameByRunId( $key['runid'] )
                )
        );
        parent::init();
    }

    /**
     * 实例化widget
     */
//    public function run() {
//        $var = $this->_var;
//        $allItemName = $this->getAllItemName( $var );
//        $var['op'] = $this->getOp();
//        $itemArr = is_array( $allItemName ) ? $allItemName : explode( ',', $allItemName );
//        $var['itemArr'] = $itemArr;
//        $var['itemCount'] = count( $itemArr );
//        $var['prcsUser'] = $this->getProcessUser( $var );
//        $var['preset'] = $this->getPreset( $var['processid'], $var['runid'], $var['lang'] );
//        $var['notAllFinished'] = $this->_notFinished;
//        $var['topflag'] = $this->getTopflag();
//        $this->render( self::VIEW, $var );
//    }
	
	public function run() {
        $var = $this->_var;
        $allItemName = $this->getAllItemName( $var );
        $var['op'] = $this->getOp();
        $itemArr = is_array( $allItemName ) ? $allItemName : explode( ',', $allItemName );
        $var['nextId'] = $var['processid']+1;
		$var['itemArr'] = $itemArr;
        $var['itemCount'] = count( $itemArr );
        $var['prcsUser'] = $this->getProcessUser( $var );
        $var['preset'] = $this->getPreset( $var['nextId'], $var['runid'], $var['lang'] );
        $var['notAllFinished'] = $this->_notFinished;
        $var['topflag'] = $this->getTopflag();
        $this->ajaxReturn( $var, Mobile::dataType() );
    }
	
	public function nextPost() {
		$var = $this->_var;
		$topflag = $this->getTopflag();
		// $topflagOld = filter_input( INPUT_POST, 'topflagOld', FILTER_SANITIZE_NUMBER_INT );
		$topflagOld = Env::getRequest( 'topflagOld' );
		$prcsUserOpNext = implode( ',', String::getId( filter_input( INPUT_POST, 'prcsUserOp', FILTER_SANITIZE_STRING ) ) );
		$op = $this->getOp();
		$prcsUserNext = String::getId( filter_input( INPUT_POST, 'prcsUser', FILTER_SANITIZE_STRING ) );
		array_push( $prcsUserNext, $prcsUserOpNext );
		$prcsUserNext = implode( ',', array_unique( $prcsUserNext ) );
		//------------end------------
		$freeOther = $var['flow']->freeother;
		$processIdNext = $var['processid'] + 1;
		$preset = filter_input( INPUT_POST, 'preset', FILTER_SANITIZE_NUMBER_INT );
		//预设步骤的处理
		if ( is_null( $preset ) ) {
			$lineCount = filter_input( INPUT_POST, 'lineCount', FILTER_SANITIZE_NUMBER_INT );
			for ( $i = 0; $i <= $lineCount; $i++ ) {
				$prcsIdSet = $processIdNext + $i;
				$tmp = $i == 0 ? '' : $i;
				//主办人
				$str = "prcsUserOp" . $tmp;
				$prcsUserOp = implode( ',', String::getId( filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING ) ) );
				$prcsUserOpOld = $prcsUserOp;
				if ( $freeOther == 2 ) {
					$prcsUserOp = Handle::turnOther( $prcsUserOp, $var['flowid'], $var['runid'], $var['processid'], $var['flowprocess'] );
				}
				//经办人
				$str = "prcsUser" . $tmp;
				$prcsUser = String::getId( filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING ) ); //把主办人添加到经办人中，省去前台判断
				array_push( $prcsUser, $prcsUserOp );
				$prcsUser = implode( ',', array_unique( $prcsUser ) );
				if ( $freeOther == 2 ) {
					$prcsUser = Handle::turnOther( $prcsUser, $var['flowid'], $var['runid'], $var['processid'], $var['flowprocess'], $prcsUserOpOld );
				}
				$str = "topflag" . $tmp;
				$topflag = filter_input( INPUT_POST, $str, FILTER_SANITIZE_NUMBER_INT );
				$prcsFlag = $i == 0 ? 1 : 5;
				$str = "freeItem" . $tmp;
				$freeItem = filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING );
				if ( is_null( $freeItem ) || empty( $freeItem ) ) {  //如果没有设置可字段就按照上一步骤的主办人设置
					$freeItem = filter_input( INPUT_POST, 'freeItemOld', FILTER_SANITIZE_STRING );
				}
				$tok = strtok( $prcsUser, "," );
				while ( $tok != "" ) {
					$free = $freeItem;
					if ( $tok == $prcsUserOp || $topflag == 1 ) {
						$opflag = 1;
					} else {
						$opflag = 0;
					}
					//无主办人会签
					if ( $topflag == 2 ) {
						$opflag = 0;
					}

					if ( $opflag == 0 ) {
						$free = "";
					}
					$data = array(
						'runid' => $var['runid'],
						'processid' => $prcsIdSet,
						'flowprocess' => $prcsIdSet,
						'uid' => $tok,
						'flag' => $prcsFlag,
						'opflag' => $opflag,
						'topflag' => $topflag,
						'freeitem' => $free,
						'createtime' => TIMESTAMP
					);
					FlowRunProcess::model()->add( $data );
					$tok = strtok( ',' );
				}//while
			}//for
		} else {
			//下一步骤为预设步骤
			FlowRunProcess::model()->updateAll( array( 'flag' => 1 ), sprintf( "runid = %d AND processid = %d", $var['runid'], $processIdNext ) );
		}
		// 工作流日志 
		$presetDesc = !is_null( $preset ) ? $var['lang']['Default step'] : '';
		$userNameStr = User::model()->fetchRealnamesByUids( $prcsUserNext );
		$content = $var['lang']['To the steps'] . $processIdNext . $presetDesc . ',' . $var['lang']['Transactor'] . ':' . $userNameStr;
		Common::runlog( $var['runid'], $var['processid'], 0, IBOS::app()->user->uid, 1, $content );
		//-------end------------
		//-- 设置当前步骤所有人为已转交 --
		FlowRunProcess::model()->updateAll( array( 'flag' => 3 ), sprintf( "runid = %d AND processid = %d", $var['runid'], $var['processid'] ) );
		//-- 设置当前步骤自己的结束时间 --
		FlowRunProcess::model()->updateAll( array( 'delivertime' => TIMESTAMP ), sprintf( "runid = %d AND processid = %d AND uid = %d", $var['runid'], $var['processid'], IBOS::app()->user->uid ) );
		//-- 短信提醒 --
		$content = filter_input( INPUT_POST, 'message', FILTER_SANITIZE_STRING );
		if ( !is_null( $content ) ) {
			$key = array(
				'runid' => $var['runid'],
				'flowid' => $var['flowid'],
				'processid' => $processIdNext,
				'flowprocess' => $var['flowprocess']
			);
			$ext = array(
				'{url}' => IBOS::app()->createUrl( 'workflow/form/index', array( 'key' => Common::param( $key ) ) ),
				'{message}' => $content
			);
			Notify::model()->sendNotify( $prcsUserNext, 'workflow_turn_notice', $ext );
		}
		//--- 流程监控的硬性跳转，要模拟接收办理过程 ---

//		if ( $op == "manage" ) {
//			$prcsFirst = $var['processid'] - 1;
//			$prcsNext = $var['processid'] - 2;
//			FlowRunProcess::model()->updateAll( array( 'flag' => 4 ), sprintf( "runid = %d AND (processid = %d OR processid = %d)", $var['runid'], $prcsFirst, $prcsNext ) );
//		}
		$flowRunPrcess = FlowRunProcess::model()->fetchAll( "runid = {$var['runid']}" );
		$datas = array(
			// 'runid' => $var['runid'],
			// 'processid' => $prcsIdSet,
			// 'flowprocess' => $prcsIdSet,
			// 'uid' => $tok,
			// 'flag' => $prcsFlag,
			// 'opflag' => $opflag,
			// 'topflag' => $topflag,
			// 'freeitem' => $free,
			// 'flowRunPrcess' => $flowRunPrcess
			'isSuccess' => true
		);
//		Main::setCookie( 'flow_turn_flag', 1, 30 );
		$this->ajaxReturn( $datas, Mobile::dataType() );
//        $url = Ibos::app()->createUrl( 'workflow/list/index', array( 'op' => 'list', 'type' => 'trans', 'sort' => 'all' ) );
//        $this->getController()->redirect( $url );
	}

//    public function nextPost() {
//        $var = $this->_var;
//        $topflag = $this->getTopflag();
//        $topflagOld = filter_input( INPUT_POST, 'topflagOld', FILTER_SANITIZE_NUMBER_INT );
//        $prcsUserOpNext = implode( ',', String::getId( filter_input( INPUT_POST, 'prcsUserOp', FILTER_SANITIZE_STRING ) ) );
//        $op = $this->getOp();
//        $prcsUserNext = String::getId( filter_input( INPUT_POST, 'prcsUser', FILTER_SANITIZE_STRING ) );
//        array_push( $prcsUserNext, $prcsUserOpNext );
//        $prcsUserNext = implode( ',', array_unique( $prcsUserNext ) );
//        //------------end------------
//        $freeOther = $var['flow']->freeother;
//        $processIdNext = $var['processid'] + 1;
//        $preset = filter_input( INPUT_POST, 'preset', FILTER_SANITIZE_NUMBER_INT );
//        //预设步骤的处理
//        if ( is_null( $preset ) ) {
//            $lineCount = filter_input( INPUT_POST, 'lineCount', FILTER_SANITIZE_NUMBER_INT );
//            for ( $i = 0; $i <= $lineCount; $i++ ) {
//                $prcsIdSet = $processIdNext + $i;
//                $tmp = $i == 0 ? '' : $i;
//                //主办人
//                $str = "prcsUserOp" . $tmp;
//                $prcsUserOp = implode( ',', String::getId( filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING ) ) );
//                $prcsUserOpOld = $prcsUserOp;
//                if ( $freeOther == 2 ) {
//                    $prcsUserOp = Handle::turnOther( $prcsUserOp, $var['flowid'], $var['runid'], $var['processid'], $var['flowprocess'] );
//                }
//                //经办人
//                $str = "prcsUser" . $tmp;
//                $prcsUser = String::getId( filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING ) ); //把主办人添加到经办人中，省去前台判断
//                array_push( $prcsUser, $prcsUserOp );
//                $prcsUser = implode( ',', array_unique( $prcsUser ) );
//                if ( $freeOther == 2 ) {
//                    $prcsUser = Handle::turnOther( $prcsUser, $var['flowid'], $var['runid'], $var['processid'], $var['flowprocess'], $prcsUserOpOld );
//                }
//                $str = "topflag" . $tmp;
//                $topflag = filter_input( INPUT_POST, $str, FILTER_SANITIZE_NUMBER_INT );
//                $prcsFlag = $i == 0 ? 1 : 5;
//                $str = "freeItem" . $tmp;
//                $freeItem = filter_input( INPUT_POST, $str, FILTER_SANITIZE_STRING );
//                if ( is_null( $freeItem ) || empty( $freeItem ) ) {  //如果没有设置可字段就按照上一步骤的主办人设置
//                    $freeItem = filter_input( INPUT_POST, 'freeItemOld', FILTER_SANITIZE_STRING );
//                }
//                $tok = strtok( $prcsUser, "," );
//                while ( $tok != "" ) {
//					$free = $freeItem;
//                    if ( $tok == $prcsUserOp || $topflag == 1 ) {
//                        $opflag = 1;
//                    } else {
//                        $opflag = 0;
//                    }
//                    //无主办人会签
//                    if ( $topflag == 2 ) {
//                        $opflag = 0;
//                    }
//
//                    if ( $opflag == 0 ) {
//                        $free = "";
//                    }
//                    $data = array(
//                        'runid' => $var['runid'],
//                        'processid' => $prcsIdSet,
//                        'flowprocess' => $prcsIdSet,
//                        'uid' => $tok,
//                        'flag' => $prcsFlag,
//                        'opflag' => $opflag,
//                        'topflag' => $topflag,
//                        'freeitem' => $free
//                    );
//                    FlowRunProcess::model()->add( $data );
//                    $tok = strtok( ',' );
//                }//while
//            }//for
//        } else {
//            //下一步骤为预设步骤
//            FlowRunProcess::model()->updateAll( array( 'flag' => 1 ), sprintf( "runid = %d AND processid = %d", $var['runid'], $processIdNext ) );
//        }
//        // 工作流日志 
//        $presetDesc = !is_null( $preset ) ? $var['lang']['Default step'] : '';
//        $userNameStr = User::model()->fetchRealnamesByUids( $prcsUserNext );
//        $content = $var['lang']['To the steps'] . $processIdNext . $presetDesc . ',' . $var['lang']['Transactor'] . ':' . $userNameStr;
//        Common::runlog( $var['runid'], $var['processid'], 0, Ibos::app()->user->uid, 1, $content );
//        //-------end------------
//        //-- 设置当前步骤所有人为已转交 --
//        FlowRunProcess::model()->updateAll( array( 'flag' => 3 ), sprintf( "runid = %d AND processid = %d", $var['runid'], $var['processid'] ) );
//        //-- 设置当前步骤自己的结束时间 --
//        FlowRunProcess::model()->updateAll( array( 'delivertime' => TIMESTAMP ), sprintf( "runid = %d AND processid = %d AND uid = %d", $var['runid'], $var['processid'], Ibos::app()->user->uid ) );
//        //-- 短信提醒 --
//        $content = filter_input( INPUT_POST, 'message', FILTER_SANITIZE_STRING );
//        if ( !is_null( $content ) ) {
//            $key = array(
//                'runid' => $var['runid'],
//                'flowid' => $var['flowid'],
//                'processid' => $processIdNext,
//                'flowprocess' => $var['flowprocess']
//            );
//            $ext = array(
//                '{url}' => Ibos::app()->createUrl( 'workflow/form/index', array( 'key' => Common::param( $key ) ) ),
//                '{message}' => $content
//            );
//            Notify::model()->sendNotify( $prcsUserNext, 'workflow_turn_notice', $ext );
//        }
//        //--- 流程监控的硬性跳转，要模拟接收办理过程 ---
//
//        if ( $op == "manage" ) {
//            $prcsFirst = $var['processid'] - 1;
//            $prcsNext = $var['processid'] - 2;
//            FlowRunProcess::model()->updateAll( array( 'flag' => 4 ), sprintf( "runid = %d AND (processid = %d OR processid = %d)", $var['runid'], $prcsFirst, $prcsNext ) );
//        }
//        Main::setCookie( 'flow_turn_flag', 1, 30 );
//        $url = Ibos::app()->createUrl( 'workflow/list/index', array( 'op' => 'list', 'type' => 'trans', 'sort' => 'all' ) );
//        $this->getController()->redirect( $url );
//    }

    /**
     * 额外操作
     * @param string $op
     */
    public function setOp( $op ) {
        $this->_op = String::filterCleanHtml( $op );
    }

    /**
     * 返回额外操作
     * @return string
     */
    public function getOp() {
        return $this->_op;
    }

    /**
     * 
     * @param string $topFlag
     */
    public function setTopflag( $topFlag ) {
        $this->_topflag = $topFlag;
    }

    /**
     * 
     * @return string
     */
    public function getTopflag() {
        return $this->_topflag;
    }

    /**
     * 获取步骤用户信息
     * @param array $var
     */
    private function getProcessUser( &$var ) {
        $return = array();
        for ( $i = 1; $i <= $var['processid']; $i++ ) {
            $querys = IBOS::app()->db->createCommand()
                    ->select( '*' )
                    ->from( '{{flow_run_process}}' )
                    ->where( sprintf( "runid = %d AND processid = %d", $var['runid'], $i ) )
                    ->order( 'opflag DESC' )
                    ->queryAll();
            $userNamestr = '';
            $this->handleUserNames( $querys, $userNamestr, $i, $var );
            $return[$i]['userName'] = $userNamestr;
        }
        return $return;
    }

    /**
     * 处理用户信息
     * @param type $rows
     * @param type $lang
     */
    private function handleUserNames( $rows, &$userNamestr, $step, $var ) {

        foreach ( $rows as $row ) {
            $userName = User::model()->fetchRealnameByUid( $row['uid'] );
            if ( $row['opflag'] ) {
                // 主办人
                $userName .= "[{$var['lang']['Host user']}]";
            }
            $isCurStep = $step == $var['processid'];
            $isMe = $row['uid'] == IBOS::app()->user->uid;
            // 处理步骤状态标记
            $userNamestr .= $this->handleFlag( intval( $row['flag'] ), $userName, $var['lang'] );
            if ( $isCurStep && $row['flag'] != 4 && !$isMe ) {
                $this->_notFinished .= $userName . ",";
            }
            $isDone = ($row['flag'] == 3 || $row['flag'] == 4);
            $notInManageMode = $var['op'] != 'manage';
            if ( $isCurStep && $isDone && $isMe && $notInManageMode ) {
                Env::iExit( $var['lang']['Already trans'] ); //已经转交，不能重复转交
            }
            $userNamestr = String::filterStr( $userNamestr );
        }
    }

    /**
     * 处理各种步骤所表示的样式
     * @param integer $flag
     * @param string $userName
     * @param array $lang 语言包
     * @return string
     */
    private function handleFlag( $flag, $userName, $lang ) {
        $userNamestr = '';
        if ( $flag == 1 ) {
            $userNamestr.="<span style='color:red;'>" . $userName . "({$lang['Not receive']})</span><br/>";  //未接收
        } elseif ( $flag == 2 ) {
            $userNamestr.="<span style='color:red;'>" . $userName . "({$lang['In handle']})</span><br/>";  //办理中
        } elseif ( $flag == 4 ) {
            $userNamestr.="<span style='color:green;'>" . $userName . "({$lang['Have been transferred']})</span><br/>"; //已办结
        } else {
            $userNamestr.= $userName . "<br>";
        }
        return $userNamestr;
    }

    /**
     * 获取所有可写的字段名
     * @param array $var 参数数组
     * @return string
     */
    private function getAllItemName( $var ) {
        $flow = $var['flow'];
        if ( $var['processid'] == 1 ) {
            $allItemName = Form::getAllItemName( $flow->form->structure, array(), ($flow->allowattachment == '1' ? '' : '[A@]') . '[B@]' );
            unset( $flow );
        } else {
            $allItemName = FlowRunProcess::model()->fetchFreeitem( $var['runid'], $var['processid'] );
        }
        return $allItemName;
    }

    /**
     * 获取预设步骤
     * @param integer $processId 
     * @param integer $runId
     * @param array $lang
     * @return string 获取预设步骤名称
     */
    private function getPreset( $processId, $runId, $lang ) {
        $preset = '';
        $querys = IBOS::app()->db->createCommand()
                ->select( '*' )->from( '{{flow_run_process}}' )->where( "runid = {$runId} AND processid = {$processId} AND flag = 5" )
                ->order( 'opflag DESC' )
                ->queryAll();
        foreach ( $querys as $row ) {
            $userName = User::model()->fetchRealnameByUid( $row['uid'] );
            if ( $row['opflag'] ) {
                $userName.="[{$lang['Host user']}]";
            }
            $preset .= $userName . ';';
        }
        return $preset;
    }

}
