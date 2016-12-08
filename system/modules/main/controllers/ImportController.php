<?php

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
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
 * @version $Id$
 */
class ImportController extends Controller
{

    public $supportFormat = array(
        'xls' => 'Excel95+',
        'xlsx' => 'Excel2007+',
        'xml' => 'Excel2003',
        'csv' => 'csv',
        'html' => 'html',
    );
    public $session = null;
    public $obj = null;

    /**
     * 获取模版的路径
     * @param string $module 模块英文名
     * @return string 路径
     */
    private function getTplPath($module)
    {
        return "system/modules/{$module}/static/tpl";
    }

    /**
     * 初始化导入参数，写入session
     */
    public function initImport()
    {
        $request = Ibos::app()->request;
        $file = $request->getPost('url');
        $tpl = $request->getPost('tpl');
        $module = $request->getPost('module');
        $sheet = $request->getPost('sheet');
        $init = $request->getPost('init');
        $this->session = Ibos::app()->session;
        if ($init) {
            $this->session->remove('import_dataArray_all');
            $this->session->remove('import_sheetNames');
            $this->session->remove('import_tplFieldArray');
            $this->session->remove('import_fieldArray');
            $this->session->remove('import_module');
            $this->session->remove('import_url');
            $this->session->remove('import_tpl');
            $this->session->add('import_module', $module);
            $this->session->add('import_url', $file);
            $this->session->add('import_tpl', $tpl);
            $obj = $this->createModuleObj($module, $tpl);
            $tplConfig = $obj->config();
            $this->session->add('import_tplConfig', $tplConfig);
        }
        $this->session->remove('import_dataArray');
        $this->session->remove('import_sheet');
        $this->session->add('import_sheet', $sheet);
    }

    /**
     * 读取上传的excel（等文件）数据以及模版的数据
     * 把这些数据保存session
     * @return array ajax
     */
    public function actionSheet()
    {
        $this->initImport();
        $sheet = $this->session->get('import_sheet');
        $allArray = $this->session->get('import_dataArray_all');
        if (!empty($allArray[$sheet])) {
            $sheetNames = $this->session->get('import_sheetNames');
        } else {
            $file = $this->session->get('import_url');
            $pathinfo = pathinfo($file);
            if (!in_array($pathinfo['extension'], array_keys($this->supportFormat))) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('not support format'),
                    'data' => array(),
                ));
            }
            $tplConfig = $this->session->get('import_tplConfig');
            require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
            $tplFieldArray = $this->session->get('import_tplFieldArray');
            if (empty($tplFieldArray)) {
                $module = $this->session->get('import_module');
                $tplPath = $this->getTplPath($module);
                $tplPHPExcel = $this->createPHPExcel($tplPath . '/' . $tplConfig['filename']);
                $tplArray = $tplPHPExcel->getActiveSheet()->toArray();
                $tplFieldArray = $tplArray[$tplConfig['fieldline'] - 1];
                $this->session->add('import_tplFieldArray', array_filter($tplFieldArray));
            }
            if (ENGINE == 'SAAS') {
                $filePath = 'data/attachment/' . CORP_CODE . '/temp/' . md5($file) . '.' . $pathinfo['extension'];
                Ibos::engine()->io()->file()->downloadLocal($file, $filePath);
                $file = $filePath;
            }
            $objPHPExcel = $this->createPHPExcel($file);
            if ($sheet >= $objPHPExcel->getSheetCount()) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('sheet error'),
                    'data' => array(),
                ));
            }
            $sheetNames = $objPHPExcel->getSheetNames();
            $dataArray = $objPHPExcel->getSheet($sheet)->toArray();
            $this->session->add('import_sheetNames', $sheetNames);
            if (empty($dataArray[$tplConfig['fieldline'] - 1])) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('sheet data error'),
                    'data' => array(),
                ));
            }
            $fieldArray = $dataArray[$tplConfig['fieldline'] - 1];
            $this->session->add('import_fieldArray', $fieldArray);
            $formatData = array();
            foreach ($dataArray as $key => $data) {
                if ($key <= $tplConfig['line'] - 1) {
                    continue;
                }
                foreach ($data as $k => $row) {
                    $formatData[$key][$fieldArray[$k]] = $row;
                }
            }
            $allArray[$sheet] = $formatData;
            $this->session->add('import_dataArray_all', $allArray);
        }
        $ajaxReturn = array(
            'sheetnames' => $sheetNames,
            'sheet' => $sheet + 0,
        );
        return $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('success'),
            'data' => $ajaxReturn,
        ));
    }

    /**
     * 设置字段对应关系
     * @return array ajax
     */
    public function actionSettingColumns()
    {
        $this->session = Ibos::app()->session;
        $sheet = $this->session->get('import_sheet');
        $allArray = $this->session->get('import_dataArray_all');
        if (empty($allArray[$sheet])) {
            return array(
                'isSuccess' => false,
                'msg' => Ibos::lang('last step has not finished yet'),
                'data' => array(),
            );
        }
        $tplFieldArray = $this->session->get('import_tplFieldArray');
        $module = $this->session->get('import_module');
        $tpl = $this->session->get('import_tpl');
        $obj = $this->createModuleObj($module, $tpl);
        $rule = $obj->getFieldRule($tplFieldArray);
        $ajaxReturn = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('success'),
            'data' => array(
                'fieldArray' => $this->session->get('import_fieldArray'),
                'tplFieldArray' => $tplFieldArray,
                'tplConfig' => $this->session->get('import_tplConfig'),
                'rule' => $rule,
            ),
        );
        return $this->ajaxReturn($ajaxReturn);
    }

    /**
     * 导入
     * @return array ajax
     */
    public function actionImport()
    {
        $request = Ibos::app()->request;
        $op = $request->getPost('op');
        $per = $request->getPost('per', 10);
        $times = $request->getPost('times');
        $this->session = Ibos::app()->session;
        $tpl = $this->session->get('import_tpl');
        if ($op == 'start') {
            $this->session->remove('import_fail_data');
            $this->session->remove('import_fail_count');
            $this->session->remove('import_success_count');
            $this->session->remove('import_time');
            $this->session->remove('import_start');
            $this->session->add('import_time', microtime());
            $this->session->add('import_start', true);
            $fieldRelation = $request->getPost('fieldRelation');
            $fieldRelationFilter = array_filter($fieldRelation, function ($empty) {
                return (empty($empty) || strtolower($empty) == 'null') ? false : true;
            });
            if (empty($fieldRelationFilter)) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('not exists import relation'),
                    'data' => array(),
                ));
            }
            $checkOption = $request->getPost('checkOption');
            $this->session->add('import_fieldRelation', $fieldRelation);
            $this->session->add('import_checkOption', $checkOption);
            $this->session->add('import_dataArray_first', true);
            return $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('success'),
                'data' => array(
                    'op' => 'continue',
                    'queue' => array(array(
                        'status' => true,
                        'text' => '开始',
                    )),
                ),
            ));
        } else if ($op == 'continue') {
            $first = $this->session->get('import_dataArray_first');
            if (true === $first) {
                $allArray = $this->session->get('import_dataArray_all');
                $sheet = $this->session->get('import_sheet');
                $dataArray = $allArray[$sheet];
                $this->session->add('import_dataArray', $dataArray);
            } else {
                $dataArray = $this->session->get('import_dataArray');
            }
            $fieldRelation = $this->session->get('import_fieldRelation');
            $checkOption = $this->session->get('import_checkOption');
            $module = $this->session->get('import_module');
            $obj = $this->createModuleObj($module, $tpl);
            if (null === $obj) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('not exists file'),
                ));
            } else {
                $ajaxReturn = $obj->setData($dataArray)
                    ->setPer($per)
                    ->setTimes($times)
                    ->setRelation($fieldRelation)
                    ->setCheck($checkOption)
                    ->import();
                return $this->ajaxReturn($ajaxReturn);
            }
        }
    }

    /**
     * 创建导入工具类实例
     * @param string $module 模块英文名
     * @param string $tpl 模版标识符
     * @return object 实例化对象
     */
    public function createModuleObj($module, $tpl)
    {
        $class = 'application\\modules\\' . $module . '\\utils\\Import';
        if (null !== $this->obj) {
            return $this->obj;
        } else {
            if (class_exists($class)) {
                $this->obj = new $class($tpl);
                return $this->obj;
            } else {
                return null;
            }
        }
    }

    /**
     * 导出错误的数据
     */
    public function actionExportError()
    {
        $this->session = Ibos::app()->session;
        $failArray = $this->session->get('import_fail_data');
        if (empty($failArray)) {
            Env::iExit('没有错误');
        }
        require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
        $tplConfig = $this->session->get('import_tplConfig');
        $module = Ibos::app()->getRequest()->getPost('module');
        $tplPath = $this->getTplPath($module);
        $tplPHPExcel = PHPExcel_IOFactory::load($tplPath . '/' . $tplConfig['filename']);
        $fieldRelation = $this->session->get('import_fieldRelation');
        $relationData = array();
        foreach ($fieldRelation as $tplField => $dataField) {
            foreach ($failArray as $key => $fail) {
                $relationData[$key][$tplField] = $fail[$dataField];
            }
        }
        $dataArray = array();
        foreach ($relationData as $key => $export) {
            $dataArray[$key] = array_values($export);
        }
        $line = $tplConfig['line'] + 1;
        $tplPHPExcel->getActiveSheet()->fromArray($dataArray, null, 'A' . $line);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . date('Y-m-d H:i:s') . '-' . $tplConfig['name'] . '-错误数据' . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory:: createWriter($tplPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 下载模版
     */
    public function actionDownloadTpl()
    {
        $request = Ibos::app()->getRequest();
        $module = $request->getParam('module');
        $tpl = $request->getParam('tpl');
        $obj = $this->createModuleObj($module, $tpl);
        $tplConfig = $obj->config();
        $name = $tplConfig['name'];
        $tplPath = $this->getTplPath($module);
        $file = $tplPath . '/' . $tplConfig['filename'];
        $fileName = iconv('utf-8', 'gbk', $name . '.' . pathinfo($file, PATHINFO_EXTENSION));
        if (is_file($file)) {
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=" . $fileName);
            readfile($file);
            exit;
        } else {
            $this->error("抱歉，找不到模板文件！");
        }
    }

    private function createPHPExcel($file)
    {
        $isCsv = substr($file, -3) == 'csv' ? true : false;
        if (true === $isCsv) {
            $objReader = PHPExcel_IOFactory::createReader('CSV')
                ->setDelimiter(',')
                ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                ->setEnclosure('"')
                ->setLineEnding("\r\n")
                ->setSheetIndex(0);
            return $objReader->load($file);
        } else {
            return PHPExcel_IOFactory::load($file);
        }
    }

}
