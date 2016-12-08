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

use application\core\utils\StringUtil;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\user\model\User;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\message\utils\RtxDept as RtxDeptUtil;
use application\modules\message\utils\RtxUser as RtxUserUtil;
use application\core\utils\Env;

class IMRtx extends IM
{

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
     * rtx 用户管理
     * @var type
     */
    private $_userManager;

    /**
     * rtx部门管理
     * @var type
     */
    private $_deptManager;

    /**
     *
     * @param type $flag
     */
    public function setPwd($pwd)
    {
        $this->pwd = StringUtil::filterCleanHtml($pwd);
    }

    /**
     *
     * @return type
     */
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * 检查RTX绑定是否可用。只需检查初始化COM组件即可
     * @return boolean
     */
    public function check()
    {
        if ($this->isEnabled('open')) {
            if (extension_loaded('com_dotnet') && LOCAL) {
                $obj = new \COM('RTXSAPIRootObj.RTXSAPIRootObj');
                return is_object($obj);
            } else {
                $this->setError('请检查是否安装php扩展com_dotnet以及RTX服务端程序', self::ERROR_INIT);
                return false;
            }
        }
    }

    /**
     * 统一推送接口
     */
    public function push()
    {
        $type = $this->getPushType();
        if ($type == 'notify' && $this->isEnabled('push/note')) {
            $this->pushNotify();
        } elseif ($type == 'pm' && $this->isEnabled('push/msg')) {
            $this->pushMsg();
        }
    }

    /**
     * 同步组织架构到RTX
     * @return boolean
     */
    public function syncOrg()
    {
        //读取缓存的部门信息，很容易发生错误，所以是直接读取数据库的信息
        //$deptArr = DepartmentUtil::loadDepartment();
        //读取配置信息
        $config = $this->getConfig();
        //获取部门信息,不去读取缓存
        $depts = Ibos::app()->db->createCommand()
            ->select(array('deptid', 'deptname', 'pid'))
            ->from('{{department}}')
            ->queryAll();
        //部门管理工具实例
        $rtxDeptUtil = new RtxDeptUtil($config['server'], $config['sdkport']);
        $countDept = 0; //统计添加成功的部门
        foreach ($depts as $dept) {
            //中文的必须是GBK编码
            $dept['deptname'] = iconv('UTF-8', 'GBK', $dept['deptname']);
            //判断部门是否已经存在
            if (!$rtxDeptUtil->isExistDept($dept['deptname'])) {
                //暂时不添加部门的说明信息
                $result = $rtxDeptUtil->addDept(intval($dept['pid']), intval($dept['deptid']), $dept['deptname'], '');
                if ($result) {
                    $countDept++; //添加成功一个就加一
                }
                //TODO 根据result判断是否添加成功，再做相应的处理，错误处理
            }
        }
        //=====添加用户到组织架构=====
        //获取用户
        $users = Ibos::app()->db->createCommand()
            ->select(array('uid', 'deptid', 'username'))
            ->from('{{user}} u')
            ->where('`status` = 0')
            ->queryAll();
        //添加用户
        //注意这里的端口
        $rtxUserUtil = new RtxUserUtil($config['server'], $config['appport']);
        $countUser = 0;
        foreach ($users as $user) {
            //中文必须是GBK编码的
            $user['username'] = iconv('UTF-8', 'GBK', $user['username']);
            //用户是否已经存在
            if (!$rtxUserUtil->isExistUser($user['username'])) {
                //添加用户
                $result = $rtxUserUtil->addUser($user['deptid'], $user['uid'], $user['username'], $this->pwd);
                if ($result) {
                    $countUser++;
                }
                //TODO 判断是否添加成功，错误处理
            }
        }
        //部门和用户有添加成功就返回true
        if (($countDept + $countUser) > 0) {
            return true;
        }
        return false;
    }

    /**
     * 同步用户
     */
    public function syncUser()
    {
        $syncFlag = $this->getSyncFlag();
        try {
            if (in_array($syncFlag, array(1, 0))) {
                $syncUsers = User::model()->fetchAllByUids($this->getUid());
                $obj = $this->getObj();
                $userObj = $obj->UserManager();
                foreach ($syncUsers as $user) {
                    $userName = Convert::iIconv($user['username'], CHARSET, 'GBK');
                    // 同步增加人员
                    if ($syncFlag == 1) {
                        //读取配置
                        $config = $this->getConfig();
                        //rtx用户管理对象
                        $rtxUser = new RtxUserUtil($config['server'], $config['appport']);
                        //登陆密码
                        $password = $this->getPwd();
                        //是否已经存在
                        if (!$rtxUser->isExistUser($userName)) {
                            //添加用户
                            $result = $rtxUser->addUser($user['deptid'], $user['uid'], $userName, $password);
                            //TODO 是否添加成功，添加失败的处理
                        }
                        //======//同步用户======
                        //之前的代码
                        //$realName = Convert::iIconv($user['realname'], CHARSET, 'gbk');
                        //$userObj->AddUser($userName, 0);
                        //$password = $this->getPwd();
                        //$userObj->SetUserPwd($userName, $password);
                        // $userObj->SetUserBasicInfo($userName, $realName, -1, $user['mobile'], $user['email'], $user['telephone'], 0);
                        // 暂时屏蔽同步用户部门，这部分很不稳定，容易出问题。原因未知 @banyan
                    } else {
                        // 删除人员
                        if ($userObj->IsUserExist($userName)) {
                            $userObj->DeleteUser($userName);
                        }
                    }
                }
            }
            $exit = <<<EOT
			<script>parent.Ui.tip('同步完成','success');parent.Ui.closeDialog();</script>
EOT;
            Env::iExit($exit);
        } catch (Exception $exc) {
            $exit = <<<EOT
			<script>parent.Ui.tip('同步出现问题，无法完成。请联系系统管理员解决','danger');parent.Ui.closeDialog();</script>
EOT;
            Env::iExit($exit);
        }
    }

    /**
     * 创建GUID，RTX发送IM消息时要用到
     * @return string
     */
    protected function GUID()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = chr(123)// "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125); // "}"
        return $uuid;
    }

    /**
     * 推送 私信到IM
     * @return boolean
     */
    protected function pushMsg()
    {
        $users = User::model()->fetchAllByUids($this->getUid());
        if (!empty($users)) {
            $userNames = Convert::getSubByKey($users, 'username');
            $names = Convert::iIconv(implode(';', $userNames), CHARSET, 'gbk');
            $message = $this->formatContent(strip_tags($this->getMessage(), '<a>'));
            try {
                $res = $this->obj->SendIM(Convert::iIconv(Ibos::app()->user->username, CHARSET, 'gbk'), '', $names, $message, $this->GUID());
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
    protected function pushNotify()
    {
        $users = User::model()->fetchAllByUids($this->getUid());
        if (!empty($users)) {
            $userNames = Convert::getSubByKey($users, 'username');
            $names = Convert::iIconv(implode(';', $userNames), CHARSET, 'gbk');
            $title = Convert::iIconv(Ibos::lang('System notify', 'default'), CHARSET, 'gbk');
            $message = $this->formatContent(strip_tags($this->getMessage(), '<a>'));
            try {
                return $this->obj->SendNotify($names, $title, 0, $message);
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
    protected function getObj($newApi = true)
    {
        $config = $this->getConfig();
        if ($newApi) {
            $rtxObj = new \COM("RTXSAPIRootObj.RTXSAPIRootObj");
        } else {
            $rtxObj = new \COM("rtxserver.rtxobj");
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
    private function formatContent($content)
    {
        if (!empty($this->url)) {
            $url = parse_url($this->getUrl());
            $str = '';
            if (!isset($url['scheme']) && !isset($url['host'])) {
                $str .= Ibos::app()->setting->get('siteurl');
            }
            //这里获取到的url地址有错误
            //去掉问号（?）之前的字符
            $pathUrl = substr($this->getUrl(), strpos($this->getUrl(), '?'));
            $content = sprintf("[%s|%s]", $content, $str . $pathUrl);
        }
        return Convert::iIconv($content, CHARSET, 'gbk');
    }

    /**
     * 获取组织架构树xml并生成文件
     * @return bool
     */
    private function makeOrgstructXml()
    {
        $deptArr = DepartmentUtil::loadDepartment();
        $unit = Ibos::app()->setting->get('setting/unit');
        $str = "<?xml version=\"1.0\" encoding=\"gb2312\" ?>";
        $str .= '<enterprise name="' . $unit['fullname'] . '" postcode="' .
            $unit['zipcode'] . '" address="' . $unit['address'] . '" phone="' .
            $unit['phone'] . '" email="' . $unit['adminemail'] . "\">";
        $str .= "<departments>";
        $str .= $this->getDeptree($deptArr);
        $str .= "</departments>";
        $str .= "</enterprise>";
        $file = 'userdata.xml';
        $fp = @fopen($file, 'wb');
        if ($fp) {
            $str = Convert::iIconv($str, CHARSET, 'gbk');
            file_put_contents($file, $str);
            if (filesize($file) > 0) {
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
    private function getDeptree($deptArr, $id = 0)
    {
        $str = '';
        foreach ($deptArr as $key => $value) {
            $upid = $value['pid'];
            if ($id == $upid) {
                $tmp = $this->getDeptree($deptArr, $value['deptid']);
                if (!$tmp) {
                    $tmp .= self::getUserlistByDept($value['deptid']);
                    $str .= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value['func']}\">";
                } else {
                    $str .= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value['func']}\">";
                }
                $str .= $tmp;
                $str .= "</department>";
                unset($deptArr[$key]);
            }
        }
        return $str;
    }

    /**
     * 根据部门ID获取用户列表
     * @param integer $deptId
     * @return string
     */
    private function getUserlistByDept($deptId)
    {
        $str = '';
        $querys = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from('{{user}} u')
            ->where('`status` = 0 AND deptid = ' . intval($deptId))
            ->queryAll();
        foreach ($querys as $row) {
            $user = User::model()->fetchByUid($row['uid']);
            $gender = $user['gender'] == '1' ? 0 : 1;
            array_push($this->users, $user['username']);
            $str .= <<<EOT
					<user uid="{$user['username']}" name="{$user['realname']}" email="{$user['email']}" mobile="{$user['mobile']}" rtxno="" phone="{$user['telephone']}"
		position="{$user['posname']}" fax="" homepage="" address="{$user['address']}" age="0" gender="{$gender}" />
EOT;
        }
        return $str;
    }

    private function addUserToRtx()
    {
        //添加用户到组织架构中
        //获取用户
        $querys = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from('{{user}} u')
            ->where('`status` = 0')
            ->queryAll();
        //添加用户
        foreach ($querys as $row) {
            //获取了很多没有必要的数据，有待优化
            $user = User::model()->fetchByUid($row['uid']);
            //添加用户之前判断是否已经存在该用户，如果已经存在是会出错的
            // $userManager = $newObj->UserManager;
            //编码一致
            $nickname = iconv('UTF-8', 'GBK', $user['username']);
            //不存在才添加进去
            $rtx = $this->getObj();
            $userManager = $rtx->UserManager;
            if (!$userManager->IsUserExist($nickname)) {
                $gender = $user['gender'] == '1' ? 0 : 1;
                //防止乱码
                $username = iconv('UTF-8', 'GBK', $user['realname']);
                $userManager->AddUser($nickname, 0); //添加用户，第二个参数给了例子是写0，暂时还知道用来干嘛的
                $userManager->SetUserPwd($nickname, $this->pwd); //设置用户密码
                //str1是手机，str2是email，str3是电话
                //nickname是告诉设置哪一个用户的基本信息
                //第三个参数应该是性别：0是男，1是女
                //最后一个参数是认证类型：0本地认证，其他的貌似都是第三方认证（其他的不测试了）
                //$UserManagerObj -> SetUserBasicInfo($nickname, $username, $gender, 'str1','str2','str3',0);
                $userManager->SetUserBasicInfo($nickname, $username, $gender, $user['mobile'], $user['email'], '', 0);

                //添加到部门（没有设置部门信息RTX会默认放到：顶级部门架构）
                //在部门架构下的用户无法在rtx客户端中显示，必须是属于某一个部门
                $queryDeptName = Ibos::app()->db->createCommand()
                    ->select('deptname')
                    ->from('{{department}}')
                    ->where('`deptid` =' . $user['deptid'])
                    ->queryAll();
                //查到有部门才设置用户部门，不设置的话可以添加进去，但是不可以在客户端中显示
                if (!empty($queryDeptName[0]['deptname'])) {
                    $dept = iconv('UTF-8', 'GBK', $queryDeptName[0]['deptname']);
                    $rtx = $this->getObj();
                    $deptManager = $rtx->DeptManager;
                    $deptManager->AddUserToDept($nickname, "", $dept, false);
                }
            }
        }
        return true;
    }

}
