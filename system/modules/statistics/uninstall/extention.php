<?php

use application\modules\dashboard\model\Menu;
use application\modules\main\model\Setting;

// 删除setting键值
$settingFields = 'statmodules';
Setting::model()->deleteAll("FIND_IN_SET(skey,'{$settingFields}')");
// 后台菜单
Menu::model()->deleteAllByAttributes(array('m' => 'statistics'));
