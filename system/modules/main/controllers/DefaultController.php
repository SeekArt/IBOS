<?php

/**
 * main模块的默认控制器
 *
 * @version $Id: DefaultController.php 6759 2016-04-06 02:09:02Z tanghang $
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\model\Module;
use application\core\utils\Attach;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Module as ModuleUtil;
use application\core\utils\Org;
use application\core\utils\String;
use application\extensions\ThinkImage\ThinkImage;
use application\modules\department\model\Department;
use application\modules\main\model\MenuCommon;
use application\modules\main\model\MenuPersonal;
use application\modules\main\model\ModuleGuide;
use application\modules\main\model\Setting;
use application\modules\main\utils\Main;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class DefaultController extends Controller {

    /**
     * 办公首页
     * @return void
     */
    public function actionIndex() {
        // 所有安装模块
        $modules = Module::model()->fetchAllEnabledModule();
        $widgetModule = $modules;
        foreach ( $widgetModule as $index => $module ) {
            $conf = CJSON::decode( $module['config'] );
            $param = $conf['param'];
            if ( !isset( $param['indexShow'] ) || !isset( $param['indexShow']['widget'] ) ) {
                unset( $widgetModule[$index] );
            }
        }
        // 分别执行每个模块api的loadSetting方法
        $widgets = $this->getWidgets( $modules );
        $moduleSetting = Main::execLoadSetting( 'loadSetting', $widgets );
        $data = array(
            'modules' => $modules,
            'widgetModule' => $moduleSetting,
            'moduleSetting' => CJSON::encode( $moduleSetting ),
            'menus' => MenuPersonal::model()->fetchMenuByUid( IBOS::app()->user->uid )
        );
        $this->setPageTitle( IBOS::lang( 'Home office' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Home office' ) )
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 获取widget数组
     * @param array $modules
     * @return array
     */
    private function getWidgets( $modules ) {
        $widgets = array();
        foreach ( $modules as $m ) {
            $config = CJSON::decode( $m['config'] );
            if ( isset( $config['param']['indexShow']['widget'] ) && is_array( $config['param']['indexShow']['widget'] ) ) {
                $widgets = array_merge( $widgets, $config['param']['indexShow']['widget'] );
            }
        }
        return $widgets;
    }

    /**
     * // todo :: 不支持浏览器的提示界面
     */
    public function actionUnsupportedBrowser() {
        $alias = 'application.views.browserUpgrade';
        $this->renderPartial( $alias );
    }

    /**
     * 快捷方式到更新缓存
     */
    public function actionUpdate() {
        $op = Env::getRequest( 'op' );
        $opArr = array( 'data', 'static', 'module', 'success' );
        if ( !in_array( $op, $opArr ) ) {
            $op = 'data';
        }
        if ( $op == 'success' ) {
            Env::iExit( '缓存更新完成' );
        }
        // 保险起见，设置执行时间为两分钟，更长一些
        if ( LOCAL ) {
            @set_time_limit( 0 );
        }
        switch ( $op ) {
            case 'data':
                Cache::update();
                $op = 'static';
                break;
            case 'static':
                LOCAL && IBOS::app()->assetManager->republicAll();
                Org::update();
                $op = 'module';
                break;
            case 'module':
                ModuleUtil::updateConfig();
                $op = 'success';
                break;
            default:
                break;
        }
        IBOS::app()->cache->clear();
        $this->redirect( $this->createUrl( 'default/update', array( 'op' => $op ) ) );
    }

    /*
     * 初始化引导入口
     */

    public function actionGuide() {
        $operation = Env::getRequest( 'op' );
        if ( !in_array( $operation, array( 'neverGuideAgain', 'checkIsGuided', 'companyInit', 'addUser', 'modifyPassword', 'modifyProfile', 'uploadAvatar' ) ) ) {
            $res['isSuccess'] = false;
            $res['msg'] = IBOS::lang( 'Parameters error', 'error' );
            $this->ajaxReturn( $res );
        } else {
            $this->$operation();
        }
    }

    /**
     * 不再提醒
     */
    private function neverGuideAgain() {
        $uid = IBOS::app()->user->uid;
        User::model()->modify( $uid, array( 'newcomer' => 0 ) );
    }

    /**
     * 检查用户是否引导过
     */
    private function checkIsGuided() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            // 检查该uid是否引导过
            $uid = IBOS::app()->user->uid;
            $isadministrator = $uid == 1 ? true : false;
            $user = User::model()->fetchByAttributes( array( 'uid' => $uid ) );
            $newcomer = $user['newcomer'];
            if ( !$newcomer ) {
                $this->ajaxReturn( array( 'isNewcommer' => false ) );
            } else {
                if ( $uid == 1 ) {
                    // 如果是管理员,返回管理员的初始化引导视图
                    $guideAlias = 'application.modules.main.views.default.adminGuide';
                    $unit = String::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
                    $data['fullname'] = $unit['fullname'];
                    $data['shortname'] = $unit['shortname'];
                    $data['pageUrl'] = $this->getPageUrl();
                } else {
                    $data['swfConfig'] = Attach::getUploadConfig( $uid );
                    $data['uid'] = $uid;
                    // 返回一般用户的初始化引导视图
                    $guideAlias = 'application.modules.main.views.default.initGuide';
                }
                $account = String::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'account' ) );
                $data['account'] = $account;
                if ( $account['mixed'] ) {
                    $data['preg'] = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
                } else {
                    $data['preg'] = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
                }
                $data['lang'] = IBOS::getLangSource( 'main.default' );
                $data['assetUrl'] = $this->getAssetUrl();
                $guideView = $this->renderPartial( $guideAlias, $data, true );
                $this->ajaxReturn( array( 'isNewcommer' => true, 'guideView' => $guideView, 'isadministrator' => $isadministrator ) );
            }
        }
    }

    /**
     * 填写公司资料
     */
    private function companyInit() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            // 添加公司资料
            $postData = array();
            $keys = array(
                'logourl', 'phone', 'fullname',
                'shortname', 'fax', 'zipcode',
                'address', 'adminemail', 'systemurl', 'corpcode'
            );
            $unit = String::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
            foreach ( $keys as $key ) {
                if ( isset( $_POST[$key] ) ) {
                    $postData[$key] = String::filterCleanHtml( $_POST[$key] );
                } else {
                    $postData[$key] = '';
                }
            }
            //企业代码是不能修改的
            $postData['corpcode'] = $unit['corpcode'];

            Setting::model()->updateSettingValueByKey( 'unit', $postData );
            Cache::update( array( 'setting' ) );
            // 添加部门
//			Department::model()->deleteAll(); // 由于可能有演示数据,暂时屏蔽
            $depts = Env::getRequest( 'depts' );
            $isSuccess = $this->handleDept( $depts );
            if ( $isSuccess ) {
                $uid = IBOS::app()->user->uid;
                User::model()->modify( $uid, array( 'newcomer' => 0 ) ); // 改成非新人，表示引导过
                $deptCache = IBOS::app()->setting->get( 'cache/department' );
                $posCache = IBOS::app()->setting->get( 'cache/position' );
                $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";
                $res['isSuccess'] = true;
                $res['depts'] = String::getTree( $deptCache, $selectFormat );
                $res['positions'] = $posCache;
            } else {
                $res['isSuccess'] = false;
                $res['msg'] = IBOS::lang( 'Add department fail' );
            }
            $this->ajaxReturn( $res );
        }
    }

    /**
     * 插入部门数据
     * @param string $depts 用户输入的部门字符串
     */
    private function handleDept( $depts ) {
        $depts = trim( String::filterCleanHtml( $depts ) ); // 安全过滤
        $deptArr = preg_split( "/\n/", $depts ); // 换行符分割部门字符串
        if ( !empty( $deptArr ) ) {
            // 组合带有空格数的部门数组
            foreach ( $deptArr as $index => $deptName ) {
                $deptName = rtrim( $deptName ); // 去掉部门字符后面的空白字符
                if ( empty( $deptName ) ) {
                    // 去掉空项
                    unset( $deptArr[$index] );
                    continue;
                }
                $deptArr[$index] = $deptName;
            }
        }
        if ( !empty( $deptArr ) ) {
            $deptArr = array_values( $deptArr ); // 格式化键名，从0开始
            foreach ( $deptArr as $index => $deptName ) {
                $dept = array();
                $dept['deptname'] = trim( $deptName ); // 最终插入数据库的部门名称
                $indentBlank = strspn( $deptName, " " ); // 缩进空格数
                $indentTab = strspn( $deptName, "	" ); // 缩进的tab
                $indentAll = $indentBlank + $indentTab;
                if ( $indentAll == 0 ) { // 如果没空格，是一级部门
                    $dept['pid'] = 0;
                    $newId = Department::model()->add( $dept, true );
                    if ( $newId ) {
                        Department::model()->modify( $newId, array( 'sort' => $newId ), '', array(), false );
                    }
                } else { // 否则有空格，则需要查找刚才添加的父级部门
                    $accordItem = array();
                    foreach ( $deptArr as $k => $v ) {
                        $indentBlank2 = strspn( $v, " " ); // 缩进空格数
                        $indentTab2 = strspn( $v, "	" ); //缩进的tab
                        $indentAll2 = $indentBlank2 + $indentTab2;
                        if ( $k < $index && $indentAll2 < $indentAll ) { // 找键值小过它的和空格少于它的，符合条件的再找键值最大的一个
                            $accordItem[$k] = $v; // 符合的项
                        }
                    }
                    $upDeptName = '';
                    if ( count( $accordItem ) == 1 ) { // 只有一个的话直接拿他的值
                        $upDeptName = array_shift( $accordItem );
                    } elseif ( count( $accordItem ) > 1 ) { // 多余一个的话就拿键值最大的那个
                        $maxKey = max( array_keys( $accordItem ) );
                        $upDeptName = $deptArr[$maxKey];
                    }
                    // 根据父级部门名称查找父级部门deptid
                    $upDept = Department::model()->fetchByAttributes( array( 'deptname' => trim( $upDeptName ) ) );
                    if ( !empty( $upDept ) ) {
                        $dept['pid'] = $upDept['deptid'];
                        $newId = Department::model()->add( $dept, true );
                        if ( $newId ) {
                            Department::model()->modify( $newId, array( 'sort' => $newId ), '', array(), false );
                        }
                    }
                }
            }
        }
        Cache::update( array( 'department' ) );
        $newId && Org::update();
        return !!$newId;
    }

    /**
     * 添加用户
     */
    private function addUser() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $fields = array( 'username', 'password', 'realname', 'mobile', 'deptid', 'positionid', 'email' );
            if ( empty( $_POST['username'] ) || empty( $_POST['password'] ) ) {
                $this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Username or password not empty' ) ) );
            }
            foreach ( $fields as $field ) {
                if ( isset( $_POST[$field] ) && !empty( $_POST[$field] ) ) {
                    $_POST[$field] = String::filterDangerTag( $_POST[$field] ); // 安全过滤
                }
            }
            $salt = String::random( 6 );
            $userData = array(
                'salt' => $salt,
                'username' => $_POST['username'],
                'password' => !empty( $_POST['password'] ) ? md5( md5( $_POST['password'] ) . $salt ) : '',
                'realname' => $_POST['realname'],
                'mobile' => $_POST['mobile'],
                'createtime' => TIMESTAMP,
                'deptid' => intval( $_POST['deptid'] ),
                'positionid' => intval( $_POST['positionid'] ),
                'email' => $_POST['email']
            );
            $newId = User::model()->add( $userData, true );
            if ( $newId ) {
                UserCount::model()->add( array( 'uid' => $newId ) );
                $ip = IBOS::app()->setting->get( 'clientip' );
                UserStatus::model()->add(
                        array(
                            'uid' => $newId,
                            'regip' => $ip,
                            'lastip' => $ip
                        )
                );
                UserProfile::model()->add( array( 'uid' => $newId ) );
                // 更新组织架构js调用接口
                Org::update();
                $res['isSuccess'] = true;
            } else {
                $res['isSuccess'] = false;
                $res['msg'] = IBOS::lang( 'Add user failed' );
            }
            $this->ajaxReturn( $res );
        }
    }

    /**
     * 修改密码
     */
    private function modifyPassword() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $uid = IBOS::app()->user->uid;
            $user = User::model()->fetchByAttributes( array( 'uid' => $uid ) );
            if ( Env::getRequest( 'checkOrgPass' ) ) {
                $originalpass = Env::getRequest( 'originalpass' );
                $isSuccess = strcasecmp( md5( md5( $originalpass ) . $user['salt'] ), $user['password'] ) == 0 ? true : false;
                $this->ajaxReturn( array( 'isSuccess' => $isSuccess ) );
            }
            $data = $_POST;
            if ( $data['originalpass'] == '' ) {
                // 没有填写原来的密码
                $res['isSuccess'] = false;
                $res['msg'] = IBOS::lang( 'Original password require' );
            } else if ( strcasecmp( md5( md5( $data['originalpass'] ) . $user['salt'] ), $user['password'] ) !== 0 ) {
                // 密码跟原来的对不上
                $res['isSuccess'] = false;
                $res['msg'] = IBOS::lang( 'Password is not correct' );
            } else if ( !empty( $data['newpass'] ) && strcasecmp( $data['newpass'], $data['newpass_confirm'] ) !== 0 ) {
                // 两次密码不一致
                $res['isSuccess'] = false;
                $res['msg'] = IBOS::lang( 'Confirm password is not correct' );
            } else {
                $password = md5( md5( $data['newpass'] ) . $user['salt'] );
                User::model()->updateByUid( $uid, array( 'password' => $password, 'lastchangepass' => TIMESTAMP ) );
                $res['realname'] = $user['realname'];
                $res['mobile'] = $user['mobile'];
                $res['email'] = $user['email'];
                $res['isSuccess'] = true;
            }
            $this->ajaxReturn( $res );
        }
    }

    /**
     * 上传头像操作
     */
    private function uploadAvatar() {
        // 获取上传域并上传到临时目录
        $upload = File::getUpload( $_FILES['Filedata'] );
        if ( !$upload->save() ) {
            $this->ajaxReturn( array( 'msg' => IBOS::lang( 'Save failed', 'message' ), 'IsSuccess' => false ) );
        } else {
            $info = $upload->getAttach();
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::fileName( $file );
            $tempSize = File::imageSize( $fileUrl );
            //判断宽和高是否符合头像要求
            if ( $tempSize[0] < 180 || $tempSize[1] < 180 ) {
                $this->ajaxReturn( array( 'msg' => IBOS::lang( 'Avatar size error' ), 'IsSuccess' => false ), 'json' );
            }
            // 加载类库
            $imgObj = new ThinkImage( THINKIMAGE_GD );
            $imgTemp = $imgObj->open( $fileUrl );
            //裁剪参数
            $params = array(
                "w" => $imgTemp->width(),
                "h" => $imgTemp->height(),
                "x" => "0",
                "y" => "0"
            );
            //转换一下，得到小于宽高最小值正常比例的图片
            if ( $params["w"] > $params["h"] ) {
                $params["x"] = ($params["w"] - $params["h"]) / 2;
                $params["w"] = $params["h"];
            } else {
                $params["y"] = ($params["h"] - $params["w"]) / 2;
                $params["h"] = $params["w"];
            }
            // 裁剪原图，裁剪中间
            $imgObj->open( $fileUrl )->crop( $params["w"], $params["h"], $params['x'], $params['y'] )->save( $fileUrl );
            $this->ajaxReturn( array( 'data' => $fileUrl, 'file' => $fileUrl, 'IsSuccess' => true ) );
        }
    }

    /**
     * 填写个人资料
     */
    private function modifyProfile() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $uid = IBOS::app()->user->uid;
            // 生成头像
            if ( !empty( $_POST['src'] ) ) {
                $this->cropImg();
            }
            $profileField = array( 'birthday' );
            $userField = array( 'mobile', 'email' );
            $model = array();
            // 确定更新所使用MODEL
            foreach ( $_POST as $key => $value ) {
                if ( in_array( $key, $profileField ) && !empty( $value ) ) {
                    // 生日字段的转换处理
                    if ( $key == 'birthday' ) {
                        $value = strtotime( $value );
                        $model['application\modules\user\model\UserProfile'][$key] = String::filterCleanHtml( $value );
                    }
                } else if ( in_array( $key, $userField ) ) {
                    $model['application\modules\user\model\User'][$key] = String::filterCleanHtml( $value );
                }
            }
            // 更新操作
            foreach ( $model as $modelObject => $value ) {
                $modelObject::model()->modify( $uid, $value );
            }
            // 提交完资料后就改成已引导过
            User::model()->modify( $uid, array( 'newcomer' => 0 ) );
            $isInstallWeibo = ModuleUtil::getIsEnabled( 'weibo' );
            $this->ajaxReturn( array( 'isSuccess' => true, 'isInstallWeibo' => !!$isInstallWeibo ) );
        }
    }

    /**
     * 生成头像
     */
    private function cropImg() {
        $uid = IBOS::app()->user->uid;
        // 临时头像地址
        $tempAvatar = $_POST['src'];
        // 存放路径
        $avatarPath = 'data/avatar/';
        // 三种尺寸的地址
        $avatarBig = UserUtil::getAvatar( $uid, 'big' );
        $avatarMiddle = UserUtil::getAvatar( $uid, 'middle' );
        $avatarSmall = UserUtil::getAvatar( $uid, 'small' );
        // 如果是本地环境，先确定文件路径要存在
        if ( LOCAL ) {
            File::makeDirs( $avatarPath . dirname( $avatarBig ) );
        }
        // 先创建空白文件
        File::createFile( 'data/avatar/' . $avatarBig, '' );
        File::createFile( 'data/avatar/' . $avatarMiddle, '' );
        File::createFile( 'data/avatar/' . $avatarSmall, '' );
        // 加载类库
        $imgObj = new ThinkImage( THINKIMAGE_GD );
        //生成缩略图
        $imgObj->open( $tempAvatar )->thumb( 180, 180, 1 )->save( $avatarPath . $avatarBig );
        $imgObj->open( $tempAvatar )->thumb( 60, 60, 1 )->save( $avatarPath . $avatarMiddle );
        $imgObj->open( $tempAvatar )->thumb( 30, 30, 1 )->save( $avatarPath . $avatarSmall );
    }

    /**
     * 获取系统url
     */
    private function getPageUrl() {
        $pageURL = 'http';
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        $thisPage = $_SERVER["REQUEST_URI"];
        // 只取 ? 前面的内容
        if ( strpos( $thisPage, "?" ) !== false ) {
            $thisPageParams = explode( "?", $thisPage );
            $thisPage = reset( $thisPageParams );
        }
        if ( $_SERVER["SERVER_PORT"] != "80" ) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $thisPage;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $thisPage;
        }
        return $pageURL;
    }

    /**
     * 模块引导
     */
    public function actionModuleGuide() {
        $uid = IBOS::app()->user->uid;
        $id = String::filterCleanHtml( Env::getRequest( 'id' ) );
        $op = Env::getRequest( 'op' );
        if ( $op == 'checkHasGuide' ) {
            // 返回用户是否已经引导过
            $guide = ModuleGuide::model()->fetchGuide( $id, $uid );
            $hasGuide = empty( $guide ) ? false : true;
            Cache::update( 'department' );
            $this->ajaxReturn( array( 'hasGuide' => $hasGuide ) );
        } elseif ( $op == 'finishGuide' ) {
            // 完成引导
            ModuleGuide::model()->add( array(
                'route' => $id,
                'uid' => $uid
            ) );
        }
    }

    /**
     * 前台显示证书
     * @return type
     */
    public function actionGetCert() {
        $certAlias = 'application.modules.main.views.default.cert';
        $params = array(
            'lang' => IBOS::getLangSource( 'main.default' )
        );
        $certView = $this->renderPartial( $certAlias, $params, true );
        echo $certView;
    }

    /**
     * 设置个人常用菜单
     */
    public function actionPersonalMenu() {
        if ( Env::submitCheck( 'personalMenu' ) ) {
            $ids = Env::getRequest( 'mod' );
            $uid = IBOS::app()->user->uid;
            MenuPersonal::model()->deleteAll( "uid = {$uid}" );
            if ( !empty( $ids ) ) {
                $common = implode( ',', $ids );
            } else {
                $common = '';
            }
            $data = array(
                'uid' => $uid,
                'common' => $common
            );
            MenuPersonal::model()->add( $data );
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     * 设置默认常用菜单
     */
    public function actionCommonMenu() {
        if ( Env::submitCheck( 'commonMenu' ) ) {
            $ids = Env::getRequest( 'mod' );
            MenuCommon::model()->updateAll( array( 'sort' => 0, 'iscommon' => 0 ) );
            if ( !empty( $ids ) ) {
                foreach ( $ids as $index => $id ) {
                    MenuCommon::model()->updateAll( array( 'sort' => intval( $index ) + 1, 'iscommon' => 1 ), "id='{$id}'" );
                }
            }
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     * 恢复默认菜单设置
     */
    public function actionRestoreMenu() {
        if ( Env::submitCheck( 'restoreMenu' ) ) {
            $uid = IBOS::app()->user->uid;
            MenuPersonal::model()->deleteAll( "uid = {$uid}" );
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
    }

    /**
     *  获取通用文件上传对话框
     */
    public function actionGetUploadDlg() {
        $alias = 'application.views.upload';
        $view = $this->renderPartial( $alias, array(), true );
        echo $view;
    }

}
