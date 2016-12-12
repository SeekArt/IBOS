<?php

/**
 * 系统进程处理组件。其作用是进行某一项耗时操作时，先看看当前进程有没有该操作，
 * 可以避免使用冲突
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$
 * @package application.modules.main.components
 */

namespace application\modules\main\components;

use application\core\utils\Cache;
use application\modules\main\model\Process as ProcesModel;
use CApplicationComponent;

class Process extends CApplicationComponent
{

    /**
     * 返回一个进程是否已经存在或锁定
     * @param string $process 进程名字
     * @param interger $ttl 持续时间
     * @return mixed
     */
    public function isLocked($process, $ttl = 0)
    {
        $ttl = $ttl < 1 ? 600 : intval($ttl);
        return $this->status('get', $process) || $this->find($process, $ttl);
    }

    /**
     * 解除一个进程的锁定
     * @param string $process 进程名字
     */
    public function unLock($process)
    {
        $this->status('rm', $process);
        $this->cmd('rm', $process);
    }

    /**
     * 进程状态处理
     * @staticvar array $processList 进程列表
     * @param string $action 状态命令
     * @param string $process 进程名字
     * @return mixed
     */
    private function status($action, $process)
    {
        static $processList = array();
        switch ($action) {
            case 'set' :
                $processList[$process] = true;
                break;
            case 'get' :
                return !empty($processList[$process]);
                break;
            case 'rm' :
                $processList[$process] = null;
                break;
            case 'clear' :
                $processList = array();
                break;
        }
        return true;
    }

    /**
     * 查找并设置一个进程
     * @param string $name 进程名字
     * @param interger $ttl 持续时间
     * @return boolean
     */
    private function find($name, $ttl = 0)
    {
        if (!$this->cmd('get', $name)) {
            $this->cmd('set', $name, $ttl);
            $ret = false;
        } else {
            $ret = true;
        }
        $this->status('set', $name);
        return $ret;
    }

    /**
     * 进程处理命令
     * @staticvar mixed $allowcache 允许缓存标识符
     * @param string $cmd 命令
     * @param string $name 进程名字
     * @param interger $ttl 持续时间
     * @return mixed
     */
    private function cmd($cmd, $name, $ttl = 0)
    {
        static $allowcache;
        if ($allowcache === null) {
            $cc = Cache::check();
            $allowcache = $cc == 'mem' || $cc == 'redis';
        }
        if ($allowcache) {
            return $this->processCmdCache($cmd, $name, $ttl);
        } else {
            return $this->processCmdDb($cmd, $name, $ttl);
        }
    }

    /**
     * 处理缓存命令
     * @param string $cmd 命令
     * @param string $name 进程名字
     * @param interger $ttl 持续时间
     * @return mixed
     * @access private
     */
    private function processCmdCache($cmd, $name, $ttl = 0)
    {
        $ret = '';
        switch ($cmd) {
            case 'set' :
                $ret = Cache::set('process_lock_' . $name, TIMESTAMP, $ttl);
                break;
            case 'get' :
                $ret = Cache::get('process_lock_' . $name);
                break;
            case 'rm' :
                $ret = Cache::rm('process_lock_' . $name);
        }
        return $ret;
    }

    /**
     * 处理数据库命令
     * @param string $cmd 命令
     * @param string $name 进程名字
     * @param interger $ttl 持续时间
     * @return mixed
     * @access private
     */
    private function processCmdDb($cmd, $name, $ttl = 0)
    {
        $ret = '';
        switch ($cmd) {
            case 'set':
                $ret = ProcesModel::model()->add(array('processid' => $name, 'expiry' => (TIMESTAMP + $ttl)));
                break;
            case 'get':
                $ret = ProcesModel::model()->find("processid = '{$name}'");
                if (empty($ret) || $ret['expiry'] < TIMESTAMP) {
                    $ret = false;
                } else {
                    $ret = true;
                }
                break;
            case 'rm':
                $ret = ProcesModel::model()->deleteProcess($name, TIMESTAMP);
                break;
        }
        return $ret;
    }

}
