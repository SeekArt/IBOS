<?php

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\user\model\User;

/**
 * 上传sun，测试一万人的性能
 *
 * @namespace application\modules\main\controllers
 * @filename TestController.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link https://www.ibos.com.cn
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-4-6 11:12:22
 * @version $Id$
 */
class TestController extends Controller
{

    public function actionIndex()
    {
        echo '<meta charset="UTF-8">';
        $start = microtime(true);
        $users = User::model()->fetchAllByUids();
        echo '一共' . count($users) . '个用户';
        echo '<br/>';
        $end = microtime(true);
        echo '耗时' . number_format($end - $start, 6);
        echo '<br/>';
        echo '当前用户的格式：<pre>';
        print_r($users[Ibos::app()->user->uid]);
    }

    public function actionTestfile()
    {
        var_dump(File::fileSize(__FILE__));
    }

}
