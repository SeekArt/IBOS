<?php

/**
 * 文件柜后台控制器
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * @package application.modules.file.controllers
 * @version $Id: DashboardController.php 2920 2014-03-25 08:36:13Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Cloud;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\controllers\BaseController;
use application\modules\file\model\File as FileModel;
use application\modules\file\model\FileCapacity;
use application\modules\file\model\FileCloudSet;
use application\modules\file\model\FileTrash;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;
use application\modules\main\model\Setting;
use application\modules\main\utils\Main;
use application\modules\user\model\User;
use CHtml;
use CJSON;

class DashboardController extends BaseController
{

    /**
     * 搜索条件
     * @var string
     */
    private $_condition = '';

    const DISK_INFO_ROUTE = 'Api/Disk/GetDiskInfo'; // 获取云盘信息地址

    /**
     * 网盘设置
     * @return void
     */

    public function actionIndex()
    {
        if (Env::submitCheck('formhash')) {
            // 更新setting表filedefsize和filecompmanager字段
            $setting['filedefsize'] = intval(Env::getRequest('filedefsize'));
            $manager = Env::getRequest('filecompmanager');
            $setting['filecompmanager'] = serialize(StringUtil::handleSelectBoxData($manager));
            foreach ($setting as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }
            CacheUtil::update('setting');
            // 指定容量设置放到file_capacity表单独存储
            FileCapacity::model()->deleteAll();
            if (isset($_POST['role'])) {
                foreach ($_POST['role'] as $k => $v) {
                    $size = intval($v['size']);
                    if (!empty($size) && !empty($v['mem'])) {
                        $data['size'] = $size;
                        $data['addtime'] = TIMESTAMP;
                        $mem = StringUtil::handleSelectBoxData($v['mem']);
                        $data['deptids'] = $mem['deptid'];
                        $data['posids'] = $mem['positionid'];
                        $data['roleids'] = $mem['roleid'];
                        $data['uids'] = $mem['uid'];
                        FileCapacity::model()->add($data);
                    }
                }
            }
            $this->success(Ibos::lang('Operation succeed', 'message'));
        } else {
            $setting['filedefsize'] = Ibos::app()->setting->get('setting/filedefsize');
            $setting['filecompmanager'] = Ibos::app()->setting->get('setting/filecompmanager');
            if (!empty($setting['filecompmanager'])) {
                $manager = $setting['filecompmanager'];
                $roleids = isset($manager['roleid']) ? $manager['roleid'] : null; //为了兼容之前没有角色的代码
                $setting['filecompmanager'] = StringUtil::joinSelectBoxValue($manager['deptid'], $manager['positionid'], $manager['uid'], $roleids);
            }
            $capacity = FileCapacity::model()->fetchAll(array("order" => "`addtime` DESC"));
            foreach ($capacity as $k => $v) {
                $capacity[$k]['mem'] = StringUtil::joinSelectBoxValue($v['deptids'], $v['posids'], $v['uids'], $v['roleids']);
            }
            $setting['filecapasity'] = $capacity;
            $params = array(
                'lang' => Ibos::getLangSource('file.default'),
                'setting' => $setting
            );
            $this->render('setup', $params);
        }
    }

    /**
     * 存储设置
     */
    public function actionStore()
    {
        if (Env::submitCheck('formhash')) {
            $isopen = Ibos::app()->setting->get('setting/filecloudopen');
            $cloudid = Ibos::app()->setting->get('setting/filecloudid');
            if (isset($_POST['filecloudopen'])) { // 开通
                if (!$this->checkIbosCloudOpen()) {
                    $this->error(Ibos::lang('Ibos cloud did not open'));
                }
                $rs = Cloud::getInstance()->fetch(self::DISK_INFO_ROUTE);
                if (!is_array($rs)) {
                    $rs = CJSON::decode($rs, true);
                    if ($rs['ret'] == '1') {
                        $set = $rs['data'][0];
                        $cloud = array(
                            'server' => $set['server'],
                            'keyid' => $set['keyid'],
                            'keysecret' => $set['keysecret'],
                            'endpoint' => $set['endpoint'],
                            'bucket' => $set['bucket'],
                            'isopen' => 1
                        );
                        $old = FileCloudSet::model()->fetchByAttributes(array('id' => $cloudid));
                        if (empty($cloudid) || empty($old)) {
                            $newId = FileCloudSet::model()->add($cloud, true);
                            Setting::model()->updateSettingValueByKey('filecloudid', $newId);
                        } else {
                            FileCloudSet::model()->updateByPk($cloudid, $cloud);
                        }
                        Setting::model()->updateSettingValueByKey('filecloudopen', 1);
                        CacheUtil::update('setting');
                        $this->success($rs['msg']);
                    } else {
                        $this->error($rs['msg']);
                    }
                } else {
                    $this->error(Ibos::lang('Cloud comm error'));
                }
            } else {
                Setting::model()->updateSettingValueByKey('filecloudopen', 0);
                CacheUtil::update('setting');
                $this->success(Ibos::lang('Cloud close succeed'));
            }
        } else {
            $iboscloudopen = $this->checkIbosCloudOpen();
            $params = array(
                'filecloudopen' => Ibos::app()->setting->get('setting/filecloudopen'),
                'iboscloudopen' => $iboscloudopen,
            );
            $this->render('store', $params);
        }
    }

    /**
     * 检查是否开通云服务
     */
    private function checkIbosCloudOpen()
    {
        $setting = Ibos::app()->setting->get('setting/iboscloud');
        if ($setting['isopen'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * 回收站管理
     */
    public function actionTrash()
    {
        if (Env::submitCheck('formhash')) {
            $op = Env::getRequest('op');
            if (in_array($op, array('restore', 'del', 'setEmpty'))) {
                $res = $this->$op();
                $this->ajaxReturn(array('isSuccess' => 'true', 'msg' => Ibos::lang('Operation succeed', 'message')));
            }
        } else {
            $search = false;
            if (Env::getRequest('param') == 'search') {
                $this->search();
                $search = true;
            }
            $this->_condition = FileData::joinCondition($this->_condition, "f.isdel=1");
            $size = Ibos::app()->db->createCommand()
                ->select("sum(f.size)")
                ->from("{{file}} f")
                ->where($this->_condition)
                ->queryScalar();
            $list = FileTrash::model()->fetchList($this->_condition);
            $datas = $this->handleTrashList($list['datas']);
            $params = array(
                'size' => Convert::sizeCount(intval($size)),
                'count' => $list['count'],
                'search' => $search,
                'datas' => $datas,
                'pages' => $list['pages'],
                'lang' => Ibos::getLangSource('file.default')
            );
            $this->render('trash', $params);
        }
    }

    /**
     * 搜索
     * @return void
     */
    private function search()
    {
        $conditionCookie = Main::getCookie('condition');
        if (empty($conditionCookie)) {
            Main::setCookie('condition', $this->_condition, 10 * 60);
        }
        if (Env::getRequest('type') == 'normal_search') {
            $keyword = CHtml::encode($_POST['keyword']);
            $users = User::model()->fetchAll("`realname` LIKE '%{$keyword}%'");
            $uids = implode(',', Convert::getSubByKey($users, 'uid'));
            $this->_condition = "f.name LIKE '%{$keyword}%' OR FIND_IN_SET(f.uid, '{$uids}')";
            Main::setCookie('keyword', $keyword, 10 * 60);
        } else {
            $this->_condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ($this->_condition != Main::getCookie('condition')) {
            Main::setCookie('condition', $this->_condition, 10 * 60);
        }
    }

    /**
     * 处理回收站显示数据
     * @param type $list
     */
    protected function handleTrashList($list)
    {
        foreach ($list as $k => $file) {
            $list[$k]['realname'] = User::model()->fetchRealnameByUid($file['uid']);
            if ($list[$k]['type'] == 1) { // 文件夹的话计算出所有子文件大小
                $list[$k]['size'] = FileModel::model()->countSizeByFid($file['fid']);
            }
            $list[$k]['location'] = $file['belong'] == 0 ? Ibos::lang('Personal folder') : Ibos::lang('Company folder');
            $parents = FileOffice::getParentsByIdPath($file['idpath']);
            foreach ($parents as $p) {
                $list[$k]['location'] .= " \ " . $p['name'];
            }
            $list[$k]['size'] = Convert::sizeCount(intval($list[$k]['size']));
            $list[$k]['delDate'] = date('Y/m/d H:i', $file['deltime']);
        }
        return $list;
    }

    /**
     * 从回收站还原
     */
    protected function restore()
    {
        $fids = Env::getRequest('fids');
        $res = FileTrash::model()->restore($fids);
        return $res;
    }

    /**
     * 彻底删除
     */
    protected function del()
    {
        $fids = Env::getRequest('fids');
        $res = FileTrash::model()->fully($fids);
        return $res;
    }

    /**
     * 清空
     */
    protected function setEmpty()
    {
        $dels = FileTrash::model()->fetchAll();
        $fids = Convert::getSubByKey($dels, 'fid');
        $res = FileTrash::model()->fully($fids);
        return $res;
    }

}
