<?php

namespace application\modules\main\utils;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\utils\Import;
use CDbCriteria;
use Exception;

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
 * @version $Id$
 */
class ImportParent
{

    //值在下方set方法里设置
    public $import = null;
    //二维数组，内层有status（成功标识，0或者1），text（提示信息），数组data（该行数据，如果有错误，则在错误处添加【醒目】的标记）
    public $error = array();
    //模版的标识符
    public $tpl = null;
    //规则模版
    public $rules = array(
        'unique' => array(),
        'required' => array(),
        'mobile' => array(),
        'email' => array(),
        'datetime' => array(),
    );
    public $session = null;

    public function __construct($tpl)
    {
        if (null === $this->import) {
            $this->import = (object)array();
            $this->import->data = array();
            $this->import->format = array();
            $this->import->insert = array();
            $this->import->update = array();
            $this->import->saveData = array();
            $this->import->relation = array();
            $this->import->per = 10;
            $this->import->times = 0;
            $this->import->i = 0;
            $this->import->importData = array();
            $this->tpl = $tpl;
            $this->session = Ibos::app()->session;
        }
    }

    protected function customRules($rules)
    {
        $ruleArray = is_array($rules) ? $rules : explode(',', $rules);
        foreach ($ruleArray as $rule) {
            $this->rules[$rule] = array();
        }
    }

    public function returnArray($array)
    {
        if (!empty($array[$this->tpl])) {
            return $array[$this->tpl];
        } else {
            return array();
        }
    }

    public function setData($data)
    {
        $this->import->data = $data;
        return $this;
    }

    /**
     * 模版字段对应导入数据字段的一维数组
     * 注意：这个数组在ImportController里经过了array_filter处理
     * @param type $fieldRelation
     * @return Import
     */
    public function setRelation($fieldRelation)
    {
        $this->import->relation = $fieldRelation;
        return $this;
    }

    public function setPer($per)
    {
        $this->import->per = $per;
        return $this;
    }

    public function setTimes($times)
    {
        $this->import->times = $times;
        return $this;
    }

    public function setI($i)
    {
        $this->import->i = $i;
        return $this;
    }

    public function getFieldRule($tplArray)
    {
        $return = array();
        $fieldMap = $this->field();
        $rules = $this->rules();
        foreach ($tplArray as $tplName) {
            foreach ($rules as $rule) {
                $fieldArray = $rule[0];
                $return[$tplName] = in_array($fieldMap[$tplName], $fieldArray) ? $rule[1] : array();
            }
        }
        return $return;
    }

    /**
     * 重复检查设置：
     * 创建新纪录new，格式：new
     * 覆盖旧记录cover，格式：cover
     * nothing,cover,new
     * @param type $check
     * @return Import
     */
    public function setCheck($check)
    {
        $this->import->check = $check;
        return $this;
    }

    private function formatRules()
    {
        //表前缀.字段=>规则
        $ruleArray = $this->rules();
        if (!empty($ruleArray)) {
            foreach ($ruleArray as $row) {
                if (!empty($row[0]) && !empty($row[1])) {
                    $fields = $row[0];
                    $rules = $row[1];
                    foreach ($rules as $rule) {
                        if (isset($this->rules[$rule])) {
                            $this->rules[$rule] = array_merge($this->rules[$rule], $fields);
                        }
                    }
                }
            }
        }
    }

    private function convertToRealFieldRelation($shiftRow)
    {
        //模版字段=>表前缀.表字段
        $fieldMap = $this->field();
        $i = $this->import->i;
        //模版字段=>导入的数据字段
        foreach ($this->import->relation as $tplField => $dataField) {
            $field = $fieldMap[$tplField];
            $data = !empty($shiftRow[$dataField]) ? $shiftRow[$dataField] : '';
            //表前缀.表字段=>导入的数据
            $this->import->importData[$field] = trim($data);
        }
        $this->import->format[$i] = $this->import->importData;
    }

    private function handleBaseCondition()
    {
        //表前缀.表字段=>模版字段
        $fieldMap = array_flip($this->field());
        //模版字段=>导入的数据字段
        $relation = $this->import->relation;
        //规则名=>array( 表前缀.字段名 ...)
        foreach ($this->rules as $ruleName => $fields) {
            if ($ruleName == 'unique') {
                continue; //这个后面去检测
            } else {
                $method = 'check' . ucfirst($ruleName);
                foreach ($fields as $field) {
                    if (isset($this->import->importData[$field])) {
                        if (method_exists($this, $method)) {
                            $this->$method($this->import->importData[$field], $relation[$fieldMap[$field]]);
                        }
                    }
                }
            }
        }
    }

    private function handleUniqueCondition()
    {
        $unique = $this->rules['unique'];
        //表前缀=>带双大括号的表名
        $tableMap = $this->table();
        //表前缀=>表主键
        $pkMap = $this->pk();
        //表前缀.表字段=>模版字段
        $fieldMap = array_flip($this->field());
        //模版字段=>导入的数据字段
        $relation = $this->import->relation;
        //i
        $i = $this->import->i;
        $insert = true;
        foreach ($unique as $field) {
            if (!empty($this->import->importData[$field])) {
                $data = $this->import->importData[$field];
                list($tablePrefix, $fieldName) = explode('.', $field);
                $table = $tableMap[$tablePrefix];
                $pk = $pkMap[$tablePrefix];
                $row = Ibos::app()->db->createCommand()
                    ->select('*')
                    ->from($table)
                    ->where(" `{$fieldName}` = '{$data}' ")
                    ->queryRow();

                //如果根据唯一字段找到了记录，则记录下来
                //这里会记录一个数组，因为每个唯一字段都可能对应一个更新操作
                if (false !== $row) {
                    $insert = false;
                    array_map(function ($key, $value) use (&$rowArray, $tablePrefix) {
                        $rowArray[$tablePrefix . '.' . $key] = $value;
                    }, array_keys($row), array_values($row));
                    $dataFieldName = $relation[$fieldMap[$field]];
                    $this->import->update[$i][$table] = array(
                        'pk' => $pk,
                        'rowid' => $row[$pk],
                        'row' => $rowArray,
                    );
                    $this->error[$i]['text'] .= $dataFieldName . '是唯一字段;';
                }
            }
        }
        //如果检查完成并没有发现重复，则设置当前数据为插入
        if (true === $insert) {
            $this->import->insert[$i] = array();
        }
    }

    /**
     * 这是入口
     * @return string
     */
    protected function import()
    {
        @set_time_limit(0);
        $failData = $this->session->get('import_fail_data', array());
        $start = $this->session->get('import_start');
        if (true === $start) {
            $this->start();
        }
        $failCount = $successCount = 0;
        $beginTime = microtime(true);
        if (!empty($this->import->data)) {
            $num = min(count($this->import->data), $this->import->per);
            $this->error[0] = array(
                'status' => true,
                'text' => '分批导入' . $num . '个，第' . ($this->import->times + 1) . '批',
                'i' => 0,
            );
            //格式化所有的设置好的规则
            $this->formatRules();
            for ($i = 1; $i <= $this->import->per; $i++) {
                if (empty($this->import->data)) {
                    break;
                }
                $this->setI($i);
                //$i就是一次性批量导入的数据的位置
                $shiftRow = array_shift($this->import->data);
                //重置导入的数据
                $this->resetImortData();
                //转化为导入的数据格式
                $this->convertToRealFieldRelation($shiftRow);
                //重置错误提示数组
                $this->resetErrorText();
                //处理唯一字段之外的检查
                $this->handleBaseCondition();
                //如果通过，则再检查唯一字段
                if (true === $this->error[$i]['status']) {
                    $this->handleUniqueCondition();
                }
                //处理根据选项判断是否继续插入或者更新数据
                $pass = $this->handleCheck();
                if ($pass) {
                    $successCount++;
                } else {
                    $failData[] = $shiftRow;
                    if (isset($this->import->update[$i])) {
                        unset($this->import->update[$i]);
                    }
                    if (isset($this->import->insert[$i])) {
                        unset($this->import->insert[$i]);
                    }
                    $failCount++;
                }
                $this->error[$i]['text'] .=
                    true === $this->error[$i]['status'] ? '成功！' : '失败！';
            }
            if (!empty($this->import->insert) || !empty($this->import->update)) {
                $this->handleData();
            }
            $failAllCount = $this->session->get('import_fail_all_count');
            $successAllCount = $this->session->get('import_success_all_count');
            $this->session->add('import_fail_data', $failData);
            $this->session->add('import_fail_count', $failCount);
            $this->session->add('import_fail_all_count', $failCount + $failAllCount);
            $this->session->add('import_success_count', $successCount);
            $this->session->add('import_success_all_count', $successCount + $successAllCount);
            $this->session->add('import_dataArray', $this->import->data);
            $this->session->add('import_dataArray_first', false);
            return array(
                'isSuccess' => true,
                'msg' => '',
                'data' => array(
                    'op' => 'continue',
                    'queue' => array_filter($this->error),
                    'times' => $this->import->times + 1,
                    'success' => $successCount,
                    'failed' => $failCount,
                ),
            );
        } else {
            $this->end();
            $this->session->add('import_dataArray_first', true);
            $endTime = microtime(true);
            $failAllCount = $this->session->get('import_fail_all_count');
            $successAllCount = $this->session->get('import_success_all_count');
            $this->session->add('import_fail_all_count', 0);
            $this->session->add('import_success_all_count', 0);
            $ajaxReturn = array(
                'isSuccess' => true,
                'msg' => '',
                'data' => array(
                    'op' => 'end',
                    'failed' => $failAllCount,
                    'success' => $successAllCount,
                    'time' => $endTime - $beginTime,
                ),
            );
        }

        return $ajaxReturn;
    }

    private function handleCheck()
    {
        $check = $this->import->check;
        $i = $this->import->i;
        //如果选择的是“新建记录”的选项，并且在重复性检查时有发现重复的值
        //设置错误状态，并且返回失败
        if (isset($this->import->update[$i]) && 'new' === $check) {
            $this->error[$i]['status'] = false;
            return false;
        }
        if (false === $this->error[$i]['status']) {
            return false;
        }
        return true;
    }

    private function resetImortData()
    {
        $this->import->importData = array();
    }

    private function resetErrorText()
    {
        $i = $this->import->i;
        $times = $this->import->times;
        $per = $this->import->per;
        $this->error[$i] = array(
            'status' => true,
            'text' => '第' . ($times * $per + $i) . '条记录：',
            'i' => $i,
        );
    }

    private function checkRequired($data, $dataFieldName)
    {
        $i = $this->import->i;
        if (empty($data)) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= $dataFieldName . '必填;';
        }
    }

    private function checkEmail($data, $dataFieldName)
    {
        $i = $this->import->i;
        if (!empty($data) && !StringUtil::isEmail($data)) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= $dataFieldName . '格式不正确;';
        }
    }

    private function checkMobile($data, $dataFieldName)
    {
        $i = $this->import->i;
        if (!empty($data) && !StringUtil::isMobile($data)) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= $dataFieldName . '格式不正确;';
        }
    }

    private function checkDatetime($data, $dataFieldName)
    {
        $i = $this->import->i;
        if (!empty($data) && false === strtotime($data)) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= $dataFieldName . '格式不正确;';
        }
    }

    private function handleData()
    {
        $tableMap = $this->table();
        $tableFlipMap = array_flip($tableMap);
        $pkMap = $this->pk();
        $insert = $this->import->insert;
        $update = $this->import->update;
        $format = $this->import->format;
        $insertArray = array_intersect_key($format, $insert);
        $updateArray = array_intersect_key($format, $update);
        $array = $insertArray + $updateArray;
        $formatData = array();
        $this->beforeFormatData($array);
        foreach ($array as $i => $data) {
            $insert = in_array($i, array_keys($this->import->insert));
            $this->formatData($data, $insert);
            $this->import->saveData[$i] = $data;
            foreach ($data as $field => $value) {
                list($tablePrefix, $fieldName) = explode('.', $field);
                $table = $tableMap[$tablePrefix];
                $formatData[$i][$table][$fieldName] = $value;
            }
        }
        $connection = Ibos::app()->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($formatData as $i => $dataArray) {
                foreach ($dataArray as $table => $row) {
                    //如果插入某张表的数据都是空的，那么就不插入
                    $temp = array_filter($row);
                    if (empty($temp) && !in_array($table, $this->force())) {
                        continue;
                    }
                    $prefix = $tableFlipMap[$table];
                    $key = $prefix . '.' . $pkMap[$prefix];
                    $formatI = $this->import->format[$i];
                    if (in_array($i, array_keys($this->import->insert))) {
                        foreach ($row as $column => $value) {
                            if (!is_array($value) && is_callable($value)) {
                                $row[$column] = $value($formatI);
                            }
                        }
                        $findRow = null;
                        if (!empty($row[$pkMap[$prefix]])) {
                            $id = $row[$pkMap[$prefix]];
                            $criteria = new CDbCriteria();
                            $criteria->condition = $pkMap[$prefix] . '=:row';
                            $criteria->params = array(':row' => $id);
                            $findRow = $connection->schema->commandBuilder
                                ->createFindCommand($table, $criteria)
                                ->execute();
                        }
                        if (empty($findRow)) {
                            $connection->schema->commandBuilder
                                ->createInsertCommand($table, $row)
                                ->execute();
                            $id = $connection->getLastInsertID();
                        }
                    } else {
                        $updateRow = $this->import->update[$i];
                        if (in_array($table, array_keys($updateRow))) {
                            $id = $updateRow[$table]['rowid'];
                            $criteria = new CDbCriteria();
                            $criteria->condition = $updateRow[$table]['pk'] . '=:row';
                            $criteria->params = array(':row' => $id);
                            $findRow = $updateRow[$table]['row'];
                            foreach ($row as $column => $value) {
                                if (!is_string($value) && is_callable($value)) {
                                    $row[$column] = $value($formatI, $findRow);
                                }
                            }
                            $connection->schema->commandBuilder
                                ->createUpdateCommand($table, $row, $criteria)
                                ->execute();
                        } else {
                            //这里是导入行中唯一字段检测出存在
                            //但是没有指定唯一字段的表的数据
                            //暂时不处理
                        }
                    }
                    //如果不是自增id，则id会为0的！！像这样的主键，一般都会自己生成
                    //所以这样的id在这两个数组里一定已经存在
                    //如果没有，就应该在子类设置
                    if (!empty($id)) {
                        $this->import->format[$i][$key] = $id;
                        $this->import->saveData[$i][$key] = $id;
                    }
                }
            }
            $this->afterHandleData($connection);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
        }
    }

    /**
     * 第一次开始的时候做的事情
     * 只有在所有数据导入结束才会变成false
     * 在第一次循环时会被设置成true
     */
    protected function start()
    {
        $this->session->add('import_start', false);
    }

    /**
     * 所有数据导入完成后做的事情
     * 只有在所有数据导入结束才会变成true
     * 在第一次循环时会被设置成false
     */
    protected function end()
    {

    }

    protected function force()
    {
        return array();
    }

    /**
     * 格式化处理之前的处理
     * @param array $data
     * @return boolean
     */
    protected function beforeFormatData(&$data)
    {

    }

    /**
     * 用以给子类重写然后返回需要的格式
     * @param array $data
     * @param boolean $isInsert 是否是插入
     * @return true
     */
    protected function formatData(&$data, $isInsert)
    {

    }

    /**
     * 新增或者更新之后操作
     * @return boolean
     */
    protected function afterHandleData($connection)
    {

    }

    /**
     * 这个暂时用不到，可能以后也用不到，用来设置表中数据依赖关系的顺序
     * 目前的顺序是根据子类的table方法定义的
     * @param type $data
     * @return boolean
     */
    protected function order(&$data)
    {

    }

}
