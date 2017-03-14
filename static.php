<?php

use application\modules\user\utils\Login;

error_reporting(0);
$engine = isset($_GET['engine']) ? strtolower($_GET['engine']) : '';
if (!in_array($engine, array('local', 'sae'))) {
    $engine = 'local';
}
$type = $_GET['type'];
$ext = '.jpg';
switch ($type) {
    case 'avatar':
    case 'bg':
        if ($type == 'avatar') {
            $path = './data/avatar/';
        } else {
            $path = './data/home/';
        }
        $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        $size = isset($_GET['size']) && in_array(
            $_GET['size'], array('small', 'big', 'middle')) ? $_GET['size'] : 'small';
        $random = isset($_GET['random']) ? $_GET['random'] : '';
        showDataStatic($uid, $size, $random);
        break;
    case 'checklogincode':
        $code = $_GET['code'];
        $filename = './data/temp/login_' . $code . '.txt';
        $notExists = !file_exists($filename);
        $tmpi = 0;
        while ($notExists) {
            usleep(50000); // 休息50毫秒，使CPU不致于负荷太高
            clearstatcache();
            $notExists = !file_exists($filename);
            $tmpi++;
            if ($tmpi > 400) { //设置尝试400次之后放弃，避免死循环。大概25秒多些。
                break;
            }
        }
        if (!$notExists) {
            echo json_encode(array('isSuccess' => true, 'code' => $code));
        } else {
            $random = substr(md5(time()), 0, 11);
            echo json_encode(array('isSuccess' => false, 'url' => 'static.php?type=qrcode&data=' . $random, 'code' => $random));
        }
        break;
    case 'checklogin':
        $code = $_GET['code'];
        $filename = './data/temp/login_' . $code . '.txt';
        $currentModif = filemtime($filename);
        $tmpi = 0;
        while (true) {
            usleep(50000); // 休息50毫秒，使CPU不致于负荷太高
            clearstatcache();
            if (@filesize($filename) > 0) {
                $content = file_get_contents($filename);
                $uid = authCode($content, 'DECODE', $code);
                if ($uid) {
                    define('PATH_ROOT', dirname(__FILE__));
                    $defines = PATH_ROOT . '/system/defines.php';
                    define('TIMESTAMP', time());
                    define('YII_DEBUG', true);
                    $yii = PATH_ROOT . '/library/yii.php';
                    $mainConfig = require_once PATH_ROOT . '/system/config/common.php';
                    require_once($defines);
                    require_once($yii);
                    require_once './api/login.php';
                    Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
                    Yii::createApplication('application\core\components\Application', $mainConfig);
                    doLogin($uid, 'web');
                    unlink($filename);
                    Login::getInstance()->sendWxLoginNotify($uid);
                    echo json_encode(array('isSuccess' => true));
                    exit();
                }
            }
            $tmpi++;
            if ($tmpi > 400) { //设置尝试400次之后放弃，避免死循环。大概25秒多些。
                break;
            }
        }
        echo json_encode(array('isSuccess' => false));
        break;
    default:
        break;
}

function showDataStatic($uid, $size = 'small', $random = '')
{
    global $engine, $path, $type, $ext;
    // $staticFile = getDataStaticFile( $uid, $size, $type );
    $dir = (int)($uid / 100);
    $staticFile = $dir . '/' . $uid . '_' . $type . '_' . $size . '.jpg';
    if ($engine == 'local') {
        $fileExists = file_exists($path . $staticFile);
    } else {
        require_once './system/extensions/enginedriver/sae/SAEFile.php';
        $file = new SAEFile();
        $path = $file->fileName(trim($path, './'));
        $fileExists = $file->fileExists($path . $staticFile);
    }
    if ($fileExists) {
        $random = !empty($random) ? rand(1000, 9999) : '';
        $staticUrl = empty($random) ? $path . $staticFile : $path . $staticFile . '?random=' . $random;
    } else {
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'small';
        $staticUrl = $path . "no{$type}_" . $size . $ext;
    }
    if (empty($random)) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Last-Modified:" . date('r'));
        header("Expires: " . date('r', time() + 86400));
    }
    header('Location: ' . $staticUrl);
}

/**
 * 获取指定用户存放在data静态资源文件路径存放地址
 * @param integer $uid 用户ID
 * @param string $size 背景尺寸
 * @param string $type 静态资源类型
 * @return string
 */
function getDataStaticFile($uid, $size = 'small', $type = 'avatar')
{
    $uid = sprintf("%09d", abs(intval($uid)));
    $level1 = substr($uid, 0, 3);
    $level2 = substr($uid, 3, 2);
    $level3 = substr($uid, 5, 2);
    return $level1 . '/' . $level2 . '/' . $level3 . '/' . substr($uid, -2) . "_{$type}_{$size}.jpg";
}

function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckeyLength = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckeyLength ? ($operation == 'DECODE' ?
        substr($string, 0, $ckeyLength) :
        substr(md5(microtime()), -$ckeyLength)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $keyLength = strlen($cryptkey);

    $string = $operation == 'DECODE' ?
        base64_decode(substr($string, $ckeyLength)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0) .
        substr(md5($string . $keyb), 0, 16) . $string;
    $stringLength = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $stringLength; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}
