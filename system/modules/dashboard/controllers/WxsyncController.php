<?php

/**
 *
 * 微信绑定控制器2.0
 * 取消actionSync1.0（请找到过去的代码查看，这里已经删除）里先获取数据对比，然后同步的方式
 * 改换成每次ajax从数据库取若干条记录，“尝试”创建数据到企业号的方法
 * 支持超级~~~~~大的数据同步
 * 实际测试100条一批的一个ajax请求在微信那边会卡上10多秒，本身处理只要3秒，服务器配置好的话消耗都在微信了
 * 感谢Mr.Z的1.0版本，以及Mr.C的突发奇想，不然我也不会脑洞大开用这种奇葩方式重写2.0
 * 这里重点在于“尝试”：
 * 1、如果成功，然后没有然后了
 * 2、如果失败，返回信息是存在的话，那么本地直接绑定
 * 3、如果失败，其他不可容错的问题，如手机号12位，就在最后提示打印错误
 * 就是把本地对比过程交给微信（接口端），大数据化成小数据。把对比过程放在进度条的每次请求中，用户不会感到慢
 * 2.0的改进主要是为了针对性能而做出的改动，所以，这里所有的查询都不使用AR的方式
 * 暂时不封装这些DAO语句，尽管它们并没有在循环里，但它们本身带的数据比较大
 * 放在函数里，把大的数据传进传出反而不好
 *
 * @namespace application\modules\dashboard\controllers
 * @filename WxsyncController.php
 * @encoding UTF-8
 * @author 1.0 banyanCheung <banyan@ibos.com.cn>
 *              forsona
 *          2.0 forsona <2317216477@qq.com>
 * @link https://github.com/forsona
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016-6-4 14:13:41
 * @version $Id$
 */

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\core\utils\WebSite;
use application\modules\dashboard\utils\Wx;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CJSON;
use Exception;

class WxsyncController extends WxController
{

    /**
     * @var integer 每次从数据库里取的部门数目，默认100
     */
    const DEPT_NUM_PER = 100;

    /**
     * @var integer 每次从数据库里取的用户数目，默认100
     */
    const USER_NUM_PER = 100;

    /**
     * 获取企业号绑定视图
     */
    public function actionIndex()
    {
        if (false === $this->isBinding) {
            return $this->unbindRender();
        }
        if (false === $this->wxqyInfo['isLogin']) {

            return $this->redirect($this->createUrl('wxbinding/index'));
        }
        //获取已经同步的人员
        $userCount = Ibos::app()->db->createCommand()
            ->select('count(uid)')
            ->from('{{user}}')
            ->where(" `status` = '0' ")
            ->queryScalar();
        $total = Ibos::app()->db->createCommand()
            ->select('count(uid)')
            ->from('{{user_binding}}')
            ->where(" `app` = 'wxqy' ")
            ->queryScalar();
        $params = array(
            'bindCount' => $total,
            'localCount' => max($userCount - $total, 0),
            'wxCount' => 0,
            'unit' => Ibos::app()->setting->get('setting/unit'),
            'aeskey' => Ibos::app()->setting->get('setting/aeskey'),
            'wxqy' => array(
                'name' => $this->wxqyInfo['name'],
                'logo' => $this->wxqyInfo['logo'],
                'corpid' => $this->wxqyInfo['corpid'],
            ),
        );

        $this->render('index', $params);
    }

    /**
     * 获取还未同步到 IBOS 的微信企业号用户人数
     *
     * @return bool
     */
    public function actionGetwxcount()
    {
        $wxUsers = $this->getDeptUser();
        $total = Ibos::app()->db->createCommand()
            ->select('count(uid)')
            ->from('{{user_binding}}')
            ->where(" `app` = 'wxqy' ")
            ->queryScalar();
        $wxCount = max(count($wxUsers) - $total, 0);
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => array(
                'wxCount' => $wxCount,
                'wxuser' => $wxUsers,
            ),
        ));
    }


    /**
     * 同步部门人员2.0
     */

    public function actionSync()
    {
        set_time_limit(120);
        $op = Env::getRequest('op');
        if (!in_array($op, array(
            'init',
            'dept',
            'user',
            'wxdept',
            'wxuser',
            'sending'
        ))
        ) {
            $op = 'init';
        }
        return $this->{'handle' . ucfirst($op)}();
    }

    /**
     * 微信企业号同步初始化操作（第一步）：
     * 1. 获取未同同步到微信的部门和用户的个数；
     * 2. 获取微信部门数据；
     * 3. 获取 IBOS 和微信部门的关联关系；
     */
    private function handleInit()
    {
        // 优先检测微信是否授权部门，如果没有直接提示没有权限
        $return = WxApi::getInstance()->getDeptList();
        $id = 1;
        $wxDept = array();
        if (!empty($return['data'])) {
            if (!empty($return['data']['department'])) {
                $id = $return['data']['department'][0]['id'];
                $wxDept = $return['data']['department'];
            } else {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => '请授权至少一个部门的权限'
                ));
            }
        }
        // IBOS 中未绑定微信企业号的部门个数
        $deptCount = Ibos::app()->db->createCommand()
            ->select('count(deptid)')
            ->from('{{department}}')
            ->where($this->deptid_not_in_binding())
            ->queryScalar();
        // IBOS 中未绑定微信企业号的用户（正常状态，排除被禁用、被锁定的用户）个数
        $userCount = Ibos::app()->db->createCommand()
            ->select('count(uid)')
            ->from('{{user}}')
            ->where($this->uid_not_in_binding())
            ->andWhere(" `status` = :status ", array(':status' => User::USER_STATUS_NORMAL))
            ->queryScalar();
        $sendMail = Env::getRequest('status');
        $dept = Ibos::app()->db->createCommand()
            ->select('deptid,bindvalue')
            ->from('{{department_binding}}')
            ->where(" `app` = 'wxqy' ")
            ->queryAll();
        $deptRelated = array();
        // 键 => 值 ：IBOS 部门id => 微信部门id
        if (!empty($dept)) {
            foreach ($dept as $d) {
                $deptRelated[$d['deptid']] = $d['bindvalue'];
            }
        }
        $deptRelated[0] = 1;
        $wxqy = array(
            'sendmail' => $sendMail,
            'deptlevel' => 0, //部门分层，pid为0的，level为0，依此类推
            'deptcount' => $deptCount,
            'deptrelated' => $deptRelated,
            'error' => array(),
            'success' => array(),
            'successSending' => 0,
            'usercount' => $userCount,
            'id' => $id,
            'wxdept' => $wxDept,
            'wxdeptinit' => 1,
            'wxuserinit' => 1,
        );
        Ibos::app()->user->setState('wxqy', $wxqy);
        $ajaxReturn = array(
            'isSuccess' => true,
            'data' => array(
                'url' => $this->createUrl('wxsync/sync', array('op' => 'dept')),
                'deptCount' => $deptCount,
                'userCount' => $userCount,
            ),
            'msg' => '开始同步部门，请耐心等候...',
        );
        return $this->ajaxReturn($ajaxReturn);
    }


    /**
     * 同步 IBOS 部门到微信企业号中（第二步）
     */
    private function handleDept()
    {
        $wxqy = Ibos::app()->user->wxqy;
        $id = $wxqy['id'];
        $level = $wxqy['deptlevel'];
        $i = 10;
        while ($i) {
            //这个使用子查询的方式去遍历一棵树
            $deptPer = $this->getPerDept($level);
            if (!empty($deptPer)) {
                $wxqy['deptlevel'] = $level;
                break;
            } else {
                $i--;
                //当前层级找不到数据，则尝试着下一层级找部门
                $level++;
            }
        }
        //找了10层也没有就认为部门同步完成
        if (empty($deptPer)) {
            //下一层也找不到，则表示完成了
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => '同步部门完成。开始处理用户,请稍后..',
                'data' => array(
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'user'))
                )
            ));
        }
        //至少有一个部门才会进来这边
        $related = $wxqy['deptrelated'];
        $bindArray = array();
        $url = $this->createUrlByType('syncDept');
        foreach ($deptPer as $dept) {
            $wxqy['deptcount']--;
            if ($dept['pid'] == 0 || isset($related[$dept['pid']])) {
                $pid = $dept['pid'] == 0 ? $id : $related[$dept['pid']];
                $res = WxApi::getInstance()->createDept($dept['deptname'], $pid, $dept['sort'], $url);

                if ($res['isSuccess'] && isset($res['data']['id'])) {
                    $newId = $res['data']['id'];
                } else {
                    if (!$res['isSuccess']) {
                        return $this->ajaxReturn(array(
                            'isSuccess' => false,
                            'msg' => '部门同步失败，错误代码：' . $res['data']['errcode'] . '，错误原因：' . Code::getErrmsg($res['data']['errcode']
                                )
                        ));
                    } else {
                        //没有权限或者父部门错了，给授权的部门中的顶级部门
                        if (in_array($res['data']['errcode'], array('60004', '60011'))) {
                            $newId = $id;
                        }
                        //如果已经创建，解析返回信息里的部门id……这个，如果微信改了msg，那就gg了
                        if ($res['data']['errcode'] == '60008') {
                            if (preg_match_all('/\s(\\d+)\s/', $res['data']['errmsg'], $matches)) {
                                $newId = isset($matches[1]) ? $matches[1][0] : $id; //如果不给数字了。。。。
                            } else {
                                $newId = $id;
                            }
                        }
                    }
                }
                //成功，则创建【OA部门=>企业号部门】的对应关系
                $related[$dept['deptid']] = $newId;
                $bindArray[$dept['deptid']] = $newId;
            } else {
                //由于使用了部门分层的方式去查询部门
                //所以可以确保部门数据的顺序一定是按照从上到下的方式，这里正常是不会进来的
                //如果进来了，请修复这个bug
                //file_put_contents( 'wx_syncdept_continue.txt', var_export( $dept, true ), FILE_APPEND );
            }
        }
        $wxqy['deptrelated'] = $related;
        //这个count只是用来告诉用户还有多少个，别无他用
        Ibos::app()->user->setState('wxqy', $wxqy);
        $connection = Ibos::app()->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($bindArray as $oaDeptid => $wxDeptid) {
                $connection->schema->commandBuilder
                    ->createInsertCommand('{{department_binding}}', array(
                        'deptid' => $oaDeptid,
                        'bindvalue' => $wxDeptid,
                        'app' => 'wxqy'
                    ))
                    ->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            //$transaction->rollback();
        }

        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '正在同步部门，还剩下' . $wxqy['deptcount'] . '个',
            'data' => array(
                'url' => $this->createUrl('wxsync/sync', array('op' => 'dept')),
                'remain' => $wxqy['deptcount'],
            )
        ));
    }

    /**
     * 同步 IBOS 用户到微信企业号中（第三步）
     */
    private function handleUser()
    {
        $wxqy = Ibos::app()->user->wxqy;
        // 微信顶级部门 ID
        $wxTopDeptId = $wxqy['id'];
        $deptIdRelated = $wxqy['deptrelated'];
        $errorUidString = implode(',', array_keys($wxqy['error']));
        $errorUidCondition = !empty($errorUidString) ? " `uid` NOT IN ( {$errorUidString} )" : 1;
        $uidArr = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from('{{user}}')
            ->where($this->uid_not_in_binding())
            ->andWhere(" `status` = :status ", array(':status' => User::USER_STATUS_NORMAL))
            ->andWhere($errorUidCondition)
            ->order(" uid ASC ")
            ->limit(self::USER_NUM_PER)
            ->queryColumn();
        if (!empty($uidArr)) {
            $bindArray = array();
            //这个是需要绑定的用户UID
            $userArray = UserUtil::wrapUserInfo($uidArr, false);
            foreach ($userArray as $user) {
                $wxqy['usercount']--;
                $wxDeptIdArr = array();
                foreach (explode(',', $user['alldeptid']) as $deptid) {
                    if (isset($deptIdRelated[$deptid])) {
                        $wxDeptIdArr[] = $deptIdRelated[$deptid];
                    }
                }
                // 如果并没有找到部门关系，直接放在有权限的顶级部门下
                $deptIdStr = implode(',', $wxDeptIdArr);
                if (empty($deptIdStr)) {
                    $deptIdStr = $wxTopDeptId;
                }
                $user['deptid'] = $deptIdStr;
                $user['userid'] = $user['mobile'];
                $user['gender'] = $user['gender'] == 1 ? 0 : 1;

                //创建链接
                $url = $this->createUrlByType('syncUser');
                $res = WxApi::getInstance()->createUser($user, $url);
                if ($res !== '') {
                    //如果用户名已经存在，是不会返回错误的，因为存在的话直接绑定，此时res = ''
                    //返回值不是空，说明有错误信息，空经过了我的处理了呢
                    $wxqy['error'][$user['uid']] = array(
                        'msg' => $res,
                        'realname' => $user['realname'],
                    );
                } else {
                    //记录需要绑定的用户数据，等循环结束后面再绑定
                    //当然如果期间php挂了，则会出现下面的情况
                    //微信已经绑定成功，但是本地并没有建立绑定关系
                    //但是没关系，再点击同步的时候，依旧会把绑定表中没有的数据筛选出提交给微信
                    //看到这个if条件的另一半分支了没有，那个分支里返回空字符串
                    //会在我接收到微信返回“XX已存在”时立刻建立绑定，所以这里失败了也没关系
                    //唯一的缺陷就是，因为这种意外情况导致的本地没有建立绑定但是微信存在的用户
                    //不会通过下面的success数组发关注提醒
                    $bindArray[$user['uid']] = $user['userid'];
                    $wxqy['success'][$user['uid']] = $user['mobile'];
                }
            }
            Ibos::app()->user->setState('wxqy', $wxqy);
            if (!empty($bindArray)) {
                $connection = Ibos::app()->db;
                $transaction = $connection->beginTransaction();
                try {
                    foreach ($bindArray as $uid => $bindValue) {
                        $connection->schema->commandBuilder
                            ->createInsertCommand('{{user_binding}}', array(
                                'app' => 'wxqy',
                                'uid' => $uid,
                                'bindvalue' => $bindValue,
                            ))->execute();
                    }
                    $transaction->commit();
                } catch (Exception $e) {
                    Log::write(array('msg' => $e->getMessage(), 'trace' => $e->getTrace()));
                    //$transaction->rollback();
                }
            }
//          $userCount = Ibos::app()->db->createCommand()
//                  ->select( 'count(uid)' )
//                  ->from( '{{user}}' )
//                  ->where( $this->uid_not_in_binding() )
//                  ->andWhere( " `status` = 0 " )
//                  ->queryScalar();
//          $wxqy['usercount'] = $userCount;
            return $this->ajaxReturn(
                array(
                    'isSuccess' => true,
                    'msg' => '正在同步用户，还剩下' . $wxqy['usercount'] . '个，请稍后...',
                    'data' => array(
                        'url' => $this->createUrl('wxsync/sync', array('op' => 'user')),
                        'remain' => $wxqy['usercount']
                    )
                ));
        } else {
            return $this->ajaxReturn(
                array(
                    'isSuccess' => true,
                    'msg' => '正在初始化微信部门……',
                    'data' => array(
                        'url' => $this->createUrl('wxsync/sync', array('op' => 'wxdept')
                        ),
                    )
                ));
        }
    }

    /**
     * 同步微信企业号部门到 IBOS（第四步）
     */
    private function handleWxdept()
    {
        $wxqy = Ibos::app()->user->wxqy;
        $wxdept = $wxqy['wxdept'];
        $wxdeptinit = $wxqy['wxdeptinit'];
        if ($wxdeptinit == 1) {
            $wxqy['wxdeptinit'] = 0;
            $temp = array();
            foreach ($wxdept as &$dept) {
                $temp[$dept['id']] = $dept;
            }
            $array = array();
            //找不到的父id，就设置成0
            foreach ($temp as $deptid => $dept) {
                if (!isset($temp[$dept['parentid']])) {
                    $dept['parentid'] = 0;
                }
                $array[$dept['parentid']][$deptid] = $dept;
            }
            //重新排序wxdept数组
            $wxdept = array();
            $pidArray = array(0);
            while (1) {
                $temp = array();
                foreach ($pidArray as $pid) {
                    $deptByPid = isset($array[$pid]) ? $array[$pid] : array();
                    $wxdept += $deptByPid;
                    $temp = array_merge($temp, array_keys($deptByPid));
                    unset($array[$pid]);
                }
                $pidArray = $temp;
                if (empty($array)) {
                    break;
                }
            }
            $temp2 = array();
            foreach ($wxdept as $dept) {
                $temp2[$dept['id']] = $dept;
            }
            $wxqy['wxdept'] = $temp2;
            Ibos::app()->user->setState('wxqy', $wxqy);
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => '正在同步微信部门到IBOS……',
                'data' => array(
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'wxdept')
                    ),
                )
            ));
        }
        $deptList = Ibos::app()->db->createCommand()
            ->select('deptid,pid')
            ->from('{{department}}')
            ->queryAll();
        $deptListRelated = array();
        if (!empty($deptList)) {
            foreach ($deptList as $dept) {
                $deptListRelated[$dept['deptid']] = $dept['pid'];
            }
        }
        $related = $wxqy['deptrelated']; // deptid =>wxdeptid
        $deptidRelated = array_flip($related);
        set_time_limit(0);
        //在初始化的时候已经限制了，如果没有部门是会报错的，所以这里一定有部门
        foreach ($wxdept as $dept) {
            $pid = isset($deptidRelated[$dept['parentid']]) ? $deptidRelated[$dept['parentid']] : 0;
            //如果已经存在关联关系，说明已经绑定过了的
            if (isset($deptidRelated[$dept['id']])) {
                $deptid = $deptidRelated[$dept['id']];
                //如果绑定过了的部门记录里，pid的值不一样，则更新pid
                if ($pid != 0 && empty($deptListRelated[$deptid])) {
                    Ibos::app()->db->createCommand()
                        ->update('{{department}}', array(
                            'pid' => $pid,
                        ), " `deptid` = '{$deptid}' ");
                }
                continue;
            } else {
                if ($dept['id'] == 1) {
                    continue;
                }
                // 不存在绑定关系，则创建，先找pid，找不到就设置为0！
                Ibos::app()->db->createCommand()
                    ->insert('{{department}}', array(
                        'deptname' => $dept['name'],
                        'pid' => $pid,
                    ));
                $deptid = Ibos::app()->db->getLastInsertID();
                Ibos::app()->db->createCommand()
                    ->insert('{{department_binding}}', array(
                        'deptid' => $deptid,
                        'bindvalue' => $dept['id'],
                        'app' => 'wxqy'
                    ));
                $related[$deptid] = $dept['id'];
                $deptidRelated[$dept['id']] = $deptid;
            }
        }
        $wxqy['deptrelated'] = $related;
        Ibos::app()->user->setState('wxqy', $wxqy);
        return $this->ajaxReturn(
            array(
                'isSuccess' => true,
                'msg' => '正在初始化微信用户……',
                'data' => array(
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'wxuser')
                    ),
                )
            ));
    }

    /**
     * 同步微信企业号用户到 IBOS（第五步）
     */
    private function handleWxuser()
    {
        $wxqy = Ibos::app()->user->wxqy;
        //此时的部门列表已经被处理过了
        //有权限的最顶级的部门已经被设置为parentid = 0
        $wxUserInit = $wxqy['wxuserinit'];
        if ($wxUserInit == 1) {
            $wxqy['wxuserinit'] = 0;
            $wxDept = $wxqy['wxdept'];
            $topDept = array();
            foreach ($wxDept as $dept) {
                if ($dept['parentid'] == 0) {
                    $topDept[$dept['id']] = $dept;
                }
            }
            $wxqy['topdept'] = $topDept;
            Ibos::app()->user->setState('wxqy', $wxqy);
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => '正在同步微信用户到IBOS……',
                'data' => array(
                    'wxdept' => $wxDept,
                    'top' => $topDept,
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'wxuser')
                    ),
                )
            ));
        }
        $topDept = $wxqy['topdept'];
        $deptIdRelated = array_flip($wxqy['deptrelated']);
        $userBinding = Ibos::app()->db->createCommand()
            ->select('uid,bindvalue')
            ->from('{{user_binding}}')
            ->where(" `app` = 'wxqy' ")
            ->queryAll();
        $userRelated = array();
        foreach ($userBinding as $row) {
            $userRelated[$row['uid']] = $row['bindvalue'];
        }
        unset($userBinding);
        $ip = Ibos::app()->setting->get('clientip');
        if (!empty($topDept)) {
            $dept = array_shift($topDept);
            $wxqy['topdept'] = $topDept;
            Ibos::app()->user->setState('wxqy', $wxqy);
            $userArr = $this->getFullDeptUser($dept['id'], 1);
            if (!empty($userArr)) {
                foreach ($userArr as $user) {
                    if (!in_array($user['userid'], array_values($userRelated))) {
                        $findUid = 0;
                        $exist = false;
                        if (isset($user['mobile']) && !empty($user['mobile'])) {
                            $findUid = Ibos::app()->db->createCommand()
                                ->select('uid')
                                ->from('{{user}}')
                                ->where(" `mobile` = '{$user['mobile']}' ")
                                ->queryScalar();
                            !empty($findUid) && $exist = true;
                        } else {
                            // 原先规则：如果微信的用户没有手机号，那么就不能同步下来
                            // 现在改为：同步所有微信用户到 IBOS，无论有没有用户有没有设置手机号
                            // continue;
                        }
                        if (isset($user['weixinid'])) {
                            $findUid = Ibos::app()->db->createCommand()
                                ->select('uid')
                                ->from('{{user}}')
                                ->where(" `weixin` = '{$user['weixinid']}' ")
                                ->andWhere(" `status` != 0 ")
                                ->queryScalar();
                            !empty($findUid) && $exist = true;
                        }
                        if (isset($user['email'])) {
                            $findUid = Ibos::app()->db->createCommand()
                                ->select('uid')
                                ->from('{{user}}')
                                ->where(" `email` = '{$user['email']}' ")
                                ->andWhere(" `status` != 0 ")
                                ->queryScalar();
                            !empty($findUid) && $exist = true;
                        }
                        if (true === $exist && $findUid !== 0) {
                            $uid = $findUid;
                            Ibos::app()->db->createCommand()
                                ->update('{{user}}', array(
                                    'status' => 0,
                                ), " `uid` = '{$findUid}' ");
                        } else {
                            $salt = StringUtil::random(6);
                            $userMainDeptId = array_shift($user['department']);
                            Ibos::app()->db->createCommand()
                                ->insert('{{user}}', array(
                                    'username' => $user['userid'],
                                    'deptid' => $deptIdRelated[$userMainDeptId],
                                    'roleid' => 3, //普通成员
                                    'realname' => $user['name'],
                                    'password' => md5(md5($user['userid']) . $salt),
                                    'gender' => $user['gender'] == '1' ? 1 : 0, //IBOS的0女1男，微信的1男2女,0未知
                                    'weixin' => isset($user['weixinid']) ? $user['weixinid'] : '',
                                    'mobile' => isset($user['mobile']) ? $user['mobile'] : '',
                                    'email' => isset($user['email']) ? $user['email'] : '',
                                    'createtime' => TIMESTAMP,
                                    'salt' => $salt,
                                    'guid' => StringUtil::createGuid(),
                                ));
                            $uid = Ibos::app()->db->getLastInsertID();
                            Ibos::app()->db->createCommand()
                                ->insert('{{user_count}}', array(
                                    'uid' => $uid,
                                ));
                            Ibos::app()->db->createCommand()
                                ->insert('{{user_status}}', array(
                                    'uid' => $uid,
                                    'regip' => $ip,
                                    'lastip' => $ip,
                                ));
                            Ibos::app()->db->createCommand()
                                ->insert('{{user_profile}}', array(
                                    'uid' => $uid,
                                ));
                        }
                        $row = Ibos::app()->db->createCommand()
                            ->select()
                            ->from('{{user_binding}}')
                            ->where(" `uid` = '{$uid}' ")
                            ->queryRow();
                        if (empty($row)) {
                            Ibos::app()->db->createCommand()
                                ->insert('{{user_binding}}', array(
                                    'uid' => $uid,
                                    'bindvalue' => $user['userid'],
                                    'app' => 'wxqy',
                                ));
                        }
                        if (!empty($user['department'])) {
                            foreach ($user['department'] as $wxRelatedDeptid) {
                                Ibos::app()->db->createCommand()
                                    ->insert('{{department_related}}', array(
                                        'uid' => $uid,
                                        'deptid' => $deptIdRelated[$wxRelatedDeptid],
                                    ));
                            }
                        }
                    }
                }
            }
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => '正在同步微信用户到IBOS……',
                'data' => array(
                    'top' => $topDept,
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'wxuser')
                    ),
                )
            ));
        } else {
            UserUtil::CacheUser();
            Org::update();
            Cache::update('setting');
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => '正在发送邀请，请稍后……',
                'data' => array(
                    'url' => $this->createUrl('wxsync/sync', array('op' => 'sending')
                    )
                )
            ));
        }
    }

    /**
     * 发送邀请（第六步）
     */
    private function handleSending()
    {
        $wxqy = Ibos::app()->user->wxqy;
        $success = count($wxqy['success']);
        $error = count($wxqy['error']);
        $downloadLink = $this->createUrl('wxsync/downerror');
        $bindCount = Ibos::app()->db->createCommand()
            ->select('count(*)')
            ->from('{{user_binding}}')
            ->where(" `app` = 'wxqy' ")
            ->queryScalar();
        if (!empty($error) && empty($success)) {
            return $this->ajaxReturn(
                array(
                    'isSuccess' => true,
                    'msg' => '全部失败',
                    'data' => array(
                        'downUrl' => $downloadLink,
                        'errorCount' => $error,
                        'bindCount' => $bindCount,
                        'successCount' => 0,
                        'tpl' => 'error',
                    )
                ));
        }

        $userId = array_shift($wxqy['success']);
        if (!empty($userId)) {
            $wxqy['successSending'] += 1;
            $url = $this->createUrlByType('sendInvition');
            $res = WxApi::getInstance()->sendInvitation($url, $userId);
            Ibos::app()->user->setState('wxqy', $wxqy);
            //这里不需要管到底发送成功与否，因为……这个邀请一个星期发一次的。。。失败了也没辙
            return $this->ajaxReturn(
                array(
                    'isSuccess' => true,
                    'msg' => '正在发送邀请，请稍后..',
                    'data' => array(
                        'url' => $this->createUrl('wxsync/sync', array('op' => 'sending'))
                    )
                ));
        } else {
            if (!empty($error) && !empty($success)) {
                return $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'msg' => '成功一半。。',
                        'data' => array(
                            'successCount' => $wxqy['successSending'],
                            'errorCount' => $error,
                            'bindCount' => $bindCount,
                            'downUrl' => $downloadLink,
                            'tpl' => 'half',
                        )
                    ));
            } else {
                Ibos::app()->user->setState('wxqy', null);
                $ajaxReturn = $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'msg' => '成功全部完成！',
                        'data' => array(
                            'successCount' => $wxqy['successSending'],
                            'errorCount' => 0,
                            'bindCount' => $bindCount,
                            'tpl' => 'success',
                        )
                    ));
            }
        }
        return $ajaxReturn;
    }

    /**
     * 根据部门的层级创建查询的条件
     *
     * @param integer $level
     */
    private function createConditionByDeptLevel($level = 0)
    {
        $sqlString = Ibos::app()->db->createCommand()
            ->select('deptid')
            ->from('{{department}}')
            ->where(" `pid` IN ( <string> )")
            ->getText();
        $sql = $sqlString;
        while ($level--) {
            $sql = str_replace('<string>', $sqlString, $sql);
        }
        return str_replace('<string>', 0, $sql);
    }

    private function getPerDept($level)
    {
        $deptidCondition = $this->createConditionByDeptLevel($level);
        $return = Ibos::app()->db->createCommand()
            ->select('deptname,deptid,pid,sort')
            ->from('{{department}}')
            ->where(" `deptid` IN( {$deptidCondition} )")
            ->andWhere($this->deptid_not_in_binding())
            ->order('deptid ASC')
            ->limit(self::DEPT_NUM_PER)
            ->queryAll();
        return $return;
    }


    /**
     * 通过类型创建访问官网的url，以便官网调用微信接口
     *
     * @param string $type 对应官网访问微信接口的方法名
     * @return string url
     */
    private function createUrlByType($type)
    {
        $aeskey = Wx::getInstance()->getAeskey();
        $url = WebSite::getInstance()->build('Api/Wxsync/' . $type, array('aeskey' => $aeskey));
        return $url;
    }

    /**
     * 获取部门成员列表
     *
     * @return array 成员列表
     */
    private function getDeptUser($deptid = 1, $fetchChild = 1)
    {
        $url = $this->createUrlByType('syncDeptUserSimple');
        $wxUsers = WxApi::getInstance()->getDeptUser($url, $deptid, $fetchChild);
        return $wxUsers;
    }

    private function getFullDeptUser($deptid = 1, $fetchChild = 1)
    {
        $url = $this->createUrlByType('syncDeptUser');
        $wxUsers = WxApi::getInstance()->getDeptUser($url, $deptid, $fetchChild);
        return $wxUsers;
    }

    /**
     * 下载导入错误文件
     */
    public function actionDownerror()
    {
        $wxqy = Ibos::app()->user->wxqy;
        $error = $wxqy['error'];
        $header = array('uid', '真实姓名', '错误原因');
        $body = array();
        foreach ($error as $uid => $row) {
            $body[] = array($uid, iconv('utf-8', 'gbk', $row['realname']), iconv('utf-8', 'gbk', $row['msg']));
        }
        Convert::exportCsv('导入用户错误记录' . TIMESTAMP, $header, $body);
        Ibos::app()->user->setState('wxqy', null);
    }

    public function actionApp()
    {
        if (false === $this->wxqyInfo['isLogin']) {
            return $this->redirect($this->createUrl('wxbinding/index'));
        }
        $res = WebSite::getInstance()->fetch('Wxapi/Api/lists');
        $lists = CJSON::decode($res);
        $unit = Ibos::app()->setting->get('setting/unit');
        $aeskey = Ibos::app()->setting->get('setting/aeskey');
        $app = array();
        $existApp = array();
        foreach ($this->wxqyInfo['app'] as $row) {
            $existApp[] = $row['appid'];
        }
        foreach ($lists as $suiteid => $row) {
            $app[$suiteid] = array(
                'name' => $row['name'],
                'logo' => $row['logo'],
                'desc' => $row['desc'],
                'url' => WebSite::getInstance()->build('Wxapi/Api/toWx', array(
                    'state' => base64_encode(json_encode(array(
                        'domain' => $unit['systemurl'],
                        'uid' => $this->wxqyInfo['uid'],
                        'aeskey' => $aeskey,
                        'version' => strtolower(implode(',', array(
                            ENGINE,
                            VERSION,
                            VERSION_TYPE
                        )))
                    ))),
                    'id' => $suiteid
                )),
            );
            if (!empty($row['app'])) {
                foreach ($row['app'] as $appflag => $value) {
                    if ($value['enable']) {
                        $app[$suiteid]['app'][] = array(
                            'img' => 'http://www.ibos.com.cn/Wxapi/image/' . $value['appid'] . '/' . $suiteid,
                            'name' => $value['name'],
                            'desc' => $value['description'],
                            'exist' => in_array($value['appid'], $existApp),
                        );
                    }
                }
            }
        }
        $param = array(
            'list' => $app,
        );
        return $this->render('applist', $param);
    }

    private function deptid_not_in_binding()
    {
        return " `deptid` NOT IN ( SELECT `deptid` FROM {{department_binding}} WHERE `app` = 'wxqy' ) ";
    }

    private function uid_not_in_binding()
    {
        return " `uid` NOT IN ( SELECT `uid` FROM {{user_binding}} WHERE `app` = 'wxqy' ) ";
    }
}
