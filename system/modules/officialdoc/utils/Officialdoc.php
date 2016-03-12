<?php

/**
 * 公文模块------ 工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 公文模块------  工具类
 * @package application.modules.officialDoc.utils
 * @version $Id: OfficialdocUtil.php 639 2013-06-20 09:42:12Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\utils;

use application\core\utils\Ibos;
use application\core\utils\String;
use application\modules\department\model\Department;
use application\modules\officialdoc\model\Officialdoc as OffModel;
use application\modules\officialdoc\model\OfficialdocReader;
use application\modules\user\model\User;

class Officialdoc {

    // 未签收
    const TYPE_NOSIGN = 'nosign';
    // 签收
    const TYPE_SIGN = 'sign';
    // 待审核
    const TYPE_NOTALLOW = 'notallow';
    // 草稿
    const TYPE_DRAFT = 'draft';

    /**
     * 列表查询条件组合
     * @param string $type 类型 值为nosign，nosign，notallow，draft中一种
     * @param array $uid 用户id
     * @param type $catid 分类id 包括当前分类及它的子类以,号分割的字符串
     * @param type $condition 新的查询条件
     * @return string $condition 新的查询条件
     */
    public static function joinListCondition( $type, $uid, $catid = 0, $condition = '' ) {
        $typeWhere = self::joinTypeCondition( $type, $uid, $catid );
        $condition = !empty( $condition ) ? $condition .= " AND " . $typeWhere : $typeWhere;
        $allCcDeptId = IBOS::app()->user->alldeptid . '';
        $allDeptId = IBOS::app()->user->alldeptid . '';
        $allDeptId .= ',' . IBOS::app()->user->allupdeptid . '';
        $allPosId = IBOS::app()->user->allposid . '';
        $deptCondition = '';
        $deptIdArr = explode( ',', trim( $allDeptId, ',' ) );
        if ( count( $deptIdArr ) > 0 ) {
            foreach ( $deptIdArr as $deptId ) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }
            $deptCondition = mb_substr( $deptCondition, 0, -4, 'utf-8' );
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }
        $scopeCondition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('{$allCcDeptId}',ccdeptid) OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$allPosId}',ccpositionid) OR FIND_IN_SET('{$uid}',uid ) OR FIND_IN_SET('{$uid}',ccuid )) OR (deptid='' AND positionid='' AND uid='') OR (author='{$uid}') OR (approver='{$uid}')) )";
        $condition.=" AND " . $scopeCondition;
        if ( !empty( $catid ) ) {
            $condition.=" AND catid IN ($catid)";
        }
        return $condition;
    }

    /**
     * 获取类型条件
     * @param string $type
     * @param integer $uid
     * @param integer $catid
     * @return string
     */
    public static function joinTypeCondition( $type, $uid, $catid = 0 ) {
        $typeWhere = '';
        $docIdArr = OfficialdocReader::model()->fetchDocidsByUid( $uid );
        if ( $type == self::TYPE_NOSIGN || $type == self::TYPE_SIGN ) {
            $docidsStr = implode( ',', $docIdArr );
            $docids = empty( $docidsStr ) ? '-1' : $docidsStr;
            $flag = $type == self::TYPE_NOSIGN ? 'NOT' : '';
            $typeWhere = " docid " . $flag . " IN($docids) AND status=1";
        } elseif ( $type == self::TYPE_NOTALLOW ) {
            $docids = OffModel::model()->fetchUnApprovalDocIds( $catid, $uid );
            $docidStr = implode( ',', $docids );
            $typeWhere = "FIND_IN_SET(`docid`, '{$docidStr}')";
        } elseif ( $type == self::TYPE_DRAFT ) {
            $typeWhere = "status='3' AND author='$uid'";
        } else {
            $typeWhere = "status ='1' AND approver!=0";
        }
        return $typeWhere;
    }

    /**
     * 组合搜索条件
     * @param array $search 查询数据
     * @param string $condition 条件
     * @return string 新的查询条件
     */
    public static function joinSearchCondition( array $search, $condition ) {
        $searchCondition = '';

        //添加对keyword转义，防止SQL错误
        $keyword = \CHtml::encode( $search['keyword'] );
        $starttime = $search['starttime'];
        $endtime = $search['endtime'];

        if ( !empty( $keyword ) ) {
            $searchCondition.=" subject LIKE '%$keyword%' AND ";
        }
        if ( !empty( $starttime ) ) {
            $starttime = strtotime( $starttime );
            $searchCondition.=" addtime>=$starttime AND";
        }
        if ( !empty( $endtime ) ) {
            $endtime = strtotime( $endtime ) + 24 * 60 * 60;
            $searchCondition.=" addtime<=$endtime AND";
        }
        $newCondition = empty( $searchCondition ) ? '' : mb_substr( $searchCondition, 0, -4, 'utf-8' );
        return $condition . $newCondition;
    }

    /**
     * 判断公文的阅读权限
     * @param integer $uid 用户访问uid
     * @param array $data 文章数据
     * @return boolean
     */
    public static function checkReadScope( $uid, $data ) {
        if ( $data['deptid'] == 'alldept' ) {
            return true;
        }
        if ( $uid == $data['author'] ) {
            return true;
        }
        //如果是审核人
//		$dashboardConfig = IBOS::app()->setting->get( 'setting/docconfig' );
//		$approver = $dashboardConfig['doccommentenable'];
//		if ( String::findIn( $uid, $approver ) ) {
//			return true;
//		}
        //如果都为空，返回true
        if ( empty( $data['deptid'] ) && empty( $data['positionid'] ) && empty( $data['uid'] ) ) {
            return true;
        }
        //得到用户的部门id,如果该id存在于文章部门范围之内,返回true
        $user = User::model()->fetch( array( 'select' => array( 'deptid', 'positionid' ), 'condition' => 'uid=:uid', 'params' => array( ':uid' => $uid ) ) );
        //取得文章部门范围id以及他的子id
        $childDeptid = Department::model()->fetchChildIdByDeptids( $data['deptid'] );
        if ( String::findIn( $user['deptid'], $childDeptid . ',' . $data['deptid'] ) ) {
            return true;
        }
        //取得文章抄送部门范围id以及他的子id
        $childCcDeptid = Department::model()->fetchChildIdByDeptids( $data['ccdeptid'] );
        if ( String::findIn( $user['deptid'], $childCcDeptid . ',' . $data['ccdeptid'] ) ) {
            return true;
        }
        //取得文章岗位范围Id与用户岗位相比较
        if ( String::findIn( $data['positionid'], $user['positionid'] ) ) {
            return true;
        }
        if ( String::findIn( $data['uid'], $uid ) ) {
            return true;
        }
        //取得文章抄送岗位范围Id与用户抄送岗位相比较
        if ( String::findIn( $data['ccpositionid'], $user['positionid'] ) ) {
            return true;
        }
        if ( String::findIn( $data['ccuid'], $uid ) ) {
            return true;
        }
        return false;
    }

    /**
     * 取得在发布范围内的uid数组
     * @param array $data
     * @return array
     */
    public static function getScopeUidArr( $data ) {
        $uidArr = array();
        if ( $data['deptid'] == 'alldept' ) {
            $users = IBOS::app()->setting->get( 'cache/users' );
            foreach ( $users as $value ) {
                $uidArr[] = $value['uid'];
            }
        } else if ( !empty( $data['deptid'] ) ) {
            foreach ( explode( ',', $data['deptid'] ) as $value ) {
                $criteria = array( 'select' => 'uid', 'condition' => "`deptid`={$value}" );
                $records = User::model()->fetchAll( $criteria );
                foreach ( $records as $record ) {
                    $uidArr[] = $record['uid'];
                }
            }
        }
        if ( !empty( $data['positionid'] ) ) {
            foreach ( explode( ',', $data['positionid'] ) as $value ) {
                $criteria = array( 'select' => 'uid', 'condition' => "`positionid`={$value}" );
                $records = User::model()->fetchAll( $criteria );
                foreach ( $records as $record ) {
                    $uidArr[] = $record['uid'];
                }
            }
        }
        if ( !empty( $data['uid'] ) ) {
            foreach ( explode( ',', $data['uid'] ) as $value ) {
                $uidArr[] = $value;
            }
        }
        return array_unique( $uidArr );
    }

    /**
     * 取出源数据中$field的值，用$join分割合并成字符串
     * @param string $str 逗号分割的字符串
     * @param array $data 源数据
     * @param type $field 要取出的字段
     */
    public static function joinStringByArray( $str, $data, $field, $join ) {
        if ( empty( $str ) ) {
            return '';
        }
        $result = array();
        $strArr = explode( ',', $str );
        foreach ( $strArr as $value ) {
            if ( array_key_exists( $value, $data ) ) {
                $result[] = $data[$value][$field];
            }
        }
        $resultStr = implode( $join, $result );
        return $resultStr;
    }

    /**
     * 取得选人框数据，去掉各自的前缀，返回数组，数组内
     * <pre>
     *  array(
     *      'deptid' => '2,3,4',
      'positionid' => '5,6',
      'uid' => '',
     * )
     * </pre>
     * @param string $data 源数据 格式 d_1,d_23,p_108
     * 
     * @return array
     */
    public static function handleSelectBoxData( $data, $flag = true ) {
        $result = array(
            'deptid' => '',
            'positionid' => '',
            'uid' => '',
        );
        if ( !empty( $data ) ) {
            if ( isset( $data['c'] ) ) {
                $result = array(
                    'deptid' => 'alldept',
                    'positionid' => '',
                    'uid' => '',
                );
                return $result;
            }
            if ( isset( $data['d'] ) ) {
                $result['deptid'] = implode( ',', $data['d'] );
            }
            if ( isset( $data['p'] ) ) {
                $result['positionid'] = implode( ',', $data['p'] );
            }
            if ( isset( $data['u'] ) ) {
                $result['uid'] = implode( ',', $data['u'] );
            }
        } else {
            if ( $flag ) {
                $result['deptid'] = 'alldept';
            }
        }
        return $result;
    }

    /**
     * 组合选人框的值
     * @param string $deptid 部门id
     * @param string $positionid 岗位Id
     * @param string $uid 用户id
     * @return type 
     */
    public static function joinSelectBoxValue( $deptid, $positionid, $uid ) {
        $tmp = array();
        if ( !empty( $deptid ) ) {
            if ( $deptid == 'alldept' ) {
                return 'c_0';
            }
            $tmp[] = String::wrapId( $deptid, 'd' );
        }
        if ( !empty( $positionid ) ) {
            $tmp[] = String::wrapId( $positionid, 'p' );
        }
        if ( !empty( $uid ) ) {
            $tmp[] = String::wrapId( $uid, 'u' );
        }
        return implode( ',', $tmp );
    }

    /**
     * 处理请求的高亮数据，过滤无用数据
     * $highLight['highlightstyle']='bold,color,italic,underline'
     */
    public static function processHighLightRequestData( $data ) {
        $highLight = array();
        $highLight['highlightstyle'] = '';
        if ( !empty( $data['endTime'] ) ) {
            $highLight['highlightendtime'] = strtotime( $data['endTime'] ) + 24 * 60 * 60 - 1;
        }
        if ( empty( $data['bold'] ) ) {
            $data['bold'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['bold'] . ',';
        if ( empty( $data['color'] ) ) {
            $data['color'] = '';
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['color'] . ',';
        if ( empty( $data['italic'] ) ) {
            $data['italic'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['italic'] . ',';
        if ( empty( $data['underline'] ) ) {
            $data['underline'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['underline'] . ',';
        $highLight['highlightstyle'] = mb_substr( $highLight['highlightstyle'], 0, strlen( $highLight['highlightstyle'] ) - 1, 'utf-8' );
        if ( !empty( $highLight['highlightendtime'] ) || strlen( $highLight['highlightstyle'] ) > 3 ) {
            $highLight['ishighlight'] = 1;
        } else {
            $highLight['ishighlight'] = 0;
        }
        return $highLight;
    }

    /**
     * 转换历史版本号,把数字1转为1.0,2转为1.1,3转为1.2
     * @param integer $version 实际版本数
     * @param float $increment 每次递增数 默认为0.1
     * @param float $minVersion 最小版本数 默认为1.0
     * @return float 转换后的版本数
     */
    public static function changeVersion( $version, $increment = 0.1, $minVersion = 1.0 ) {
        $newVersion = $minVersion + $increment * ($version - 1);
        if ( !strpos( $newVersion, '.' ) ) {
            $newVersion = $newVersion . '.0';
        }
        return $newVersion;
    }

    /**
     * 取得html中字母及中文字符数
     * @param string $html
     * @return type
     */
    public static function getCharacterLength( $html ) {
        $len = 0;
        $contents = preg_split( "~(<[^>]+?>)~si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
        foreach ( $contents as $tag ) {
            if ( trim( $tag ) == "" )
                continue;
            if ( preg_match( "~<([a-z0-9]+)[^/>]*?/>~si", $tag ) ) {
                continue;
            } else if ( preg_match( "~</([a-z0-9]+)[^/>]*?>~si", $tag, $match ) ) {
                continue;
            } else if ( preg_match( "~<([a-z0-9]+)[^/>]*?>~si", $tag, $match ) ) {
                continue;
            } else if ( preg_match( "~<!--.*?-->~si", $tag ) ) {
                continue;
            } else {
                $len += self::mstrlen( $tag );
            }
        }
        return $len;
    }

    /**
     * 截取HTML,并自动补全闭合
     * @param $html
     * @param $length
     * @param $end
     */
    public static function subHtml( $html, $start, $length ) {
        $result = '';
        $tagStack = array( '' );
        $len = 0;

        $contents = preg_split( "~(<[^>]+?>)~si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
        foreach ( $contents as $tag ) {
            if ( trim( $tag ) == "" )
                continue;
            if ( preg_match( "~<([a-z0-9]+)[^/>]*?/>~si", $tag ) ) {
                if ( $len >= $start && $len <= $length ) {
                    $result .= $tag;
                }
            } else if ( preg_match( "~</([a-z0-9]+)[^/>]*?>~si", $tag, $match ) ) {
                if ( $len >= $start && $len < $length ) {
                    if ( $tagStack[count( $tagStack ) - 1] == $match[1] ) {
                        array_pop( $tagStack );
                        $result .= $tag;
                    }
                }
            } else if ( preg_match( "~<([a-z0-9]+)[^/>]*?>~si", $tag, $match ) ) {
                if ( $len >= $start && $len <= $length ) {
                    array_push( $tagStack, $match[1] );
                    $result .= $tag;
                }
            } else if ( preg_match( "~<!--.*?-->~si", $tag ) ) {
                if ( $len >= $start && $len <= $length ) {
                    $result .= $tag;
                }
            } else {
                if ( $len + self::mstrlen( $tag ) < $length ) {
                    $len += self::mstrlen( $tag );
                    if ( $len >= $start && $len <= $length ) {
                        $result .= $tag;
                    }
                } else {
                    $str = self::msubstr( $tag, 0, $length - $len + 1 );
                    $result .= $str;
                    break;
                }
            }
        }
        while ( !empty( $tagStack ) ) {
            $result .= '</' . array_pop( $tagStack ) . '>';
        }
        return $result;
    }

    /**
     * 截取中文字符串
     * @param $string 字符串
     * @param $start 起始位
     * @param $length 长度
     * @param $charset&nbsp; 编码
     * @param $dot 附加字串
     */
    public static function msubstr( $string, $start, $length, $dot = '', $charset = 'UTF-8' ) {
        $string = str_replace( array( '&amp;', '&quot;', '&lt;', '&gt;', '&nbsp;' ), array( '&', '"', '<', '>', ' ' ), $string );
        if ( strlen( $string ) <= $length ) {
            return $string;
        }

        if ( strtolower( $charset ) == 'utf-8' ) {
            $n = $tn = $noc = 0;
            while ( $n < strlen( $string ) ) {
                $t = ord( $string[$n] );
                if ( $t == 9 || $t == 10 || (32 <= $t && $t <= 126) ) {
                    $tn = 1;
                    $n++;
                } elseif ( 194 <= $t && $t <= 223 ) {
                    $tn = 2;
                    $n += 2;
                } elseif ( 224 <= $t && $t <= 239 ) {
                    $tn = 3;
                    $n += 3;
                } elseif ( 240 <= $t && $t <= 247 ) {
                    $tn = 4;
                    $n += 4;
                } elseif ( 248 <= $t && $t <= 251 ) {
                    $tn = 5;
                    $n += 5;
                } elseif ( $t == 252 || $t == 253 ) {
                    $tn = 6;
                    $n += 6;
                } else {
                    $n++;
                }
                $noc++;
                if ( $noc >= $length ) {
                    break;
                }
            }
            if ( $noc > $length ) {
                $n -= $tn;
            }
            $strcut = mb_substr( $string, 0, $n, 'utf-8' );
        } else {
            for ( $i = 0; $i < $length; $i++ ) {
                $strcut .= ord( $string[$i] ) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            }
        }

        return $strcut . $dot;
    }

    /**
     * 取得字符串的长度，包括中英文。
     */
    public static function mstrlen( $str, $charset = 'UTF-8' ) {
        if ( function_exists( 'mb_substr' ) ) {
            $length = mb_strlen( $str, $charset );
        } elseif ( function_exists( 'iconv_substr' ) ) {
            $length = iconv_strlen( $str, $charset );
        } else {
            $arr = array();
            preg_match_all( "/[x01-x7f]|[xc2-xdf][x80-xbf]|xe0[xa0-xbf][x80-xbf]|[xe1-xef][x80-xbf][x80-xbf]|xf0[x90-xbf][x80-xbf][x80-xbf]|[xf1-xf7][x80-xbf][x80-xbf][x80-xbf]/", $str, $arr );
            $length = count( $arr[0] );
        }
        return $length;
    }

}
