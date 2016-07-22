<?php

namespace application\modules\main\utils;

/**
 * Description
 *
 * @namespace application\modules\main\utils
 * @filename importInterface.php
 * @encoding UTF-8
 * @author forsona <2317216477@example.com>
 * @link https://github.com/forsona
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-4-12 12:15:03
 * @version $Id: ImportInterface.php 6943 2016-04-26 07:41:07Z tanghang $
 */
interface ImportInterface {

    public function rules();

    public function field();

    public function table();

    public function pk();

    public function config();
}
