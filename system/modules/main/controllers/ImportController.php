<?php

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\IBOS;
use CJSON;
use PHPExcel_IOFactory;

/**
 * excel（有可能会有其他的如pdf，xml，html等）导入导出工具
 *
 * @namespace application\modules\main\controllers
 * @filename ImportController.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link https://www.ibos.com.cn
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-3-23 16:54:42
 * @version $Id: ImportController.php 6726 2016-03-31 02:07:23Z tanghang $
 */
class ImportController extends Controller {

    public $tpl = 'data/tpl';
    public $supportFormat = array(
        'xls' => 'Excel95+',
        'xlsx' => 'Excel2007+',
        'xml' => 'Excel2003',
        'csv' => 'csv',
        'html' => 'html',
    );
    public $session = NULL;

    private function getImportConfig() {
        $module = IBOS::app()->getEnabledModule();
        $importConfig = array();
        foreach ( $module as $row ) {
            $config = CJSON::decode( $row['config'] );
            if ( !empty( $config['param']['importdoc'] ) ) {
                $importConfig = array_merge( $importConfig, $config['param']['importdoc'] );
            }
        }
        return $importConfig;
    }

    private function getTplConfig( $tpl ) {
        $tplConfig = $this->session->get( 'import_tplConfig' );
        if ( empty( $tplConfig ) ) {
            $importConfig = $this->getImportConfig();
            $tplConfig = $importConfig[$tpl];
        }
        return $tplConfig;
    }

    public function initImport() {
        $request = IBOS::app()->request;
        $file = $request->getPost( 'url' );
        $tpl = $request->getPost( 'tpl' );
        $sheet = $request->getPost( 'sheet' );
        $init = $request->getPost( 'init' );
        $this->session = IBOS::app()->session;
        if ( $init ) {
            $this->session->remove( 'import_dataArray_all' );
            $this->session->remove( 'import_sheetNames' );
            $this->session->remove( 'import_url' );
            $this->session->remove( 'import_tpl' );
            $this->session->remove( 'import_tplConfig' );
            $this->session->remove( 'import_tplFieldArray' );
            $this->session->remove( 'import_fieldArray' );
            $tplConfig = $this->getTplConfig( $tpl );
            $this->session->add( 'import_tplConfig', $tplConfig );
        }
        $this->session->remove( 'import_sheet' );
        $this->session->remove( 'import_dataArray' );
        $this->session->remove( 'import_fieldCheck' );
        $this->session->add( 'import_url', $file );
        $this->session->add( 'import_tpl', $tpl );
        $this->session->add( 'import_sheet', $sheet );
    }

    public function actionSheet() {
        $this->initImport();
        $sheet = $this->session->get( 'import_sheet' );
        $allArray = $this->session->get( 'import_dataArray_all' );
        if ( !empty( $allArray[$sheet] ) ) {
            $sheetNames = $this->session->get( 'import_sheetNames' );
        } else {
            $file = $this->session->get( 'import_url' );
            $pathinfo = pathinfo( $file );
            if ( !in_array( $pathinfo['extension'], array_keys( $this->supportFormat ) ) ) {
                return $this->ajaxReturn( array(
                            'isSuccess' => false,
                            'msg' => IBOS::lang( 'not support format' ),
                            'data' => array(),
                        ) );
            }
            $tplConfig = $this->getTplConfig( $this->session->get( 'import_tpl' ) );
            require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
            $tplFieldArray = $this->session->get( 'import_tplFieldArray' );
            if ( empty( $tplFieldArray ) ) {
                $tplPHPExcel = PHPExcel_IOFactory::load( $this->tpl . '/' . $tplConfig['filename'] );
                $tplArray = $tplPHPExcel->getActiveSheet()->toArray();
                $tplFieldArray = $tplArray[$tplConfig['fieldline'] - 1];
                $this->session->add( 'import_tplFieldArray', $tplFieldArray );
            }

            $objPHPExcel = PHPExcel_IOFactory::load( $file );
            if ( $sheet >= $objPHPExcel->getSheetCount() ) {
                return $this->ajaxReturn( array(
                            'isSuccess' => false,
                            'msg' => IBOS::lang( 'sheet error' ),
                            'data' => array(),
                        ) );
            }
            $worksheetCount = $objPHPExcel->getSheetCount();
            //以下这个循环其实是复制phpExcel里getSheetNames的代码改过来的
            //不信你就可以把上面的getSheetCount改成getSheetNames然后按ctrl进去看看
            //拿出来的原因是，phpExcel磕了药似的，明明存在的键
            //在调用了getSheetNames方法后再调用getSheet方法去toArray获取数据，就报错了
            //然后我懵逼了，然后有了下面的代码
            $sheetNames = array();
            for ( $i = 0; $i < $worksheetCount; ++$i ) {
                if ( $i == $sheet ) {//这个if一定成立，不成立的情况被上面的return返回了
                    $dataArray = $objPHPExcel->getSheet( $i )->toArray();
                }
                $sheetNames[] = $objPHPExcel->getSheet( $i )->getTitle();
            }
            $this->session->add( 'import_sheetNames', $sheetNames );
            if ( empty( $dataArray[$tplConfig['fieldline'] - 1] ) ) {
                return $this->ajaxReturn( array(
                            'isSuccess' => false,
                            'msg' => IBOS::lang( 'sheet data error' ),
                            'data' => array(),
                        ) );
            }
            $fieldArray = $dataArray[$tplConfig['fieldline'] - 1];
            $this->session->add( 'import_fieldArray', $fieldArray );
            $formatData = array();
            foreach ( $dataArray as $key => $data ) {
                if ( $key <= $tplConfig['line'] - 1 ) {
                    continue;
                }
                foreach ( $data as $k => $row ) {
                    $formatData[$key][$fieldArray[$k]] = $row;
                }
            }
            $allArray[$sheet] = $formatData;
            $this->session->add( 'import_dataArray_all', $allArray );
        }
        $ajaxReturn = array(
            'sheetnames' => $sheetNames,
            'sheet' => $sheet + 0,
        );
        return $this->ajaxReturn( array(
                    'isSuccess' => true,
                    'msg' => IBOS::lang( 'success' ),
                    'data' => $ajaxReturn,
                ) );
    }

    public function actionSettingColumns() {
        $this->session = IBOS::app()->session;
        $sheet = $this->session->get( 'import_sheet' );
        $allArray = $this->session->get( 'import_dataArray_all' );
        if ( empty( $allArray[$sheet] ) ) {
            return array(
                'isSuccess' => false,
                'msg' => IBOS::lang( 'last step has not finished yet' ),
                'data' => array(),
            );
        }
        $ajaxReturn = array(
            'isSuccess' => true,
            'msg' => IBOS::lang( 'success' ),
            'data' => array(
                'fieldArray' => $this->session->get( 'import_fieldArray' ),
                'tplFieldArray' => $this->session->get( 'import_tplFieldArray' ),
                'tplConfig' => $this->session->get( 'import_tplConfig' ),
            ),
        );
        return $this->ajaxReturn( $ajaxReturn );
    }

    public function actionImport() {
        $request = IBOS::app()->request;
        $op = $request->getPost( 'op' );
        $per = $request->getPost( 'per', 10 );
        $times = $request->getPost( 'times' );
        $this->session = IBOS::app()->session;
        $tpl = $this->session->get( 'import_tpl' );
        $tplConfig = $this->session->get( 'import_tplConfig' );
        if ( $op == 'start' ) {
            $this->session->remove( 'import_fail_data' );
            $this->session->remove( 'import_fail_count' );
            $this->session->remove( 'import_success_count' );
            $this->session->remove( 'import_time' );
            $this->session->add( 'import_time', microtime() );
            $fieldRelation = $request->getPost( 'fieldRelation' );
            $fieldRelationFilter = array_filter( $fieldRelation, function($empty) {
                return (empty( $empty ) || strtolower( $empty ) == 'null') ? false : true;
            } );
            if ( empty( $fieldRelationFilter ) ) {
                return $this->ajaxReturn( array(
                            'isSuccess' => false,
                            'msg' => IBOS::lang( 'not exists import relation' ),
                            'data' => array(),
                        ) );
            }
            $checkOption = $request->getPost( 'checkOption' );
            $this->session->add( 'import_fieldRelation', $fieldRelationFilter );
            $this->session->add( 'import_checkOption', $checkOption );
            $this->session->add( 'import_dataArray_first', true );
            return $this->ajaxReturn( array(
                        'isSuccess' => true,
                        'msg' => IBOS::lang( 'success' ),
                        'data' => array(
                            'op' => 'continue',
                            'queue' => array( array(
                                    'status' => true,
                                    'text' => '开始',
                                ) ),
                        ),
                    ) );
        } else if ( $op == 'continue' ) {
            $first = $this->session->get( 'import_dataArray_first' );
            if ( true === $first ) {
                $allArray = $this->session->get( 'import_dataArray_all' );
                $sheet = $this->session->get( 'import_sheet' );
                $dataArray = $allArray[$sheet];
                $this->session->add( 'import_dataArray', $dataArray );
            } else {
                $dataArray = $this->session->get( 'import_dataArray' );
            }
            $fieldRelation = $this->session->get( 'import_fieldRelation' );
            $checkOption = $this->session->get( 'import_checkOption' );
            $methodName = 'import';
            foreach ( explode( '_', $tpl ) as $part ) {
                $methodName.=ucfirst( $part );
            }
            $class = 'application\\modules\\' . $tplConfig['module'] . '\\utils\\Import';
            if ( class_exists( $class ) ) {
                $obj = new $class();
                $fieldCheck = $this->session->get( 'import_fieldCheck' );
                if ( empty( $fieldCheck ) ) {
                    $fieldCheck = $obj->formatFieldCheck( $tplConfig );
                    $this->session->add( 'import_fieldCheck', $fieldCheck );
                }
                $ajaxReturn = $obj->setData( $dataArray )
                        ->setConfig( $tplConfig )
                        ->setFieldCheck( $fieldCheck )
                        ->setPer( $per )
                        ->setTimes( $times )
                        ->setRelation( $fieldRelation )
                        ->setCheck( $checkOption )
                        ->$methodName();
                return $this->ajaxReturn( $ajaxReturn );
            } else {
                return $this->ajaxReturn( array(
                            'isSuccess' => false,
                            'msg' => IBOS::lang( 'not exists file' ),
                        ) );
            }
        }
    }

    public function actionExportError() {
        $this->session = IBOS::app()->session;
        $failArray = $this->session->get( 'import_fail_data' );
        if ( empty( $failArray ) ) {
            Env::iExit( '没有错误' );
        }
        require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
        $tplConfig = $this->getTplConfig( $this->session->get( 'import_tpl' ) );
        $tplPHPExcel = PHPExcel_IOFactory::load( $this->tpl . '/' . $tplConfig['filename'] );
        $fieldRelation = $this->session->get( 'import_fieldRelation' );
        $relationData = array();
        foreach ( $fieldRelation as $tplField => $dataField ) {
            foreach ( $failArray as $key => $fail ) {
                $relationData[$key][$tplField] = $fail[$dataField];
            }
        }
        $dataArray = array();
        foreach ( $relationData as $key => $export ) {
            $dataArray[$key] = array_values( $export );
        }
        $line = $tplConfig['line'] + 1;
        $tplPHPExcel->getActiveSheet()->fromArray( $dataArray, NULL, 'A' . $line );
        header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        header( 'Content-Disposition: attachment;filename="' . date( 'Y-m-d H:i:s' ) . '-' . $tplConfig['name'] . '-错误数据' . '.xlsx"' );
        header( 'Cache-Control: max-age=0' );

        $objWriter = PHPExcel_IOFactory:: createWriter( $tplPHPExcel, 'Excel2007' );
        $objWriter->save( 'php://output' );
        exit;
    }

}
