<?php

/**
 * IMRtx class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * IM组件RTX类，实现ICIM里的抽象方法并提供推送，同步等功能
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core
 * @version $Id$
 */

namespace application\modules\message\core;

use application\core\utils\String;
use application\core\utils\Convert;
use application\core\utils\IBOS;
use application\modules\user\model\User;
use application\modules\department\utils\Department as DepartmentUtil;

class IMRtx extends IM {

    /**
     * 同步标记，是增加还是删除
     * @var type 
     */
    protected $syncFlag;

    /**
     * 同步用户时的密码。明文
     * @var type 
     */
    protected $pwd;

    /**
     *
     * @var type 
     */
    private $users = array();

    /**
     * 
     * @param type $flag
     */
    public function setPwd( $pwd ) {
        $this->pwd = String::filterCleanHtml( $pwd );
    }

    /**
     * 
     * @return type
     */
    public function getPwd() {
        return $this->pwd;
    }

    /**
     * 检查RTX绑定是否可用。只需检查初始化COM组件即可
     * @return boolean
     */
    public function check() {
        if ( $this->isEnabled( 'open' ) ) {
            if ( extension_loaded( 'com_dotnet' ) && LOCAL ) {
                $obj = new \COM( 'RTXSAPIRootObj.RTXSAPIRootObj' );
                return is_object( $obj );
            } else {
                $this->setError( '服务器环境不支持调用组件，请联系系统管理员', self::ERROR_INIT );
                return false;
            }
        }
    }

    /**
     * 统一推送接口
     */
    public function push() {
        $type = $this->getPushType();
        if ( $type == 'notify' && $this->isEnabled( 'push/note' ) ) {
            $this->pushNotify();
        } elseif ( $type == 'pm' && $this->isEnabled( 'push/msg' ) ) {
            $this->pushMsg();
        }
    }

    /**
     * 同步组织架构到RTX
     * @return boolean
     */
    public function syncOrg() {
        $obj = $this->getObj( false );
        $obj->Name = "USERSYNC";
        $rtxParam = new \COM( 'rtxserver.collection' );
        $xmlDoc = new \DOMDocument( '1.0', 'GB2312' );
        $xml = $this->makeOrgstructXml();
        if ( $xml ) {
            $xmlDoc->load( 'userdata.xml' );
            $rtxParam->Add( "DATA", $xmlDoc->saveXML() );
            $rs = $obj->Call2( 1, $rtxParam );
            $newObj = $this->getObj();
            try {
                $u = $newObj->UserManager();
                foreach ( $this->users as $user ) {
                    $u->SetUserPwd( Convert::iIconv( $user, CHARSET, 'gbk' ), $this->pwd );
                }
                return true;
            } catch (Exception $exc) {
                $this->setError( '同步过程中出现未知错误', self::ERROR_SYNC );
                return false;
            }
        } else {
            $this->setError( '无法生成组织架构XML文件', self::ERROR_SYNC );
            return false;
        }
    }

    /**
     * 同步用户
     */
    public function syncUser() {
        $syncFlag = $this->getSyncFlag();
        try {
            if ( in_array( $syncFlag, array( 1, 0 ) ) ) {
                $syncUsers = User::model()->fetchAllByUids( $this->getUid() );
                $obj = $this->getObj();
                $userObj = $obj->UserManager();
                foreach ( $syncUsers as $user ) {
                    $userName = Convert::iIconv( $user['username'], CHARSET, 'gbk' );
                    // 同步增加人员
                    if ( $syncFlag == 1 ) {
                        $realName = Convert::iIconv( $user['realname'], CHARSET, 'gbk' );
                        $userObj->AddUser( $userName, 0 );
                        $userObj->SetUserPwd( $userName, $this->getPwd() );
                        $userObj->SetUserBasicInfo( $userName, $realName, -1, $user['mobile'], $user['email'], $user['telephone'], 0 );
                        // 暂时屏蔽同步用户部门，这部分很不稳定，容易出问题。原因未知 @banyan
                    } else {
                        // 删除人员
                        if ( $userObj->IsUserExist( $userName ) ) {
                            $userObj->DeleteUser( $userName );
                        }
                    }
                }
            }
            $exit = <<<EOT
			<script>parent.Ui.tip('同步完成','success');parent.Ui.closeDialog();</script>
EOT;
            Env::iExit( $exit );
        } catch (Exception $exc) {
            $exit = <<<EOT
			<script>parent.Ui.tip('同步出现问题，无法完成。请联系系统管理员解决','danger');parent.Ui.closeDialog();</script>				
EOT;
            Env::iExit( $exit );
        }
    }

    /**
     * 创建GUID，RTX发送IM消息时要用到
     * @return string
     */
    protected function GUID() {
        $charid = strtoupper( md5( uniqid( mt_rand(), true ) ) );
        $hyphen = chr( 45 ); // "-"
        $uuid = chr( 123 )// "{"
                . substr( $charid, 0, 8 ) . $hyphen
                . substr( $charid, 8, 4 ) . $hyphen
                . substr( $charid, 12, 4 ) . $hyphen
                . substr( $charid, 16, 4 ) . $hyphen
                . substr( $charid, 20, 12 )
                . chr( 125 ); // "}"
        return $uuid;
    }

    /**
     * 推送 私信到IM
     * @return boolean
     */
    protected function pushMsg() {
        $users = User::model()->fetchAllByUids( $this->getUid() );
        if ( !empty( $users ) ) {
            $userNames = Convert::getSubByKey( $users, 'username' );
            $names = Convert::iIconv( implode( ';', $userNames ), CHARSET, 'gbk' );
            $message = $this->formatContent( strip_tags( $this->getMessage(), '<a>' ) );
            try {
                $res = $this->obj->SendIM( Convert::iIconv( IBOS::app()->user->username, CHARSET, 'gbk' ), '', $names, $message, $this->GUID() );
                return $res;
            } catch (Exception $exc) {
                
            }
        }
        return false;
    }

    /**
     * 推送提醒
     * @return type
     */
    protected function pushNotify() {
        $users = User::model()->fetchAllByUids( $this->getUid() );
        if ( !empty( $users ) ) {
            $userNames = Convert::getSubByKey( $users, 'username' );
            $names = Convert::iIconv( implode( ';', $userNames ), CHARSET, 'gbk' );
            $title = Convert::iIconv( IBOS::lang( 'System notify', 'default' ), CHARSET, 'gbk' );
            $message = $this->formatContent( strip_tags( $this->getMessage(), '<a>' ) );
            try {
                return $this->obj->SendNotify( $names, $title, 0, $message );
            } catch (Exception $exc) {
                
            }
        }
        return false;
    }

    /**
     * 获取RTX对象
     * @param boolean $newApi 是否使用新的API接口
     * @return \COM Object
     */
    protected function getObj( $newApi = true ) {
        $config = $this->getConfig();
        if ( $newApi ) {
            $rtxObj = new \COM( "RTXSAPIRootObj.RTXSAPIRootObj" );
        } else {
            $rtxObj = new \COM( "rtxserver.rtxobj" );
        }
        $rtxObj->ServerIP = $config['server'];
        $rtxObj->ServerPort = $newApi ? $config['appport'] : $config['sdkport'];
        return $rtxObj;
    }

    /**
     * 格式化推送内容，替换有<a>链接的为[链接|链接文字]之类RTX接受的信息
     * @param string $content
     * @return string
     */
    private function formatContent( $content ) {
        if ( !empty( $this->url ) ) {
            $url = parse_url( $this->getUrl() );
            $str = '';
            if ( !isset( $url['scheme'] ) && !isset( $url['host'] ) ) {
                $str .= IBOS::app()->setting->get( 'siteurl' );
            }
            $content = sprintf( "[%s|%s]", $content, $str . $this->getUrl() );
        }
        return Convert::iIconv( $content, CHARSET, 'gbk' );
    }

    /**
     * 获取组织架构树xml并生成文件
     * @return bool
     */
    private function makeOrgstructXml() {
        $deptArr = DepartmentUtil::loadDepartment();
        $unit = IBOS::app()->setting->get( 'setting/unit' );
        $str = "<?xml version=\"1.0\" encoding=\"gb2312\" ?>";
        $str .= '<enterprise name="' . $unit['fullname'] . '" postcode="' .
                $unit['zipcode'] . '" address="' . $unit['address'] . '" phone="' .
                $unit['phone'] . '" email="' . $unit['adminemail'] . "\">";
        $str .= "<departments>";
        $str .= $this->getDeptree( $deptArr );
        $str .= "</departments>";
        $str .= "</enterprise>";
        $file = 'userdata.xml';
        $fp = @fopen( $file, 'wb' );
        if ( $fp ) {
            $str = Convert::iIconv( $str, CHARSET, 'gbk' );
            file_put_contents( $file, $str );
            if ( filesize( $file ) > 0 ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取部门树结构
     * @param array $deptArr
     * @param integer $id
     * @return string
     */
    private function getDeptree( $deptArr, $id = 0 ) {
        $str = '';
        foreach ( $deptArr as $key => $value ) {
            $upid = $value['pid'];
            if ( $id == $upid ) {
                $tmp = $this->getDeptree( $deptArr, $value['deptid'] );
                if ( !$tmp ) {
                    $tmp .= self::getUserlistByDept( $value['deptid'] );
                    $str.= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value['func']}\">";
                } else {
                    $str.= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value['func']}\">";
                }
                $str.= $tmp;
                $str.= "</department>";
                unset( $deptArr[$key] );
            }
        }
        return $str;
    }

    /**
     * 根据部门ID获取用户列表
     * @param integer $deptId
     * @return string
     */
    private function getUserlistByDept( $deptId ) {
        $str = '';
        $querys = IBOS::app()->db->createCommand()
                ->select( 'uid' )
                ->from( '{{user}} u' )
                ->where( '`status` = 0 AND deptid = ' . intval( $deptId ) )
                ->queryAll();
        foreach ( $querys as $row ) {
            $user = User::model()->fetchByUid( $row['uid'] );
            $gender = $user['gender'] == '1' ? 0 : 1;
            array_push( $this->users, $user['username'] );
            $str .= <<<EOT
					<user uid="{$user['username']}" name="{$user['realname']}" email="{$user['email']}" mobile="{$user['mobile']}" rtxno="" phone="{$user['telephone']}" 
		position="{$user['posname']}" fax="" homepage="" address="{$user['address']}" age="0" gender="{$gender}" />  
EOT;
        }
        return $str;
    }

}
