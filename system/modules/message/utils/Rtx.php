<?php

/**
 * @filename Rtx.php
 * @encoding UTF-8
 * @author gzdzl
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2010-2015 IBOS Inc
 * @datetime 2015-7-28  14:42:57
 */
/**
 * rtx api 的部分操作代码
 * define('SDK_COMMAND_MESSAGE_CODE', 0x2100);
 * define('SDK_COMMAND_IM_CODE', 0x2002);
 * define('SDK_LOGIC_TOOL_NAME', 'SYSTOOLS');
 *
 * define('SDK_COMMAND_USER_ADD', 1);
 * define('SDK_COMMAND_USER_DEL', 2);
 * define('SDK_COMMAND_USER_EDIT_SIMPLE', 3);
 * define('SDK_COMMAND_USER_VIEW_DETAIL', 4);
 * define('SDK_COMMAND_USER_EDIT_DETAIL', 5);
 * define('SDK_COMMAND_USER_VIEW_SIMPLE', 6);
 * define('SDK_COMMAND_USER_IS_EXIST', 8);
 * define('SDK_COMMAND_USER_SESSION_KEY', 0x2000);
 *
 * define('SDK_COMMAND_DEPT_ADD', 0x101);
 * define('SDK_COMMAND_DEPT_DEL', 0x102);
 * define('SDK_COMMAND_DEPT_EDIT', 0x103);
 * define('SDK_COMMAND_DEPT_VIEW', 0x107);
 */

namespace application\modules\message\utils;

/**
 * rtx基本工具类，不能直接使用
 */
class Rtx
{

    /**
     * rtx Rtxserver.rtxobj对象
     * @var type
     */
    protected $_rtxObj;

    /**
     * rtx RTXSAPIRootObj.RTXSAPIRootObj对象
     * @var type
     */
    protected $_rootObj;

    /**
     * rtx Rtxserver.collection对象
     * @var type
     */
    protected $_collection;

    /**
     * 构造rtx
     * @param type $server rtx服务器地址
     * @param type $port 监听端口
     * @param type $logicName 逻辑名称，默认是：USERMANAGER
     */
    public function __construct($server, $port, $logicName = 'USERMANAGER')
    {
        $this->_rtxObj = new \COM('Rtxserver.rtxobj');
        $this->_rtxObj->Name = $logicName;
        $this->_collection = new \COM('Rtxserver.collection');
    }

    /**
     * 初始化rtx
     */
    protected function initRtx()
    {
        $this->_rootObj = new \COM('RTXSAPIRootObj.RTXSAPIRootObj');
    }

    /**
     * 验证操作是否成功
     * @param type $result
     * @return boolean 成功返回true，否则返回false
     */
    protected function verifyResult($result)
    {
        if (strcmp($result, null) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 异常信息
     * @param type $state 状态码
     * @param type $msg 消息详情
     * @return type json格式
     */
    protected function haltMessage($status, $msg)
    {
        $data = array('status' => $status, 'msg' => $msg);
        return json_encode($data);
    }

}
