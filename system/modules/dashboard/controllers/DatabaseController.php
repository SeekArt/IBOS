<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;
use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\core\utils\Database;

class DatabaseController extends BaseController
{

    /**
     * 必须是本地引擎才能进行此操作
     */
    public function init()
    {
        parent::init();
        if (!LOCAL) {
            die(Ibos::lang('Not compatible service', 'message'));
        }
    }

    /**
     * 备份
     */
    public function actionBackup()
    {
        $formSubmit = Env::submitCheck('dbSubmit');
        $type = $msg = $url = '';
        $param = array();
        if ($formSubmit) {
            $status = Database::databaseBackup();
            extract($status);
            $type = '';
            if (!empty($type)) {
                $this->$type($msg, $url, $param);
            } else {
                $this->success(empty($msg) ? '备份出了点问题，请重试' : $msg, $url, $param);
            }
        } else {
            $data = array();
            $tablePrefix = Ibos::app()->setting->get('config/db/tableprefix');
            // 多卷备份重复执行操作
            if (Env::getRequest('setup') == '1') {
                $status = Database::databaseBackup();
                extract($status);
                $type = '';
                if (!empty($type)) {
                    $this->$type($msg, $url, $param);
                } else {
                    $this->success(empty($msg) ? '备份出了点问题，请重试' : $msg, $url, $param);
                }
            }
            $data['defaultFileName'] = date('Y-m-d') . '_' . StringUtil::random(8);
            $data['tables'] = Database::getTablelist($tablePrefix);
            $this->render('backup', $data);
        }
    }

    /**
     * 恢复备份
     */
    public function actionRestore()
    {
        $formSubmit = Env::submitCheck('dbSubmit');
        if ($formSubmit) {
            $backupDir = Database::getBackupDir();
            if (is_array($_POST['key'])) {
                foreach ($_POST['key'] as $fileName) {
                    $filePath = $backupDir . '/' . str_replace(array('/', '\\'), '', $fileName);
                    if (is_file($filePath)) {  // zip
                        @unlink($filePath);
                    } else { // sql
                        $i = 1;
                        while (1) {
                            $filePath = $backupDir . '/' . str_replace(array('/', '\\'), '', $fileName . '-' . $i . '.sql');
                            if (is_file($filePath)) {
                                @unlink($filePath);
                                $i++;
                            } else {
                                break;
                            }
                        }
                    }
                }
                $this->success(Ibos::lang('Database file delete succeed'));
            }
        } else {
            $this->render('restore', array('list' => Database::getBackupList()));
        }
    }

    /**
     * 优化数据表
     */
    public function actionOptimize()
    {
        $formSubmit = Env::submitCheck('dbSubmit');
        if ($formSubmit) {
            $tables = $_POST['optimizeTables'];
            if (!empty($tables)) {
                Database::optimize($tables);
            }
            $this->success(Ibos::lang('Operation succeed', 'message'));
        } else {
            $list = Database::getOptimizeTable();
            $totalSize = 0;
            foreach ($list as $table) {
                $totalSize += $table['Data_length'] + $table['Index_length'];
            }
            $data['list'] = $list;
            $data['totalSize'] = $totalSize;
            $this->render('optimize', $data);
        }
    }

}
