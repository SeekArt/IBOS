<?php


use ibos\upgrade\core\Response;

require_once "bootstrap/autoload.php";


// 初始化请求
$op = getQuery('op');

// 允许执行的操作
$definedFunctions = get_defined_functions();
$allowOps = array_filter($definedFunctions['user'], function($functionName) {
    if (strcasecmp(substr($functionName, 0, 6), 'handle')) {
        return false;
    }
    return true;
});

// 默认 op 为 confirm
if (!in_array(strtolower('handle' . $op), $allowOps)) {
    $op = 'confirm';
}

if (session_id() !== '') {
    $from = getVersion();
    $_SESSION['from'] = rawurldecode($from);
}


try {
    $opFunc = sprintf('handle%s', ucfirst($op));

    if (function_exists($opFunc)) {
        $opFunc();
    } else {
        throw new Exception('Invalid operate, please confirm.');
    }
} catch (Exception $e) {
    ErrorLogger::log($e->getTraceAsString());
    $errors = ErrorLogger::getError();
    return Response::getInstance()->ajaxBaseReturn(false, array(), $e->getMessage());
}

/**
 * 返回数据库升级确认页面
 */
function handleConfirm()
{
    $fromVersion = getVersion();
    $crossVersion = getCross($fromVersion);
    $confirmContent = '';
    if (!empty($crossVersion)) {
        foreach ($crossVersion as $cv) {
            $confirmContent .= getConfirm($cv);
        }
    } else {
        $confirmContent = getConfirm($fromVersion);
    }

    if (empty($confirmContent)) {
        @unlink(UPGRADE_FILE);
    }
    include './views/confirm.php';
}

/**
 * 执行数据库升级操作
 *
 * @return bool
 */
function handleExecute()
{
    $fromVersion = getPost('fromVersion', '');
    $crossVersion = getCross($fromVersion);
    if (!empty($crossVersion)) {
        foreach ($crossVersion as $cv) {
            execute($cv);
        }
    } else {
        execute($fromVersion);
    }
    if (ErrorLogger::hasError()) {
//        $errors = ErrorLogger::getError();
        return Response::getInstance()->ajaxBaseReturn(false, array(), sprintf(t('db upgrade error')));
    } else {
        updateVersion(strtolower(getNewVersion()));
        @unlink(UPGRADE_FILE);
        return Response::getInstance()->ajaxBaseReturn(true, array('fromVersion' => $fromVersion));
    }
}
