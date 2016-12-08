<?php

/**
 * V1-V2数据库升级文件
 */
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
ini_set('memory_limit', '100M');
@set_magic_quotes_runtime(0);

define('PATH_ROOT', dirname(__FILE__) . '/../');  //ibos2根目录
define('CONFIG_PATH', PATH_ROOT . 'system/config/'); // ibos2配置文件目录

require PATH_ROOT . './system/version.php';
require './include/installLang.php';
require './include/installVar.php';
require './include/installFunction.php';

$oldDirName = 'ibos1'; // ibos1文件夹路径（用于判断是否是V1-V2用户升级）
$editPre = 'old_'; // 将Ibos1前缀修改为
define('PATH_ROOT_IBOS1', dirname(__FILE__) . '/../../' . $oldDirName . '/');  //ibos1根目录
define('CONFIG_PATH_IBOS1', PATH_ROOT_IBOS1 . 'config/'); // ibos1配置文件目录

if (!is_dir(PATH_ROOT_IBOS1)) {
    $errorMsg = "没有找到老版本的OA文件夹，请把老版本OA的文件夹名称改为‘old’，重新进行升级";
    include 'errorInfo.php';
    exit();
}

if (isset($_GET['init']) && $_GET['init']) {
// 初始化ibos核心
    // 定义驱动引擎
    define('ENGINE', 'LOCAL');
    // TODO，上线记得删除此行
    define('YII_DEBUG', false);
    $yii = PATH_ROOT . '/library/yii.php';
    $ibosApplication = PATH_ROOT . '/system/core/components/ICApplication.php';
    require_once($yii);
    require_once($ibosApplication);
    $commonConfig = require CONFIG_PATH . 'common.php';
    // 由于模块安装之间的依赖性，需要用到dashboard的一些组件，所以得加载进来
    $dashboardImport = array(
        'application.modules.dashboard.model.*',
        'application.modules.dashboard.utils.*'
    );
    $commonConfig['import'] = array_merge($commonConfig['import'], $dashboardImport);
    unset($commonConfig['preload']);
    Yii::createApplication('ICApplication', $commonConfig);
}

$updataLockFile = PATH_ROOT . './data/updateSQL.lock';
// 是否已升级过
if (file_exists($updataLockFile)) {
    $errorMsg = $lang['UpdateSQL locked'] . str_replace(PATH_ROOT, '', $updataLockFile);
    include 'errorInfo.php';
    exit();
}

if (!isset($_GET['step'])) {
    $step = 'prepare';
} else {
    $step = $_GET['step'];
}
$ibos1ConfigFile = CONFIG_PATH_IBOS1 . 'config_global.php';
$ibos2ConfigFile = CONFIG_PATH . 'config.php';
if ($step == 'prepare') {
    // 用户安装判断是否是V1-V2用户
    $_SESSION['convertDatabase'] = md5('convertDatabase');

    include $ibos1ConfigFile;
    // ibos1数据库信息
    $ibos1Config = array(
        'dbHost' => $_config['db']['1']['dbhost'],
        'dbAccount' => $_config['db']['1']['dbuser'],
        'dbPassword' => $_config['db']['1']['dbpw'],
        'dbName' => $_config['db']['1']['dbname'],
        'dbPre' => $_config['db']['1']['tablepre']
    );
// ibos2数据库信息
    if (file_exists($ibos2ConfigFile)) {
        $configData = include($ibos2ConfigFile);
        $dbInitData = $configData['db'];
        $dbInitData['adminAccount'] = 'admin';
        $dbInitData['adminPassword'] = '';
    } else {
        $dbInitData = array(
            'username' => 'root', // 数据库用户名
            'password' => 'root', // 数据库密码
            'host' => '127.0.0.1', // 数据库服务器
            'dbname' => 'ibos', // 数据库名
            'tableprefix' => 'ibos_', // 数据表前缀
        );
    }
    include 'oldInit.php';
    exit();
} else if ($step == 'modifyPre') {

    $dbHost = $_POST['dbHost'];
    $dbAccount = $_POST['dbAccount'];
    $dbPassword = $_POST['dbPassword'];
    $dbName = $_POST['dbName'];
    $tablePre = $_POST['tablePre'];
    if (!function_exists('mysql_connect')) {
        $ret['isSuccess'] = false;
        $ret['msg'] = 'mysql_connect' . $lang['func not exist'];
        echo json_encode($ret);
        exit();
    }

    $link = @mysql_connect($dbHost, $dbAccount, $dbPassword);
    $selectDb = @mysql_select_db($dbName, $link);

    if (!$link || !$selectDb) {
        $errno = mysql_errno();
        $error = mysql_error();
        if ($errno == 1045) {
            $errnoMsg = $lang['Database errno 1045'];
        } elseif ($errno == 2003) {
            $errnoMsg = $lang['Database errno 2003'];
        } elseif ($errno == 1049) {
            $errnoMsg = $lang['Database errno 1049'];
        } else {
            $errnoMsg = $lang['Database connect error'];
        }
        $ret['isSuccess'] = false;
        $ret['msg'] = $errnoMsg . $lang['Database error info'] . $error;
        echo json_encode($ret);
        exit();
    } else {
        $oldTablePre = $_config['db']['1']['tablepre'];
        $oldPreLength = strlen($oldTablePre);
        $tables = array();
        $query = @mysql_query("SHOW TABLES FROM $dbName");
        while ($row = mysql_fetch_row($query)) {
            $tables[] = $row[0];
        }
        foreach ($tables as $k => $tableName) {
            $unPreName = substr($tableName, $oldPreLength);
            $newTableName = $editPre . $unPreName;
            $sql = 'RENAME TABLE `' . $tableName . '` TO `' . $newTableName . '`';
            if (!mysql_query($sql)) {
                $ret['isSuccess'] = false;
                $ret['msg'] = '数据表' . $tableName . '重命名失败，请重试或手动修改数据表名称';
            }
        }
    }
    $ret['isSuccess'] = true;
    echo json_encode($ret);
    exit();
} elseif ($step == 'convert') { // 开始转换数据
    $ibos2ConfigFile = include CONFIG_PATH . 'config.php';
    $ibos2Config = array(
        'dbHost' => $ibos2ConfigFile['db']['host'],
        'dbAccount' => $ibos2ConfigFile['db']['username'],
        'dbPassword' => $ibos2ConfigFile['db']['password'],
        'dbName' => $ibos2ConfigFile['db']['dbname'],
        'dbPre' => $ibos2ConfigFile['db']['tableprefix']
    );
    $link = @mysql_connect($ibos2Config['dbHost'], $ibos2Config['dbAccount'], $ibos2Config['dbPassword']);
    $selectDb = @mysql_select_db($ibos2Config['dbName'], $link);
    $allowOptions = array(
        'convertUser', 'convertDept', 'convertPosition', 'convertMail', 'convertDiary',
        'convertAtt', 'convertCal', 'convertNews'
    );
    $updateDB = array(
        'convertUser', 'convertDept', 'convertPosition', 'convertMail', 'convertDiary',
        'convertAtt', 'convertCal', 'convertNews'
    );
    $option = $_GET['op'];
    if (empty($option) || !in_array($option, $allowOptions)) {
        $option = 'convertUser';
    }
    switchop($option);
}

//================================================转换数据函数=================================================
function switchop($op)
{
    global $updateDB, $lang;
    $start = false;
    foreach ($updateDB as $moduleName => $v) {
        if ($op == $v)
            $start = true;
        if ($start) {
//			echo "<br/>开始转换", $lang[$v], "<br/>";
            eval($v . "();");
        }
    }
    global $updataLockFile;
    file_put_contents($updataLockFile, '');
    header("Location: index.php?op=updateCache&init=1");
}

function showlog($str)
{
    if (mysql_errno()) {
        $msg = mysql_errno() . ": " . mysql_error();
        header("Location: index.php?op=installResult&res=0&msg=" . $msg);
    }
//	ob_flush();
//	flush();
}

function convertUser()
{
    global $ibos2Config, $editPre;

    $sql = "
REPLACE INTO {$ibos2Config['dbPre']}user 
(
`uid` ,
`username` ,
`isadministrator`,
`deptid` , 
`positionid` ,
`upuid` ,
`groupid` ,
`jobnumber`, 
`realname` ,
`password` ,
`gender` ,
`mobile` ,
`email` ,
`status` ,
`createtime` ,
`credits` ,
`newcomer` ,
`salt` ,
`validationemail`,
`validationmobile`
)
SELECT 
m.`uid` ,
m.`username` ,
m.`adminid` as isadministrator,
m.`deptid` , 
m.`positionid` ,
m.`upuid` ,
m.`groupid` ,
m.`uid`+10000 as jobnumber, -- m.`jobnumber` , -- ****
m.`realname` ,
im.`password` ,
mp.`gender` ,
mp.`mobile` ,
im.`email` ,
ABS(m.`status`) ,
im.`regdate` , -- createtime
m.`credits` ,
m.`newcomer` , -- 1
im.`salt` ,
0 as validationemail, -- m.`validationemail` , -- 0
0 as validationmobile -- m.`validationmobile` , -- 0
FROM " . "{$editPre}common_member" . " m
LEFT JOIN " . "{$editPre}common_member_profile" . " mp 
ON m.uid=mp.uid
LEFT JOIN {$editPre}ic_members im
ON m.uid=im.uid
";

    $do = mysql_query($sql);
    showlog("用户表转换完成");
    $sql = "
	REPLACE INTO {$ibos2Config['dbPre']}user_profile 
	(
	`uid`, `birthday`, `telephone`, `qq`  
	)
	SELECT 
	`uid`, `birthday`, `mobile`, `qq` 
	FROM " . "{$editPre}common_member_profile";
    $do = mysql_query($sql);
    showlog("用户扩展转换完成");


    $sql = "
	REPLACE INTO {$ibos2Config['dbPre']}user_count 
	(
`uid`,
`extcredits1`,
`extcredits2`,
`extcredits3`,
`extcredits4`,
`extcredits5`,
`attachsize`,
`oltime`
	)
	SELECT 
`uid`,
`extcredits1`,
`extcredits2`,
`extcredits3`,
`extcredits4`,
`extcredits5`,
`attachsize`,
`oltime`
	FROM " . "{$editPre}common_member_count";
    $do = mysql_query($sql);
    showlog("用户统计表转换完成");

    $sql = "
	REPLACE INTO {$ibos2Config['dbPre']}user_status
	(
`uid`,
`regip`,
`lastip`,
`lastvisit`,
`lastactivity`,
`invisible`
	)
	SELECT
`uid`,
`regip`,
`lastip`,
`lastvisit`,
`lastactivity`,
`invisible`
	FROM " . "{$editPre}common_member_status";
    $do = mysql_query($sql);
    showlog("用户状态表转换完成");
}

function convertDept()
{
    global $ibos2Config, $editPre;

    $sql = "
REPLACE INTO {$ibos2Config['dbPre']}department
(
`deptid`,`deptname`,`pid`,`manager`,`leader`,`subleader`,`tel`,`fax`,`addr`,`func`,`sort`
)
SELECT 
`deptid`,`deptname`,`upid`,`manager`,`leader`,`subleader`,`tel`,`fax`,`addr`,`func`,`sort` 
FROM {$editPre}ic_department
";
    $do = mysql_query($sql);

    showlog("部门转换完成");
}

function convertPosition()
{
    global $ibos2Config, $editPre;

    //岗位分类表
    $sql = "REPLACE INTO {$ibos2Config['dbPre']}position_category (`catid`,`pid`,`name`,`sort`) SELECT `catid`,`pid`,`name`,`sort` FROM {$editPre}common_position_category";
    $do = mysql_query($sql);
    //岗位表
    $sql = "REPLACE INTO {$ibos2Config['dbPre']}position(`positionid`,`catid`,`posname`,`sort`,`goal`,`minrequirement`) SELECT `positionid`,`catid`,`posname`,`possort`,`goal`,`minrequirement` FROM {$editPre}common_position";
    $do = mysql_query($sql);

    showlog("岗位转换完成");
}

function convertDiary()
{
    if (ModuleUtil::getIsEnabled('diary')) {
        global $ibos2Config, $editPre;

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}diary (
`diaryid`,`uid`,`diarytime`,`nextdiarytime`,`addtime`,`content`,`attachmentid`,`shareuid`,`readeruid`,`remark`,`stamp`,`isreview`
) SELECT 
`diaid`,`uid`,`diadate`, 0 as `nextdiarytime`,`diatime`,`content`,`attachmentid`,`toid`,`readers`,`remark`,`stamp`,1 as `isreview`
FROM {$editPre}diary";
        $do = mysql_query($sql);
        showlog("日志转换完成");
        // @todo:: 需要更新nextdiarytime

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}diary_record (

`recordid`,`diaryid`,`content`,`uid`,`flag`,`planflag`,`schedule`,`plantime`
) SELECT 
`recordid`,`diaid`,`content`,`uid`, 0 as `flag`, ABS((`flag`)/2-1) as `planflag`, `process`/10 , `dateline`

FROM {$editPre}diary_record";
        $do = mysql_query($sql);
        showlog("日志记录转换完成");

        $sql = "UPDATE  
{$ibos2Config['dbPre']}diary d,{$ibos2Config['dbPre']}diary_record dr  SET d.`nextdiarytime` = dr.plantime where d.`diaryid` = dr.`diaryid` AND planflag=1";
        $do = mysql_query($sql);
        showlog("日志更新完成");


        $sql = "REPLACE INTO {$ibos2Config['dbPre']}diary_share SELECT * FROM {$editPre}diary_default_share";
        $do = mysql_query($sql);
        showlog("日志默认共享转换完成");
    }
}

function convertNews()
{
    if (ModuleUtil::getIsEnabled('article')) {
        global $ibos2Config, $editPre;

        //新闻分类表
        $sql = "REPLACE INTO {$ibos2Config['dbPre']}article_category (`catid`,`pid`,`name`,`sort`) SELECT `catid`,`pid`,`name`,`sort` FROM {$editPre}articles_category";
        $do = mysql_query($sql);
        showlog("新闻分类转换完成");
        //新闻表
        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}article(

`articleid`,`subject`,`content`,`type`,`author`,`approver`,`addtime`,`uptime`,`clickcount`,`attachmentid`,`commentstatus`,`url`,`catid`,`status`,`deptid`,`positionid`,`uid`,`istop`,`toptime`,`topendtime`,`ishighlight`,`highlightstyle`,`highlightendtime`
) SELECT 
`articleid`,`subject`,`content`,`type`,`author`,`auditor`,`addtime`,`uptime`,`clickcount`,`attachmentid`,`commentstatus`,`url`,`catid`,`status`,`deptid`,`positionid`,`uid`,`istop`,`topdays`,`topstackdate`,`ishighlight`,`highlightstyle`,`highlightstackdate`

FROM {$editPre}articles";
        $do = mysql_query($sql);
        showlog("新闻转换完成");

        // @todo:: 需要一个转换已读未读人员的内容
        $query = "SELECT `articleid`,`readers`,`uptime` FROM {$editPre}articles";
        $data = mysql_query($query);
        while ($row = mysql_fetch_array($data)) {
            $strArr = explode(",", trim($row["readers"], ','));
            $str = array_filter($strArr, create_function('$v', 'return !empty($v);'));
            foreach ($str as $v) {
                $sql = "REPLACE INTO {$ibos2Config['dbPre']}article_reader(
			`articleid`,
			`uid`,
			`addtime`
			) VALUE ('" . $row["articleid"] . "','" . $v . " ','" . $row["uptime"] . "')
			";
                $do = mysql_query($sql);
            }
        }
        if (mysql_errno()) {
            $errorMsg = mysql_errno() . ": " . mysql_error();
            include 'errorInfo.php';
            exit();
        }
    }
}

function convertMail()
{
    if (ModuleUtil::getIsEnabled('email')) {
        global $ibos2Config, $editPre;

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}email(

`emailid`,`toid`,`isread`,`isdel`,`fid`,`bodyid`,`isreceipt`,`ismark`
) SELECT 
`emailid`,`toid`,`isread`,`isdel`,`boxid`,`bodyid`,`receipt`,`ismark`

FROM {$editPre}email";

        $do = mysql_query($sql);
        showlog("邮件转换完成");

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}email_body(

`bodyid`,`fromid`,`toids`,`copytoids`,`secrettoids`,`subject`,`content`,`sendtime`,`attachmentid`,`isremind`,`issend`,`important`,`size`,`fromwebmail`,`towebmail`,`issenderdel`,`isneedreceipt`
) SELECT 
`bodyid`,`fromid`,`toids`,`copytoids`,`secrettoids`,`subject`,`content`,`sendtime`,`attachmentid`,`isremind`,`issend`,`important`,`size`,`fromwebmail`,`towebmail`,`issenderdel`,`isneedreceipt`

FROM {$editPre}email_body";

        $do = mysql_query($sql);
        showlog("邮件内容转换完成");

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}email_folder(

`fid`,`system`,`sort`,`name`,`uid`,`webid`
) SELECT 
`boxid`,0 as `system`,`boxorder`,`boxname`,`uid`,`webid`

FROM {$editPre}email_box where boxid>4";

        $do = mysql_query($sql);
        showlog("邮件文件夹转换完成");
    }
}

function convertAtt()
{
    global $ibos2Config, $editPre;

    $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}attachment(
`aid`,
`uid`,
`tableid`,
`downloads`
) SELECT 
`aid` ,
`uid` ,
`tableid` ,
`downloads` 
FROM {$editPre}attachment";

    $do = mysql_query($sql);
    showlog("附件主表转换完成");

    $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}attachment_edit SELECT *
FROM {$editPre}attachment_edit";

    $do = mysql_query($sql);
    showlog("附件主表转换完成");

    for ($i = 0; $i < 10; $i++) {
        $sql = "REPLACE INTO 
	{$ibos2Config['dbPre']}attachment_" . $i . "(
	`aid`,
	`uid`,
	`dateline`,
	`filename`,
	`filesize`,
	`description`,
	`attachment`,
	`isimage`
	) SELECT 
	`aid`,
	`uid`,
	`dateline`,
	`filename`,
	`filesize`,
	`description`,
	`attachment`,
	`isimage`
	FROM {$editPre}attachment_" . $i . "";

        $do = mysql_query($sql);
        showlog("附件表" . $i . "转换完成");
    }

    $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}attachment_unused SELECT *
FROM {$editPre}attachment_unused";

    $do = mysql_query($sql);
    showlog("附件主表转换完成");
}

function convertCal()
{
    if (ModuleUtil::getIsEnabled('calendar')) {
        global $ibos2Config, $editPre;

        $sql = "REPLACE INTO 
{$ibos2Config['dbPre']}calendars(
`calendarid`,
`subject`,
`location`,
`mastertime`,
`masterid`,
`description`,
`calendartype`,
`starttime`,
`endtime`,
`isalldayevent`,
`hasattachment`,
`category`,
`instancetype`,
`recurringtype`,
`recurringtime`,
`status`,
`recurringbegin`,
`recurringend`,
`attendees`,
`attendeenames`,
`otherattendee`,
`upuid`,
`upname`,
`uptime`,
`recurringrule`,
`uid`
) SELECT 
`Id`,
`Subject`,
`Location`,
`MasterTime`,
`MasterId`,
`Description`,
`CalendarType`,
`StartTime`,
`EndTime`,
`IsAllDayEvent`,
`HasAttachment`,
`Category`,
`InstanceType`,
`RecurringType`,
`RecurringTime`,
`Status`,
`RecurringBegin`,
`RecurringEnd`,
`Attendees`,
`AttendeeNames`,
`OtherAttendee`,
`UPAccount`,
`UPName`,
`UPTime`,
`RecurringRule`,
`uid`
FROM {$editPre}calendars";

        $do = mysql_query($sql);
        showlog("日程表转换完成");
    }
}
