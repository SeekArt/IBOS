<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\dashboard\utils\ArchiveSplit;
use application\modules\main\model\Setting;

class SplitController extends BaseController
{

    const DEFAULT_ARCHIVE_MOVE = 100; // 分表存档默认移动条数

    public function actionIndex()
    {
        $mod = Env::getRequest('mod');
        $modList = array('email', 'diary');
        if (!in_array($mod, $modList)) {
            foreach ($modList as $module) {
                if (Module::getIsEnabled($module)) {
                    $mod = $module;
                    break;
                }
            }
        }
        // 检查模块是否已安装
        if (!Module::getIsEnabled($mod)) {
            $this->error(Ibos::lang('Module not installed'));
        }
        $operation = Env::getRequest('op');
        if (!in_array($operation, array('manage', 'move', 'movechoose', 'droptable', 'addtable'))) {
            $operation = 'manage';
        }
        $data = array();
        // 针对不同的模块，采用表驱动法来确定各个模块的配置键值
        if ($mod == 'email') {
            $tableDriver = array(
                'tableId' => 'emailtableids',
                'tableInfo' => 'emailtable_info',
                'mainTable' => 'Email',
                'bodyTable' => 'EmailBody',
                'bodyIdField' => 'bodyid'
            );
        } else {
            $tableDriver = array(
                'tableId' => 'diarytableids',
                'tableInfo' => 'diarytable_info',
                'mainTable' => 'Diary',
                'bodyTable' => 'DiaryRecord',
                'bodyIdField' => 'diaryid'
            );
        }
        $setting = Setting::model()->fetchSettingValueByKeys($tableDriver['tableId'] . ',' . $tableDriver['tableInfo'], true);
        // 分表信息数组
        $tableIds = $setting[$tableDriver['tableId']] ? $setting[$tableDriver['tableId']] : array();
        $tableInfo = $setting[$tableDriver['tableInfo']] ? $setting[$tableDriver['tableInfo']] : array();
        $formSubmit = Env::submitCheck('archiveSubmit');
        if ($formSubmit) {
            if ($operation == 'manage') {
                // 更新分表管理信息
                $info = array();
                $_POST['memo'] = !empty($_POST['memo']) ? $_POST['memo'] : array();
                $_POST['displayname'] = !empty($_POST['displayname']) ? $_POST['displayname'] : array();
                foreach (array_keys($_POST['memo']) as $tableId) {
                    $info[$tableId]['memo'] = $_POST['memo'][$tableId];
                }
                foreach (array_keys($_POST['displayname']) as $tableId) {
                    $info[$tableId]['displayname'] = $_POST['displayname'][$tableId];
                }
                // 更新分表设置信息
                Setting::model()->updateSettingValueByKey($tableDriver['tableInfo'], $info);
                Cache::save($tableDriver['tableInfo'], $info);
                // 更新分表ID
                ArchiveSplit::updateTableIds($tableDriver);
                // 更新setting表
                Cache::update(array('setting'));
                $this->success(Ibos::lang('Archivessplit manage update succeed'), $this->createUrl('split/index', array('op' => 'manage', 'mod' => $mod)));
            } else if ($operation == 'movechoose') {
                // 选择移动信息
                $conditions = array(
                    'sourcetableid' => Env::getRequest('sourcetableid'),
                    'timerange' => intval(Env::getRequest('timerange'))
                );
                $showDetail = intval($_POST['detail']);
                $count = ArchiveSplit::search($conditions, $tableDriver, true);
                $data['count'] = $count;
                $data['sourceTableId'] = $conditions['sourcetableid'];
                $data['tableInfo'] = $tableInfo;
                if ($showDetail) {
                    $list = ArchiveSplit::search($conditions, $tableDriver);
                    !empty($list) && $data['list'] = $list;
                    $data['detail'] = 1;
                } else {
                    $data['readyToMove'] = $count;
                    $data['detail'] = 0;
                }
                $data['conditions'] = serialize($conditions);
                $data = array_merge($data, ArchiveSplit::getTableStatus($tableIds, $tableDriver));
                $this->render($mod . 'MoveChoose', $data);
            } else if ($operation == 'moving') {
                $tableId = intval(Env::getRequest('tableid'));
                $step = intval(Env::getRequest('step'));
                $sourceTableId = intval(Env::getRequest('sourcetableid'));
                $detail = intval(Env::getRequest('detail'));
                // 处理移动
                if (!$tableId) {
                    $this->error(Ibos::lang('Archivessplit no target table'));
                }
                $continue = false;
                //要移动的总数
                $readyToMove = intval(Env::getRequest('readytomve'));
                $bodyIdArr = !empty($_POST['bodyidarray']) ? $_POST['bodyidarray'] : array();
                if (empty($bodyIdArr) && !$detail && !empty($_POST['conditions'])) {
                    $conditions = StringUtil::utf8Unserialize($_POST['conditions']);
                    $maxMove = intval($_POST['pertime']) ? intval($_POST['pertime']) : self::DEFAULT_ARCHIVE_MOVE;
                    $list = ArchiveSplit::search($conditions, $tableDriver, false, $maxMove);
                    $bodyIdArr = Convert::getSubByKey($list, $tableDriver['bodyIdField']);
                } else {
                    //统计提交过来的条数
                    $readyToMove = count($bodyIdArr);
                }
                if (!empty($bodyIdArr)) {
                    $continue = true;
                }
                if ($tableId == $sourceTableId) {
                    $this->error(Ibos::lang('Archivessplit source cannot be the target'), $this->createUrl('split/index', array('op' => 'move', 'mod' => $mod)));
                }
                if ($continue) {
                    $cronArchiveSetting = Setting::model()->fetchSettingValueByKeys('cronarchive', true);
                    $tableTarget = intval($tableId);
                    $tableSource = $_POST['sourcetableid'] ? $_POST['sourcetableid'] : 0;
                    $tableDriver['mainTable']::model()->moveByBodyId($bodyIdArr, $tableSource, $tableTarget);
                    $tableDriver['bodyTable']::model()->moveByBodyid($bodyIdArr, $tableSource, $tableTarget);
                    if (!$step) {
                        //设置计划任务
                        if ($_POST['setcron'] == '1') {
                            $cronArchiveSetting[$mod] = array(
                                'sourcetableid' => $tableSource,
                                'targettableid' => $tableTarget,
                                'conditions' => StringUtil::utf8Unserialize($_POST['conditions'])
                            );
                        } else {
                            unset($cronArchiveSetting[$mod]);
                        }
                        Setting::model()->updateSettingValueByKey('cronarchive', $cronArchiveSetting);
                    }
                    $completed = intval(Env::getRequest('completed')) + count($bodyIdArr);
                    $nextStep = $step + 1;
                }
                $param = array(
                    'op' => 'moving',
                    'tableid' => $tableId,
                    'completed' => $completed,
                    'sourcetableid' => $sourceTableId,
                    'readytomove' => $readyToMove,
                    'step' => $nextStep,
                    'detail' => $detail,
                    'mod' => $mod
                );
                $data['message'] = Ibos::lang(ucfirst($mod) . ' moving', '', array(
                        '{count}' => $completed,
                        '{total}' => $readyToMove,
                        '{pertime}' => $_POST['pertime'],
                        '{conditions}' => $_POST['conditions']
                    )
                );
                $data['url'] = $this->createUrl('split/index', $param);
                $this->render('moving', $data);
            }
        } else {
            if ($operation == 'droptable') { // 删除存档表
                $tableId = intval(Env::getRequest('tableid'));
                // 获取要删除的分表的信息
                $statusInfo = $tableDriver['mainTable']::model()->getTableStatus($tableId);
                if (!$tableId || !$statusInfo) {
                    $this->error(Ibos::lang('Archivessplit table no exists'));
                }
                // 有数据的表不能删除
                if ($statusInfo['Rows'] > 0) {
                    $this->error(Ibos::lang('Archivessplit drop table no empty error'));
                }
                $tableDriver['mainTable']::model()->dropTable($tableId);
                $tableDriver['bodyTable']::model()->dropTable($tableId);
                unset($tableInfo[$tableId]);
                //更新分表ID以及数据缓存
                ArchiveSplit::updateTableIds($tableDriver);
                Setting::model()->updateSettingValueByKey($tableDriver['tableInfo'], $tableInfo);
                Cache::save($tableDriver['tableInfo'], $tableInfo);
                // 更新setting表
                Cache::update(array('setting'));
                $this->success(Ibos::lang('Archivessplit drop table succeed'), $this->createUrl('split/index', array('op' => 'manage', 'mod' => $mod)));
            } else if ($operation == 'manage') { // 分表管理
                $data['tableInfo'] = $tableInfo;
                $data = array_merge($data, ArchiveSplit::getTableStatus($tableIds, $tableDriver));
                $this->render($mod . 'Manage', $data);
            } else if ($operation == 'addtable') { // 增加存档表
                if (empty($tableIds)) {
                    $maxTableId = 0;
                } else {
                    $maxTableId = max($tableIds);
                }
                // 创建表
                $tableDriver['mainTable']::model()->createTable($maxTableId + 1);
                $tableDriver['bodyTable']::model()->createTable($maxTableId + 1);
                // 更新分表ID
                ArchiveSplit::updateTableIds($tableDriver);
                Cache::update(array('setting'));
                $this->success(Ibos::lang('Archivessplit table create succeed'), $this->createUrl('split/index', array('op' => 'manage', 'mod' => $mod)));
            } else if ($operation == 'move') {
                // 必须先关闭系统才能进行移动操作
                if (Ibos::app()->setting->get('setting/appclosed') !== '1') {
                    $this->error(Ibos::lang('Archivessplit must be closed'), $this->createUrl('split/index', array('op' => 'manage', 'mod' => $mod)));
                }
                // 获取可以搜索的表
                $tableSelect = array();
                foreach ($tableIds as $tableId) {
                    $tableSelect[$tableId] = $tableDriver['mainTable']::model()->getTableName($tableId) . ' & ' . $tableDriver['bodyTable']::model()->getTableName($tableId);
                }
                $data['tableSelect'] = $tableSelect;
                $this->render($mod . 'Move', $data);
            }
        }
    }

}
