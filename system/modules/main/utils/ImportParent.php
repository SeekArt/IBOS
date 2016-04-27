<?php

namespace application\modules\main\utils;

use application\core\utils\IBOS;
use application\modules\user\utils\Import;

/**
 * 导入父类
 *
 * @namespace application\modules\main\utils
 * @filename ImportParent.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link https://www.ibos.com.cn
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-3-24 17:07:27
 * @version $Id: ImportParent.php 6726 2016-03-31 02:07:23Z tanghang $
 */
class ImportParent {

    //值在下方set方法里设置
    public $import = NULL;
    //二维数组，内层有status（成功标识，0或者1），text（提示信息），数组data（该行数据，如果有错误，则在错误处添加【醒目】的标记）
    public $error = array();
    //模版字段以及数据库对应关系，带表前缀，由子类属性覆盖
    public $tplField = array();
    //模版字段关系中，数据表的前缀和表的对应关系，由子类属性覆盖
    public $tableMap = array();

    public function __construct() {
        if ( NULL === $this->import ) {
            $this->import = (object) array();
            $this->import->config = array();
            $this->import->data = array();
            $this->import->relation = array();
            $this->import->per = 10;
            $this->import->importData = array();
            $this->import->fieldCheck = array();
            $this->import->times = 0;
        }
    }

    /**
     *  'module' => 'user',
     *  'type' => 'common',
     *  'name' => '用户导入模版',
     *  'filename' => 'user_import.xls',
     *  'fieldline' => 3,
     *  'line' => 3,
     *  'required'=>'手机号,密码',
     *  'unique' => '手机号,用户名,微信号,工号,邮箱',
     * @param type $config
     * @return Import
     */
    public function setConfig( $config ) {
        $this->import->config = $config;
        return $this;
    }

    public function setData( $data ) {
        $this->import->data = $data;
        return $this;
    }

    /**
     * 模版字段对应导入数据字段的一维数组
     * 注意：这个数组在ImportController里经过了array_filter处理
     * @param type $fieldRelation
     * @return Import
     */
    public function setRelation( $fieldRelation ) {
        $this->import->relation = $fieldRelation;
        return $this;
    }

    /**
     * nothing,cover,new
     * @param type $check
     * @return Import
     */
    public function setCheck( $check ) {
        $this->import->check = $check;
        return $this;
    }

    public function setPer( $per ) {
        $this->import->per = $per;
        return $this;
    }

    public function setTimes( $times ) {
        $this->import->times = $times;
        return $this;
    }

    /**
     * 格式见：formatFieldCheck
     * @param type $fieldCheck
     * @return ImportParent
     */
    public function setFieldCheck( $fieldCheck ) {
        $this->import->fieldCheck = $fieldCheck;
        return $this;
    }

    /**
     * 对每次取出的一行格式化，并放入import->imosrtData里
     * 格式为：
     * 'importData' =>
     *    array (
     *      '{{department}}' =>
     *      array (
     *        'deptname' => 'mm',
     *      ),
     *      '{{user}}' =>
     *      array (
     *        'mobile' => 13250302684,
     *        'password' => 123456,
     *        'realname' => 木木,
     *        'gender' => NULL,
     *        'email' => NULL,
     *        'weixin' => NULL,
     *        'jobnumber' => 748360,
     *        'username' => '木木',
     *      ),
     *      '{{user_profile}}' =>
     *      array (
     *        'birthday' => NULL,
     *        'telephone' => NULL,
     *        'address' => NULL,
     *        'qq' => NULL,
     *        'bio' => NULL,
     *      ),
     *    ),
     * @param array $shiftRow 导入数据的一行
     */
    public function formatDataIndexByTable( $shiftRow ) {
        foreach ( $this->import->relation as $tplField => $dataField ) {
            $field = $this->tplField[$tplField];
            list($tablePrefix, $fieldName) = explode( '.', $field );
            $data = $shiftRow[$dataField];
            $pushData = array( $fieldName => trim( $data ) ); //去除导入数据里左右两边的空白符
            if ( empty( $this->import->importData[$this->tableMap[$tablePrefix]] ) ) {
                $this->import->importData[$this->tableMap[$tablePrefix]] = $pushData;
            } else {
                $this->import->importData[$this->tableMap[$tablePrefix]] = array_merge(
                        $this->import->importData[$this->tableMap[$tablePrefix]], $pushData );
            }
        }
    }

    /**
     * 格式化必须字段和唯一字段，在最开始的时候调用，存入session，后面放入import->fieldCheck里
     * 格式：
     * 'fieldCheck' =>
     *     array (
     *       'required' =>
     *       array (
     *         '{{user}}' =>
     *         array (
     *           0 => 'mobile',
     *           1 => 'password',
     *         ),
     *         ),
     *         'unique' =>
     *         array (
     *           '{{user}}' =>
     *           array (
     *             0 => 'mobile',
     *             1 => 'email',
     *             2 => 'weixin',
     *             3 => 'jobnumber',
     *             4 => 'username',
     *           ),
     *         ),
     *       ),
     * @param array $tplConfig config文件里的配置
     * @return 返回上面格式里键fieldCheck对应的数组
     */
    public function formatFieldCheck( $tplConfig ) {
        $list = array();
        foreach ( $this->tplField as $tplField => $field ) {
            list($tablePrefix, $fieldName) = explode( '.', $field );
            $requiredArray = explode( ',', $tplConfig['required'] );
            $uniqueArray = explode( ',', $tplConfig['unique'] );
            if ( in_array( $tplField, $requiredArray ) ) {
                $list['required'][$this->tableMap[$tablePrefix]][] = $fieldName;
            }
            if ( in_array( $tplField, $uniqueArray ) ) {
                $list['unique'][$this->tableMap[$tablePrefix]][] = $fieldName;
            }
        }
        return $list;
    }

    /**
     * 处理必须字段
     * @param integer $i 从数组里取出的顺序，比如取出第一个，i就等于1
     * @return array 错误数组，当然有可能是成功的
     */
    public function handleRequired( $i ) {
        $requiredAll = array();
        $table2PrefixArray = array_flip( $this->tableMap );
        $prefixDotField2Text = array_flip( $this->tplField );
        foreach ( array_values( $this->tableMap ) as $table ) {
            if ( empty( $this->import->importData[$table] ) ||
                    empty( $this->import->fieldCheck['required'][$table] ) ) {
                continue; //imortData真实的一条导入数据里以及必须字段里有可能没有某张表的数据，没有的话，跳过
            }
            $row = $this->import->importData[$table];
            foreach ( $this->import->fieldCheck['required'][$table] as $field ) {
                $prefixDotField = $table2PrefixArray[$table] . '.' . $field;
                if ( empty( $row[$field] ) ) {
                    $requiredAll[] = $prefixDotField2Text[$prefixDotField];
                }
            }
        }

        $num = $i + $this->import->times * $this->import->per;
        $this->error[$i] = array(
            'status' => true,
            'text' => sprintf( "第%s个：", $num ),
            'i' => $i,
        );
        if ( !empty( $requiredAll ) ) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= implode( ',', $requiredAll ) . '不能为空;';
        }
        return $this->error[$i]['status'];
    }

    /**
     * 根据唯一字段查数据库，如果条件不为空并且查到了，说明有重复，则返回重复标记
     * @param string $table 表名，带花括号
     * @param array $importRow 导入数据中的一行，经过处理，处理格式见formatDataIndexByTable的importData
     * @param array $uniqueArray 唯一字段数组，格式见formatFieldCheck里的unique
     * @param array $row 开始值不论传什么都会被变成通过查询之后的结果，建议给false
     * @return boolean 重复标记
     */
    public function repeatOrNot( $table, $importRow, $uniqueArray, &$row ) {
        foreach ( $uniqueArray as $unique ) {
            if ( !empty( $importRow[$unique] ) ) {
                $where[] = "`{$unique}` = '{$importRow[$unique]}' ";
            }
        }
        $row = IBOS::app()->db->createCommand()
                ->select( '*' )
                ->from( $table )
                ->where( implode( ' OR ', $where ) )
                ->queryRow();
        $repeat = !empty( $row ) && !empty( $where );
        return $repeat;
    }

    public function importData( $importName ) {
        $session = IBOS::app()->session;
        $failData = $session->get( 'import_fail_data', array() );
        $failCount = $successCount = 0;
        if ( !empty( $this->import->data ) ) {
            $num = min( count( $this->import->data ), $this->import->per );
            $this->error[0] = array(
                'status' => true,
                'text' => '分批导入' . $num . '个，第' . ($this->import->times + 1 ) . '批',
                'i' => 0,
            );
            set_time_limit( 300 );
            for ( $i = 1; $i <= $this->import->per; $i++ ) {
                if ( empty( $this->import->data ) ) {
                    break;
                }
                $shiftRow = array_shift( $this->import->data );
                $this->formatDataIndexByTable( $shiftRow );
                $requiredPass = $this->handleRequired( $i ); //必须字段通过与否判断
                if ( $requiredPass ) {
                    $this->{'import' . ucfirst( $importName ) . 'Detail'}( $i ); //子类必须实现具体的导入
                }
                if ( false === $this->error[$i]['status'] ) {
                    $failData = array_merge( $failData, array( $shiftRow ) );
                    $failCount ++;
                } else {
                    $successCount ++;
                }
            }
            $failAllCount = $session->get( 'import_fail_all_count' );
            $successAllCount = $session->get( 'import_success_all_count' );
            $session->add( 'import_fail_data', $failData );
            $session->add( 'import_fail_count', $failCount );
            $session->add( 'import_fail_all_count', $failCount + $failAllCount );
            $session->add( 'import_success_count', $successCount );
            $session->add( 'import_success_all_count', $successCount + $successAllCount );
            $session->add( 'import_dataArray', $this->import->data );
            $session->add( 'import_dataArray_first', false );
            return array(
                'isSuccess' => true,
                'msg' => '',
                'data' => array(
                    'op' => 'continue',
                    'queue' => array_filter( $this->error ),
                    'times' => $this->import->times + 1,
                    'success' => $successCount,
                    'failed' => $failCount,
                ),
            );
        } else {
            $session->add( 'import_dataArray_first', true );
            $time = $this->returnTime( microtime() ) - $this->returnTime( $session->get( 'import_time' ) );
            $failAllCount = $session->get( 'import_fail_all_count' );
            $successAllCount = $session->get( 'import_success_all_count' );
            $session->add( 'import_fail_all_count', 0 );
            $session->add( 'import_success_all_count', 0 );
            $ajaxReturn = array(
                'isSuccess' => true,
                'msg' => $time,
                'data' => array(
                    'op' => 'end',
                    'failed' => $failAllCount,
                    'success' => $successAllCount,
                    'time' => $time,
                ),
            );
        }

        return $ajaxReturn;
    }

    public function returnTime( $microtime ) {
        list($usec, $sec) = explode( ' ', $microtime );
        return ((float) $usec + (float) $sec);
    }

    /**
     * 由子类调用
     * @param type $i
     * @return type
     */
    public function refuseRepeat( $i, $way ) {
        $tableArray = array_values( $this->tableMap );
        $refuse = false;
        $row = false;
        $repeat = false;
        $tableMapFilp = array_flip( $this->tableMap );
        $tplFieldMapFilp = array_flip( $this->tplField );
        foreach ( $tableArray as $table ) {
            if ( empty( $this->import->importData[$table] ) ) {
                continue;
            }
            $importRow = $this->import->importData[$table];
            $prefix = $tableMapFilp[$table];
            if ( !empty( $this->import->fieldCheck['unique'][$table] ) ) {
                $uniqueArray = $this->import->fieldCheck['unique'][$table];
                $repeat = $this->repeatOrNot( $table, $importRow, $uniqueArray, $row );
                $textArray = array();
                foreach ( $uniqueArray as $uniqueField ) {
                    $textArray[] = $tplFieldMapFilp[$prefix . '.' . $uniqueField];
                }
                //重复检查的规则是创建新的数据时，如果有重复数据，则创建失败
                if ( $way == 'new' && $repeat ) {
                    $this->error[$i]['status'] = false;
                    $this->error[$i]['text'] .= implode( ',', $textArray );
                }
            }
            $rowArray[$table] = $row;
        }
        //虽然可能有重复的值，但是如果是覆盖的方式（覆盖之后还是唯一啊）
        //或者不处理的方式（只要不是数据库限定死的，那么就算是唯一还是可以重复的嘛）
        //所以，当状态是false的时候，才加上这句话。true的时候，text到这里时应该“成功”的字样
        if ( false === $this->error[$i]['status'] ) {
            $this->error[$i]['text'].='为唯一字段，请保证值唯一;';
        }
        return array(
            'refuse' => $refuse,
            'row' => $rowArray,
            'repeat' => $repeat,
        );
    }

    /**
     * 解释一下这个函数的名字和参数名，如果你觉得有更好的名字，告诉我吧
     * 函数名：查找（并且）创建文件夹，这是一个比喻的说法
     * 类似于组织架构那种树状结构，如果需要创建一个新的根节点，就是需要“查找（根节点），并且创建（他们）”
     * 这样的树在数据库存的格式就是id,pid的格式，查询出来就是一个二维数组
     * 而一般插入树节点，都是提供类似于“a/b/c”这样的路径，因此这里我把他们叫做path
     *
     * 说明：这个函数里的“无尽の数组”必须确保永远都处于最新状态，因此并未对一次性插入多个文件夹进行优化处理
     * 如果要做这样的操作，需要找到文件夹后，插入数据库，查出新的数据，再查找
     * @param array $endlessArray “无尽の数组”（指的是用以无限级格式的数组，也就是有pid的那种）
     * @param string $path 目录的路径名
     * @param string $idName id的字段名
     * @param string $pidName pid的字段名
     * @param string $nameName name的字段名
     */
    public function findToCreateFolder( $endlessArray, $path, $idName, $pidName, $nameName ) {
        //以pid为键重新生成一个数组
        $arrayIndexByPid = array();
        foreach ( $endlessArray as $endless ) {
            $arrayIndexByPid[$endless[$pidName]][] = $endless;
        }
        $pathArray = explode( '/', $path );
        $findArray = array();
        $root = $pid = 0; //根父节点的值一定是0，而默认的找到的根父节点的值也是0
        foreach ( $pathArray as $folderName ) {
            if ( !empty( $arrayIndexByPid[$root] ) ) {
                $folderArray = $arrayIndexByPid[$root];
                $nameArray = array();
                //I和II的循环的数组都是一样的
                //I为了让目录名和某个父节点下的目录对比
                //II在I的确保下，那个等于号一定是存在的，此时就需要找到对应的id
                foreach ( $folderArray as $folder ) {//--------------------(I)
                    $nameArray[] = $folder[$nameName];
                }
                if ( in_array( $folderName, $nameArray ) ) {
                    foreach ( $folderArray as $folder ) {//-----------------(II)
                        if ( $folder[$nameName] == $folderName ) {
                            $root = $folder[$idName];
                            $pid = $root;
                        }
                    }
                    continue;
                } else {
                    $pid = $root;
                    $findArray[] = $folderName;
                }
            } else {
                $pid = $root;
                $findArray[] = $folderName;
            }
        }
        return array(
            'pid' => $pid,
            'findArray' => $findArray,
        );
    }

}
