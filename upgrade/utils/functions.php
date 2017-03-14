<?php


if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    if (isset($_GET)) {
        $_GET = istripSlashes($_GET);
    }
    if (isset($_POST)) {
        $_POST = istripSlashes($_POST);
    }
    if (isset($_REQUEST)) {
        $_REQUEST = istripSlashes($_REQUEST);
    }
    if (isset($_COOKIE)) {
        $_COOKIE = istripSlashes($_COOKIE);
    }
}

/**
 * Strips slashes from input data.
 * This method is applied when magic quotes is enabled.
 *
 * @param mixed $data input data to be processed
 * @return mixed processed data
 */
function istripSlashes(&$data)
{
    if (is_array($data)) {
        if (count($data) == 0) {
            return $data;
        }
        $keys = array_map('istripSlashes', array_keys($data));
        $data = array_combine($keys, array_values($data));
        return array_map('istripSlashes', $data);
    } else {
        return stripslashes($data);
    }
}

/**
 * 翻译语言
 *
 * @param string $key
 * @return string
 */
function t($key)
{
    $lang = getLang();
    return isset($lang[$key]) ? $lang[$key] : $key;
}

/**
 * Returns the named GET parameter value.
 * If the GET parameter does not exist, the second parameter to this method will be returned.
 *
 * @param string $name the GET parameter name
 * @param mixed $defaultValue the default parameter value if the GET parameter does not exist.
 * @return mixed the GET parameter value
 */
function getQuery($name, $defaultValue = null)
{
    return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
}

/**
 * Returns the named POST parameter value.
 * If the POST parameter does not exist, the second parameter to this method will be returned.
 *
 * @param string $name the POST parameter name
 * @param mixed $defaultValue the default parameter value if the POST parameter does not exist.
 * @return mixed the POST parameter value
 */
function getPost($name, $defaultValue = null)
{
    return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
}

/**
 * Returns the named GET or POST parameter value.
 * If the GET or POST parameter does not exist, the second parameter to this method will be returned.
 * If both GET and POST contains such a named parameter, the GET parameter takes precedence.
 *
 * @param string $name the GET parameter name
 * @param mixed $defaultValue the default parameter value if the GET parameter does not exist.
 * @return mixed the GET parameter value
 * @see getQuery
 * @see getPost
 */
function getParam($name, $defaultValue = null)
{
    return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $defaultValue);
}

/**
 * Get the upgrade sql file.
 *
 * @param  string $version
 * @return string
 */
function getUpgradeSQLFile($version)
{
    return PATH_ROOT . '/upgrade/db/update' . $version . '.sql';
}

/**
 * Create the confirm contents.
 *
 * @param  string $fromVersion
 * @return string
 */
function getConfirm($fromVersion)
{
    $confirmContent = '';
    $sqlFile = getUpgradeSQLFile($fromVersion);

    if (file_exists($sqlFile)) {
        $confirmContent .= file_get_contents($sqlFile);
    }
    switch ($fromVersion) {
    }
    return $confirmContent;
}

/**
 * 获取语言包
 *
 * @staticvar array $lang
 * @return array
 */
function getLang()
{
    static $lang = array();
    if (empty($lang)) {
        $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
        if (preg_match("/zh-c/i", $language) || preg_match("/zh/i", $language)) {
            $local = 'cn';
        } else {
            $local = 'en';
        }
        $lang = require PATH_UPGRADE . DS . 'lang/' . $local . '.php';
    }
    return $lang;
}

/**
 * 重定向到另一个页面。
 *
 * @param string $url the target url.
 * @return  void
 */
function locate($url)
{
    header("location: $url");
    exit;
}

/**
 *
 * @return string
 */
function getNewVersion()
{
    $version = strtolower(VERSION . ' ' . VERSION_TYPE);
    return $version;
}

/**
 * 将对象转换成数组
 * @param object $object 对象数组
 * @return mixed
 */
function object2array($object) {
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}

/**
 *
 * @param string $version
 */
function execute($version)
{
    $upgradeSqlFile = getUpgradeSQLFile($version);
    if (file_exists($upgradeSqlFile)) {
        execSQL($upgradeSqlFile);
    }

    switch ($version) {
        case '4.1.0 pro':
            upgradeTo20170103();
            break;
        case '4.2.0 pro':
            upgradeTo20170203();
        default:
            break;
    }
}

/**
 * 数据库更新到 v4.1.0 pro 版本
 */
function upgradeTo20170103()
{
    $queryBuilder = getQB();

    // 通讯录模块更新
    $contactMenuRow = $queryBuilder->table('menu')->where('name', '=', '通讯录')->first();
    if (empty($contactMenuRow)) {
        $queryBuilder->table('menu')->insert(array(
            'name' => '通讯录',
            'pid' => '0',
            'm' => 'contact',
            'c' => 'dashboard',
            'a' => 'index',
            'param' => '',
            'sort' => '2',
            'disabled' => '0',
        ));
    }

    // 新闻模块更新
    $queryBuilder->table('article_approval')->addColumnsIfNotExists(array(
        array(
            'columnName' => 'time',
            'columnType' => "int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间'",
        ),
        array(
            'columnName' => 'isdel',
            'columnType' => "tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '软删除。0为未删除，1为已删除'",
        ),
    ));

    // CRM 模块更新
    $queryBuilder->table('crm_client')->addColumnIfNotExists('phone',
        "char(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '客户电话'");


    // 投票模块数据表变动
    $queryBuilder->table('vote')->addColumnsIfNotExists(array(
        array(
            'columnName' => 'content',
            'columnType' => "text NOT NULL COMMENT '投票描述'",
        ),
        array(
            'columnName' => 'deptid',
            'columnType' => "text NOT NULL COMMENT '阅读范围部门'",
        ),
        array(
            'columnName' => 'positionid',
            'columnType' => "text NOT NULL COMMENT '阅读范围职位'",
        ),
        array(
            'columnName' => 'roleid',
            'columnType' => "text NOT NULL COMMENT '阅读范围角色'",
        ),
        array(
            'columnName' => 'scopeuid',
            'columnType' => "text NOT NULL COMMENT '阅读范围人员'",
        ),
        array(
            'columnName' => 'addtime',
            'columnType' => "int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间'",
        ),
        array(
            'columnName' => 'updatetime',
            'columnType' => "int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间'",
        ),
    ));

    $queryBuilder->table('vote_item')->addColumnIfNotExists('topicid',
        "int(11) unsigned NOT NULL COMMENT '投票题目 id'");

    $queryBuilder->table('vote_item_count')->addColumnsIfNotExists(array(
        array(
            'columnName' => 'voteid',
            'columnType' => "mediumint(9) unsigned NOT NULL COMMENT '投票 id'",
        ),
        array(
            'columnName' => 'topicid',
            'columnType' => "mediumint(9) unsigned NOT NULL COMMENT '投票话题 id'",
        ),
    ));

    $queryBuilder->table('vote')->setTableEngine('InnoDB');
    $queryBuilder->table('vote_item')->setTableEngine('InnoDB');
    $queryBuilder->table('vote_item_count')->setTableEngine('InnoDB');

    $voteNavRow = $queryBuilder->table('nav')->where('name', '调查投票')->first();
    if (empty($voteNavRow)) {
        $queryBuilder->table('nav')->insert(array(
            'pid' => 5,
            'name' => '调查投票',
            'url' => 'vote/default/index',
            'targetnew' => 0,
            'system' => 1,
            'disabled' => 0,
            'sort' => 7,
            'module' => 'vote',
        ));
    }

    $votePublishNode = $queryBuilder->table('notify_node')->where('node', 'vote_publish_message')->first();
    if (empty($votePublishNode)) {
        $queryBuilder->table('notify_node')->insert(array(
            'node' => 'vote_publish_message',
            'nodeinfo' => '投票发布提醒',
            'module' => 'vote',
            'titlekey' => 'vote/default/New message title',
            'contentkey' => 'vote/default/New message content',
            'sendemail' => '1',
            'sendmessage' => '1',
            'sendsms' => '1',
            'type' => '2',
        ));
    }

    $voteUpdateNode = $queryBuilder->table('notify_node')->where('node', 'vote_update_message')->first();
    if (empty($voteUpdateNode)) {
        $queryBuilder->table('notify_node')->insert(array(
            'node' => 'vote_update_message',
            'nodeinfo' => '投票更新提醒',
            'module' => 'vote',
            'titlekey' => 'vote/default/Update message title',
            'contentkey' => 'vote/default/Update message content',
            'sendemail' => '1',
            'sendmessage' => '1',
            'sendsms' => '1',
            'type' => '2',
        ));
    }

    // 投票模块数据迁移
    $votes = $queryBuilder->table('vote')->get();

    foreach ($votes as $vote) {
        $voteId = $vote->voteid;
        $articleId = $vote->relatedid;

        $voteItemModel = $queryBuilder->table('vote_item')->where('voteid', '=', $voteId)->first();
        if (!empty($voteItemModel)) {
            // 添加投票话题（vote topic）
            $itemNum = $queryBuilder->table('vote_item')->where('voteid', '=', $voteId)->count();

            $subject = empty($vote->subject) ? '' : $vote->subject;
            $maxselectnum = (int)$vote->maxselectnum;
            $type = (int)$voteItemModel->type;
            $queryBuilder->table('vote_topic')->insert(array(
                'voteid' => $voteId,
                'subject' => $subject,
                'type' => $type,
                'maxselectnum' => $maxselectnum,
                'itemnum' => $itemNum,
            ));
        }

        $articleModel = $queryBuilder->table('article')->where('articleid', '=', $articleId)->first();
        if (!empty($articleModel)) {
            // 更新投票记录选择范围数据
            $queryBuilder->table('vote')->where('voteid', '=', $voteId)
                ->update(array(
                    'deptid' => $articleModel->deptid,
                    'positionid' => $articleModel->positionid,
                    'roleid' => $articleModel->roleid,
                    'scopeuid' => $articleModel->uid,
                    'addtime' => time(),
                    'updatetime' => time(),
                ));
        }

    }

    // 更新 vote item 数据
    $voteItems = $queryBuilder->table('vote_item')->get();
    if (!empty($voteItems)) {
        foreach ($voteItems as $voteItem) {
            $topic = $queryBuilder->table('vote_topic')->where('voteid', '=', $voteItem->voteid)->first();
            if (!empty($topic)) {
                $queryBuilder->table('vote_item')->where('itemid', '=', $voteItem->itemid)
                    ->update(array('topicid' => $topic->topicid));
            }
        }
    }

    // 更新 vote item count 数据
    $voteItemCounts = $queryBuilder->table('vote_item_count')->get();
    if (!empty($voteItemCounts)) {
        foreach ($voteItemCounts as $voteItemCount) {
            $item = $queryBuilder->table('vote_item')->where('itemid', '=', $voteItemCount->itemid)->first();

            if (!empty($item)) {
                $queryBuilder->table('vote_item_count')->where('voteid', '=', $item->itemid)
                    ->update(array(
                        'voteid' => $item->voteid,
                        'topicid' => $item->topicid,
                    ));
            }

        }
    }

}

/**
 * 更新数据库数据库语句到4.2.0 pro
 */
function upgradeTo20170203()
{
    //更新新闻模块数据库
    $queryBuilder = getQB();
    $approval = $queryBuilder->table('article_approval')->get();
    if (!empty($approval)) {
        //将对象转成数组
        $record = object2array($approval);
        $length = count($approval);
        $all = array();
        for ($i = 0; $i < $length; $i++) {
            if ($record[$i]['step'] == 0) {
                $all[] = array('article', $record[$i]['articleid'], $record[$i]['uid'], $record[$i]['step'], 0, 3, '');
            } else {
                $all[] = array('article', $record[$i]['articleid'], $record[$i]['uid'], $record[$i]['step'], 0, 1, '');
            }
            $last = $queryBuilder->table('article_approval')->where('articleid', '=', $record[$i]['articleid'])->where('step', '=', $record[$i]['step'] + 1)->get();
            $isBack = $queryBuilder->table('article_approval')->where('articleid', '=', $record[$i]['articleid'])->get();
            if (empty($last) && !empty($isBack)) {
                $back = $queryBuilder->table('article_back')->where('articleid', '=', $record[$i]['articleid'])->first();
                $backStep = $record[$i]['step'] + 1;
                $all[] = array(
                    'article',
                    $record[$i]['articleid'],
                    $back->uid,
                    $backStep,
                    $back->time,
                    0,
                    $back->reason,
                );
            }
        }
        if (!empty($all)) {
            for ($i = 0; $i < count($all); $i++) {
                $queryBuilder->table('approval_record')->insert(array(
                    'module' => $all[$i][0],
                    'relateid' => $all[$i][1],
                    'uid' => $all[$i][2],
                    'step' => $all[$i][3],
                    'time' => $all[$i][4],
                    'status' => $all[$i][5],
                    'reason' => $all[$i][6],
                ));
            }
        }
    }
}

function getCross($fromVersion)
{
    global $versions;
    $counter = 0;
    $gotcha = false;
    foreach ($versions as $key => $ver) {
        if ($key == $fromVersion) {
            $gotcha = true;
            break;
        }
        $counter++;
    }
    if ($gotcha) {
        $crossVersions = array_slice($versions, $counter+1);
        return array_keys($crossVersions);
    } else {
        return array();
    }
}

/**
 * Execute a sql.
 *
 * @param  string $sqlFile
 * @return void
 */
function execSQL($sqlFile)
{
    $mysqlVersion = getMysqlVersion();
    $ignoreCode = '|1050|1060|1062|1091|1169|1061|';
    $pdo = getPdo();
    $config = getConfig();
    // Read the sql file to lines, remove the comment lines, then join theme by ';'.
    $sqls = explode("\n", file_get_contents($sqlFile));
    foreach ($sqls as $key => $line) {
        $line = trim($line);
        $sqls[$key] = $line;
        // Skip sql that is note.
        if (preg_match('/^--|^#|^\/\*/', $line) or empty($line)) {
            unset($sqls[$key]);
        }
    }
    $sqls = explode(';', join("\n", $sqls));


    foreach ($sqls as $sql) {
        if (empty($sql)) {
            continue;
        }
        if ($mysqlVersion <= 4.1) {
            $sql = str_replace('DEFAULT CHARSET=utf8', '', $sql);
            $sql = str_replace('CHARACTER SET utf8 COLLATE utf8_general_ci', '', $sql);
        }

        // 替换表前缀，Example：{{user}} => prefix_user、ibos_user => YourPrefix_user
        $sql = str_replace('ibos_', $config['tableprefix'], $sql);
        $sql = preg_replace('/{{(.+?)}}/', $config['tableprefix'] . '\1', $sql);

        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            $errorInfo = $e->errorInfo;
            $errorCode = $errorInfo[1];
            if (strpos($ignoreCode, "|$errorCode|") === false) {
                ErrorLogger::log($e->getMessage() . "<p>The sql is: $sql</p>", 'pdo');
            }
        }
    }
}
