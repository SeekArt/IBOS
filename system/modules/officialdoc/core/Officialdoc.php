<?php

/**
 * 公文模块------公文组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 *  公文模块------公文组件类
 * @package application.modules.officialDoc.model
 * @version $Id: ICOfficialdoc.php 66 2013-09-13 08:40:50Z 36700438@qq.com $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\core;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Approval;
use application\modules\department\model\Department;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\officialdoc\model\Officialdoc as Doc;
use application\modules\officialdoc\model\OfficialdocApproval;
use application\modules\officialdoc\model\OfficialdocBack;
use application\modules\officialdoc\model\OfficialdocCategory;
use application\modules\officialdoc\model\OfficialdocReader;
use application\modules\officialdoc\utils\Officialdoc as OfficialdocUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CPagination;

class Officialdoc {

    // 管理权限
    const NO_PERMISSION = 0; // 无权限
    const ONLY_SELF = 1; //本人
    const CONTAIN_SUB = 2; //本人及下属
    const SELF_BRANCH = 4; // 当前分支机构
    const All_PERMISSION = 8; // 全部

    /**
     * 所有的字段属性数组
     * @var array
     * @access private
     */

    private $attributes = array();

    /**
     * 构造方法
     * @param integer $docid 文章id
     * @return mixed
     */
    public function __construct( $docid ) {
        $officialDoc = Doc::model()->fetchByPk( $docid );
        if ( !empty( $officialDoc ) ) {
            $this->attributes = $officialDoc;
            //取得签收信息
            $this->attributes['issign'] = OfficialdocReader::model()->fetchSignByDocid( $docid, IBOS::app()->user->uid );
        }
    }

    /**
     * 当没有set$Name方法时，设置对应$name的属性值
     * @param string $name
     * @param string $value
     */
    public function __set( $name, $value ) {
        isset( $this->attributes[$name] ) && $this->attributes[$name] = $value;
    }

    /**
     * 当没有set$Name方法时，获取对应$name的属性值
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        return isset( $this->attributes[$name] ) ? $this->attributes[$name] : null;
    }

    /**
     * 获取所有字段（属性）值
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * 设置公文状态
     * @param string $value
     */
    public function setStatus( $value ) {
        $this->attributes['status'] = $value;
    }

    /**
     * 获取公文状态
     * @return string
     */
    public function getStatus() {
        return $this->attributes['status'];
    }

    /**
     * 获取打印对象
     * @todo 为实现
     */
    public function getPrint() {

    }

    /**
     * 对查看页数据进行相应处理，方便页面输出显示
     * @param array $data
     * @return array $data
     */
    public static function getShowData( $data ) {
        $data["subject"] = stripslashes( $data["subject"] );
        $data['showVersion'] = OfficialdocUtil::changeVersion( $data['version'] );

        $departments = DepartmentUtil::loadDepartment();
        $positions = PositionUtil::loadPosition();

        if ( $data['approver'] != 0 ) {
            $data['approver'] = User::model()->fetchRealnameByUid( $data['approver'] );
        } else {
            $data['approver'] = IBOS::lang( 'None' );
        }

        $data['addtime'] = Convert::formatDate( $data['addtime'], 'u' );
        if ( !empty( $data['uptime'] ) ) {
            $data['uptime'] = Convert::formatDate( $data['uptime'], 'u' );
        }
        $data['categoryName'] = OfficialdocCategory::model()->fetchCateNameByCatid( $data['catid'] );

        //发布范围
        if ( empty( $data['deptid'] ) && empty( $data['positionid'] ) && empty( $data['uid'] ) ) {
            $data['departmentNames'] = IBOS::lang( 'All' );
            $data['positionNames'] = $data['uidNames'] = '';
        } else if ( $data['deptid'] == 'alldept' ) {
            $data['departmentNames'] = IBOS::lang( 'All' );
            $data['positionNames'] = $data['uidNames'] = '';
        } else {
            //取得部门名称集以、号分隔
            $department = DepartmentUtil::loadDepartment();
            $data['departmentNames'] = OfficialdocUtil::joinStringByArray( $data['deptid'], $department, 'deptname', '、' );
            //取得职位名称集以、号分隔
            $position = PositionUtil::loadPosition();
            $data['positionNames'] = OfficialdocUtil::joinStringByArray( $data['positionid'], $position, 'posname', '、' );

            //取得阅读范围人员名称集以、号分隔
            if ( !empty( $data['uid'] ) ) {
                $users = User::model()->fetchAllByUids( explode( ",", $data['uid'] ) );
                $data['uidNames'] = OfficialdocUtil::joinStringByArray( $data['uid'], $users, 'realname', '、' );
            } else {
                $data['uidNames'] = "";
            }
        }
        //抄送

        if ( empty( $data['ccdeptid'] ) && empty( $data['ccpositionid'] ) && empty( $data['ccuid'] ) ) {
            $data['ccDepartmentNames'] = ''; //Ibos::lang( 'All' );
            $data['ccPositionNames'] = $data['ccUidNames'] = '';
        } else if ( $data['ccdeptid'] == 'alldept' ) {
            $data['ccDepartmentNames'] = IBOS::lang( 'All' );
            $data['ccPositionNames'] = $data['ccUidNames'] = '';
        } else {
            //取得部门名称集以、号分隔
            $department = DepartmentUtil::loadDepartment();
            $data['ccDepartmentNames'] = OfficialdocUtil::joinStringByArray( $data['ccdeptid'], $department, 'deptname', '、' );
            //取得职位名称集以、号分隔
            $position = PositionUtil::loadPosition();
            $data['ccPositionNames'] = OfficialdocUtil::joinStringByArray( $data['ccpositionid'], $position, 'posname', '、' );

            //取得阅读范围人员名称集以、号分隔
            if ( !empty( $data['ccuid'] ) ) {
                $users = User::model()->fetchAllByUids( explode( ",", $data['ccuid'] ) );
                $data['ccUidNames'] = OfficialdocUtil::joinStringByArray( $data['ccuid'], $users, 'realname', '、' );
            } else {
                $data['ccUidNames'] = "";
            }
        }
        return $data;
    }

    /**
     * 对列表原始数据进行处理，方便渲染视图显示
     * @param array $datas
     * @return array $listArr
     * @todo 有html代码
     */
    public static function getListDatas( $datas ) {
        $datas = self::handlePurv( $datas );
        $uidArray = array();
        foreach ( $datas as $data ) {
            $uidArray[] = $data['author'];
        }
        $realnameArray = User::model()->findRealnameIndexByUid( $uidArray );
        $listDatas = array();
        $uid = IBOS::app()->user->uid;
        $checkTime = 3 * 86400;
        // 所有已读新闻id
        $readDocIds = OfficialdocReader::model()->fetchReadArtIdsByUid( $uid );
        $signedDocIds = OfficialdocReader::model()->fetchSignArtIdsByUid( $uid );
        foreach ( $datas as $data ) {
            $data['subject'] = StringUtil::cutStr( $data['subject'], 50 );
            // 1:已读；-1:未读
            $data['readStatus'] = in_array( $data['docid'], $readDocIds ) ? 1 : -1;
            // 三天内为新文章
            if ( $data['readStatus'] === -1 && $data['uptime'] > TIMESTAMP - $checkTime ) {
                $data['readStatus'] = 2;
            }
            //签收数
            $data['signNum'] = OfficialdocReader::model()->count( "issign = 1 AND docid = {$data['docid']}" );
            // 签收状态
            $data['signStatus'] = in_array( $data['docid'], $signedDocIds ) ? 1 : 0;
            $data['author'] = !empty( $realnameArray[$data['author']] ) ? $realnameArray[$data['author']] : '--';
            $data['uptime'] = empty( $data['uptime'] ) ? $data['addtime'] : $data['uptime'];
            $data['uptime'] = Convert::formatDate( $data['uptime'], 'u' );
            $keyword = Env::getRequest( 'keyword' );
            if ( !empty( $keyword ) ) {
                // 搜索关键字变红
                $data['subject'] = preg_replace( "|({$keyword})|i", "<span style='color:red'>\$1</span>", $data['subject'] );
            }
            if ( $data['ishighlight'] == '1' ) {
                // 有高亮,形式为bolc,color,in,undefinline
                $highLightStyle = $data['highlightstyle'];
                $hiddenInput = "<input type='hidden' id='{$data['docid']}_hlstyle' value='$highLightStyle'/>";
                $data['subject'] .= $hiddenInput;
                $highLightStyleArr = explode( ',', $highLightStyle );
                // 字体颜色
                $color = $highLightStyleArr[1];
                // 字体加粗
                $isB = $highLightStyleArr[0];
                // 字体倾斜
                $isI = $highLightStyleArr[2];
                // 字体下划线
                $isU = $highLightStyleArr[3];
                $isB && $data['subject'] = "<b>{$data['subject']}</b>";
                $isU && $data['subject'] = "<u>{$data['subject']}</u>";
                $fontStyle = '';
                $color != '' && $fontStyle .= "color:{$color};";
                $isI && $fontStyle .= "font-style:italic;";
                $fontStyle != '' && $data['subject'] = "<font style='{$fontStyle}'>{$data['subject']}</font>";
            }
            $listDatas[] = $data;
        }
        return $listDatas;
    }

    /**
     * 处理未审核列表的审核流程数据
     * @param array $datas 要处理的未审核公文
     * @return array
     */
    public static function handleApproval( $datas ) {
        $allApprovals = Approval::model()->fetchAllSortByPk( 'id' ); // 所有审批流程
        $allCategorys = OfficialdocCategory::model()->fetchAllSortByPk( 'catid' ); // 所有公文分类
        $docApprovals = OfficialdocApproval::model()->fetchAllGroupByDocId(); // 已走审批的公文
        $backDocIds = OfficialdocBack::model()->fetchAllBackDocId();
        foreach ( $datas as &$doc ) {
            $doc['back'] = in_array( $doc['docid'], $backDocIds ) ? 1 : 0;
            $doc['approval'] = $doc['approvalStep'] = array();
            $catid = $doc['catid'];
            if ( !empty( $allCategorys[$catid]['aid'] ) ) { // 审批流程不为空
                $aid = $allCategorys[$catid]['aid'];
                if ( !empty( $allApprovals[$aid] ) ) {
                    $doc['approval'] = $allApprovals[$aid];
                }
            }
            if ( !empty( $doc['approval'] ) ) {
                $doc['approvalName'] = !empty( $doc['approval'] ) ? $doc['approval']['name'] : ''; // 审批流程名称
                $doc['docApproval'] = isset( $docApprovals[$doc['docid']] ) ? $docApprovals[$doc['docid']] : array(); // 某篇公文的审批步骤记录
                $doc['stepNum'] = count( $doc['docApproval'] ); // 共审核了几步
                $step = array();
                foreach ( $doc['docApproval'] as $docApproval ) {
                    $step[$docApproval['step']] = User::model()->fetchRealnameByUid( $docApproval['uid'] ); // 步骤=>审核人名称 格式
                }
                for ( $i = 1; $i <= $doc['approval']['level']; $i++ ) {
                    if ( $i <= $doc['stepNum'] ) { // 如果已走审批步骤，找审批的人的名称， 否则找应该审核的人
                        $doc['approval'][$i]['approvaler'] = isset( $step[$i] ) ? $step[$i] : '未知'; // 容错
                    } else {
                        $levelName = Approval::model()->getLevelNameByStep( $i );
                        $approvalUids = $doc['approval'][$levelName];
                        $doc['approval'][$i]['approvaler'] = User::model()->fetchRealnamesByUids( $approvalUids, '、' );
                    }
                }
            }
        }
        return $datas;
    }

    /**
     * 设置分页
     * @param string $content 内容
     * @param integer $pageSize 分页基数
     * @return \CPagination
     */
    public static function setPages( $content, $pageSize, $page ) {
        $contentLength = strlen( $content );
        $pageCount = ceil( $contentLength / $pageSize );
        $pages = new CPagination( $pageCount );
        $pages->setPageSize( $pageSize );
        $pages->setCurrentPage( 0 );
        $data = array();
        for ( $i = 0; $i < $pageCount; $i++ ) {
            $data[$i] = substr( $content, $i * $pageSize, ($i + 1) * $pageSize );
        }

        return array( 'data' => $data[$page - 1], 'pages' => $pages );
    }

    protected function handleShowData( $data ) {
        foreach ( $data as $k => $approval ) {
            for ( $level = 1; $level <= $approval['level']; $level++ ) {
                $field = "level{$level}";
                $data[$k]['levels'][$field] = $this->getShowNames( $approval[$field] );
                $data[$k]['levels'][$field]['levelClass'] = $this->getShowLevelClass( $field );
            }
            $data[$k]['free'] = $this->getShowNames( $approval['free'] );
            $data[$k]['free']['levelClass'] = $this->getShowLevelClass( 'free' );
        }
        return $data;
    }

    /**
     * 处理编辑、删除权限
     * @param array $list 文章数据
     * @return array
     */
    private static function handlePurv( $list ) {
        if ( empty( $list ) ) {
            return $list;
        }
        if ( IBOS::app()->user->isadministrator ) {
            $list = self::grantPermission( $list, 1, self::getAllowType( 'edit' ) );
            return self::grantPermission( $list, 1, self::getAllowType( 'del' ) );
        }
        $uid = IBOS::app()->user->uid;
        $user = User::model()->fetchByUid( $uid );
        // 编辑、删除权限，取主岗位和辅助岗位权限最大值
        $editPurv = RoleUtil::getMaxPurv( $uid, 'officialdoc/manager/edit' );
        $delPurv = RoleUtil::getMaxPurv( $uid, 'officialdoc/manager/del' );
        $list = self::handlePermission( $user, $list, $editPurv, 'edit' );
        $list = self::handlePermission( $user, $list, $delPurv, 'del' );
        return $list;
    }

    /**
     * 获取允许类型字符串
     * @param string $type edit、del
     * @return string allowEdit、allowDel
     */
    private static function getAllowType( $type ) {
        return 'allow' . ucfirst( $type );
    }

    /**
     * 处理是否有编辑/删除权限
     * @param array $user 登陆者用户数组
     * @param array $list 要处理的新闻数组
     * @param integer $purv 岗位对应权限数值
     * @param string $type 类型（编辑：edit，删除：del）
     * @return array
     */
    private static function handlePermission( $user, $list, $purv, $type ) {
        $uid = $user['uid'];
        $allowType = self::getAllowType( $type );
        switch ( $purv ) {
            // 没权限
            case self::NO_PERMISSION:
                $list = self::grantPermission( $list, 0, $allowType );
                break;
            // 只是自己
            case self::ONLY_SELF:
                foreach ( $list as $k => $doc ) {
                    if ( $doc['author'] == $uid ) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 自己和下属
            case self::CONTAIN_SUB:
                $subUids = UserUtil::getAllSubs( $uid, '', true );
                array_push( $subUids, $uid );
                $accordUid = array_unique( $subUids );
                foreach ( $list as $k => $doc ) {
                    if ( in_array( $doc['author'], $accordUid ) ) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 所在分支
            case self::SELF_BRANCH:
                //改动之后是一定会有部门的
                $branch = Department::model()->getBranchParent( $user['deptid'] );
                $childDeptIds = Department::model()->fetchChildIdByDeptids( $branch['deptid'], true );
                $accordUid = User::model()->fetchAllUidByDeptids( $childDeptIds, false );
                foreach ( $list as $k => $doc ) {
                    if ( in_array( $doc['author'], $accordUid ) ) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 所有人
            case self::All_PERMISSION:
                $list = self::grantPermission( $list, 1, $allowType );
                break;
            default :
                $list = self::grantPermission( $list, 0, $allowType );
                break;
        }
        return $list;
    }

    /**
     * 遍历给用户授予管理权限
     * @param array $users 用户数组
     * @param integer $permission 授与管理权限（0为无权限，1为有权限）
     * @param string $allowType 类型（allowEdit、allowDel）
     * @return array
     */
    private static function grantPermission( $list, $permission, $allowType ) {
        foreach ( $list as $k => $doc ) {
            $list[$k][$allowType] = $permission;
        }
        return $list;
    }

}
