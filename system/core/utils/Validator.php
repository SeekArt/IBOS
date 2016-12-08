<?php

namespace application\core\utils;

use CHtml;
use Exception;

/**
 * 验证器（过滤器）
 *
 * @namespace application\core\utils
 * @filename Validator.php
 * @encoding UTF-8
 * @author forsona <2317216477@qq.com>
 * @link https://github.com/forsona
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016-10-10 18:38:10
 * @version $Id: Validator.php 8680 2016-10-20 13:00:43Z tanghang $
 */
class Validator
{

    /**
     * 手机验证器
     *
     * 为空时不做验证
     * @选填参数：isEmpty空值函数或结构
     */
    const VALIDATOR_MOBILE = 'mobile';

    /**
     * 邮箱验证器
     *
     * 为空时不做验证
     * @选填参数：isEmpty空值函数或结构
     */
    const VALIDATOR_EMAIL = 'email';

    /**
     * 必填验证器
     *
     * @选填参数：isEmpty空值函数或结构
     */
    const VALIDATOR_REQUIRED = 'required';

    /**
     * 唯一验证器
     *
     * @必填参数：model模型类，attribute属性
     * @选填参数：extra额外条件
     */
    const VALIDATOR_UNIQUE = 'unique';

    /**
     * 正则验证器
     *
     * @必填参数：pattern，如'/^1\\d{10}$/'
     * @选填参数：isEmpty空值函数或结构
     */
    const VALIDATOR_MATCH = 'match';

    /**
     * 规则验证器
     *
     * 条件成立时验证
     *
     * 使用filter过滤器计算值，因此支持filter过滤器的参数：value、isEmpty
     * @必填参数：method函数或结构
     * @选填参数：condition函数或结构，为空则一定验证
     */
    const VALIDATOR_RULE = 'rule';

    /**
     * 列表验证器
     *
     * @必填参数：list范围列表，逗号字符串或者数组
     * @选填参数：useStrict默认false，是否使用严格模式，严格模式不会强制转换类型
     *             value为空时的默认值，isEmpty空值函数或结构
     */
    const VALIDATOR_IN = 'in';

    /**
     * 数组验证器
     *
     * 当目标数组的键全部都在列表里或数据为空（不做验证）时，返回成功
     * @必填参数：list键值列表，逗号字符串或者数组，为空则直接验证失败
     */
    const VALIDATOR_ARRAY = 'array';

    /**
     * 二维数组验证器
     *
     * 功能同“数组验证器”，只不过是判断二维数组里层数组的键
     * @必填参数：list键值列表，逗号字符串或者数组，为空则直接验证失败
     */
    const VAlIDATOR_ARRAYS = 'arrays';

    /**
     * 默认值过滤器
     *
     * 为空时设置默认值
     * @必填参数：value默认值
     * @选填参数：isEmpty空值函数或结构
     */
    const FILTER_DEFAULT = 'default';

    /**
     * 通用过滤器
     *
     * @必填参数：method函数或结构
     * @选填参数：value为空时的默认值，isEmpty空值函数或结构
     */
    const FILTER_FILTER = 'filter';

    /**
     * 赋值过滤器
     *
     * @必填参数：value
     */
    const FILTER_SET = 'set';

    /**
     * 编码过滤器
     *
     * 不为空时编码，为空返回null
     * @选填参数：isEmpty
     */
    const FILTER_ENCODE = 'encode';

    /**
     * 删除过滤器
     *
     * 使用filter过滤器计算值，true时删除键
     * @必填参数：method函数或结构
     * @选填参数：value为空时默认值，isEmpty空值函数或结构
     */
    const FILTER_UNSET = 'unset';

    /**
     * 验证器名字
     * @var type
     */
    protected static $validator = array(
        'mobile',
        'email',
        'required',
        'unique',
        'match',
        'rule',
        'in',
        'array',
        'arrays',
    );

    private static function defaultOption()
    {
        return array(
            'debug' => !!DEBUG,
            'showField' => !!DEBUG,
            'safe' => true,
        );
    }

    public static function rules($data, $rules, $option = null)
    {
        if (null === $option) {
            $optionArray = self::defaultOption();
        } else {
            $temp = is_array($option) ? $option : explode(',', $option);
            $optionArray = array_merge(self::defaultOption(), $temp);
        }
        $error = $fieldArrays = $safeFieldArray = array();
        if (!is_array($data)) {
            $data = array();
        }
        $returnData = $data;
        if (!is_array($rules)) {
            $rules = array();
        }
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (!isset($rule[0]) || !isset($rule[1])) {
                    throw new Exception('缺少校验字段和规则：' . var_export($rule, true));
                }
                $fieldArray = is_array($rule[0]) ? $rule[0] : explode(',', $rule[0]);
                $ruleName = $rule[1];
                foreach ($fieldArray as $key => $value) {
                    $field = is_numeric($key) ? $value : $key;
                    if (!isset($fieldArrays[$field]) || $fieldArrays[$field] == $field) {
                        $fieldArrays[$field] = $value;
                    }
                    if (!method_exists(__CLASS__, $ruleName . 'Validator')) {
                        $method = 'filterValidator';
                        $rule['method'] = $ruleName;
                    } else {
                        if (!empty($rule[$ruleName])) {
                            $method = 'filterValidator';
                            $rule['method'] = $rule[$ruleName];
                        } else {
                            $method = $ruleName . 'Validator';
                        }
                    }
                    $result = self::$method($data, $field, $rule);
                    if (in_array($ruleName, self::$validator)) {
                        if (false === $result) {
                            $error[$field][] = isset($rule[2]) ? $rule[2] : "字段{$field}没有通过规则{$ruleName}";
                            if (false === $optionArray['debug'] && !empty($error)) {
                                return array('error' => $error);
                            }
                        } else {
                            $returnData[$field] = self::defaultValue($data, $field, $rule);
                        }
                    } else {
                        if ('unset' === $ruleName && null === $result) {
                            unset($returnData[$field]);
                        }
                        if (array_key_exists($field, $data) || array_key_exists('value', $rule)) {
                            $returnData[$field] = $result;
                        }
                    }
                    if (true === $optionArray['safe']) {
                        $safeFieldArray[] = $field;
                    }
                }
            }
        }
        $return = !empty($error) ? array(
            'error' => $error,
            ) : array(
            'data' => true === $optionArray['safe'] ? array_intersect_key($returnData, array_flip($safeFieldArray)) : $returnData,
        );
        if (true === $optionArray['showField']) {
            $return['field'] = $fieldArrays;
        }
        return $return;
    }

    private static function value($data, $field)
    {
        return isset($data[$field]) ? $data[$field] : null;
    }

    private static function defaultValue($data, $field, $rule)
    {
        $keyExist = array_key_exists('value', $rule);
        return $keyExist ? self::defaultValidator($data, $field, $rule) : self::value($data, $field);
    }

    private static function isEmpty($value, $rule)
    {
        return array_key_exists('isEmpty', $rule) ? self::filterValidator(array($value), 0, array(
                'method' => self::fun($value, $rule['isEmpty']),
            )) : null === $value;
    }

    private static function fun($value, $method)
    {
        if ('empty' == $method) {
            return function () use ($value) {
                return empty($value);
            };
        } else {
            if ('return' == $method) {
                return function () use ($value) {
                    return $value;
                };
            } else {
                return $method;
            }
        }
    }

    /**
     * ---------------------------以下为验证器----------------------------------
     */

    /**
     *
     * @param type $data
     * @param type $field
     * @param type $rule
     * @return type
     */
    protected static function mobileValidator($data, $field, $rule)
    {
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        return $isEmpty ? true : !!StringUtil::isMobile($value);
    }

    protected static function emailValidator($data, $field, $rule)
    {
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        return $isEmpty ? true : !!StringUtil::isEmail($value);
    }

    protected static function requiredValidator($data, $field, $rule)
    {
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        return !$isEmpty;
    }

    protected static function uniqueValidator($data, $field, $rule)
    {
        if (empty($rule['model']) || empty($rule['attribute'])) {
            throw new Exception('参数缺失：model,attribute');
        }
        $model = $rule['model'];
        $attribute = $rule['attribute'];
        $value = self::value($data, $field);
        $unique = " `{$attribute}` = '{$value}'";
        $extra = !empty($rule['extra']) ? $rule['extra'] : '';
        $where = $extra ? implode(' AND ', array($unique, $extra)) : $unique;
        $one = $model->findAll($where);
        return empty($one);
    }

    protected static function matchValidator($data, $field, $rule)
    {
        if (!array_key_exists('pattern', $rule)) {
            throw new Exception("参数缺失：pattern");
        }
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        if (true === $isEmpty) {
            return true;
        }
        $pattern = empty($rule['pattern']) ? '//' : $rule['pattern'];
        $result = !!preg_match($pattern, $value);
        return $result;
    }

    protected static function ruleValidator($data, $field, $rule)
    {
        //默认是会验证的
        $validate = true;
        if (!empty($rule['condition'])) {
            $validate = !!self::filterValidator($data, $field, array(
                    'method' => $rule['condition'],
            ));
        }
        //如果条件不成立，则不验证，返回true
        if (false === $validate) {
            return true;
        }
        //如果有validator参数，认为使用内部验证器验证
        if (array_key_exists('validator', $rule)) {
            $validator = $rule['validator'];
            if (!in_array($validator, self::$validator)) {
                throw new Exception("不支持的验证器：" . $validator);
            } else {
                if ($validator == 'rule') {
                    throw new Exception("不允许使用rule作为验证器");
                } else {
                    $method = $validator . 'Validator';
                    return self::$method($data, $field, $rule);
                }
            }
        } else {
            //否则使用filter过滤器的布尔值验证，通过返回true
            return !!self::filterValidator($data, $field, $rule);
        }
    }

    protected static function inValidator($data, $field, $rule)
    {
        if (!array_key_exists('list', $rule)) {
            throw new Exception("参数缺失：list");
        }
        $list = is_array($rule['list']) ? $rule['list'] : explode(',', $rule['list']);
        $keyExist = array_key_exists('value', $rule);
        $value = $keyExist ? self::defaultValidator($data, $field, $rule) : self::value($data, $field);
        $useStrict = array_key_exists('useStrict', $rule) ? $rule['useStrict'] : false;
        return in_array($value, $list, $useStrict);
    }

    protected static function arrayValidator($data, $field, $rule)
    {
        if (!array_key_exists('list', $rule)) {
            throw new Exception("参数缺失：list");
        }
        $list = is_array($rule['list']) ? $rule['list'] : explode(',', $rule['list']);
        $value = self::value($data, $field);
        //如果是空数组，则不做验证
        if (empty($value)) {
            return true;
        }
        if (empty($list) || !is_array($value)) {
            return false;
        }
        $diff = array_diff_key(array_flip($list), $value);
        if (!empty($diff)) {
            return false;
        }
        return true;
    }

    protected static function arraysValidator($data, $field, $rule)
    {
        if (!array_key_exists('list', $rule)) {
            throw new Exception("参数缺失：list");
        }
        $list = is_array($rule['list']) ? $rule['list'] : explode(',', $rule['list']);
        $value = self::value($data, $field);
        //如果是空数组，则不做验证
        if (empty($value)) {
            return true;
        }
        if (empty($list)) {
            return false;
        }
        foreach ($value as $row) {
            if (empty($row)) {
                continue;
            }
            $diff = array_diff_key(array_flip($list), $row);
            if (!empty($diff)) {
                return false;
            }
        }

        return true;
    }

    /**
     * ---------------------------以下为过滤器----------------------------------
     */
    protected static function defaultValidator($data, $field, $rule)
    {
        if (!array_key_exists('value', $rule)) {
            throw new Exception("参数缺失：value");
        }
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        return $isEmpty ? $rule['value'] : $value;
    }

    protected static function filterValidator($data, $field, $rule)
    {
        $keyExist = array_key_exists('value', $rule);
        $value = $keyExist ? self::defaultValidator($data, $field, $rule) : self::value($data, $field);
        if (empty($rule['method'])) {
            throw new Exception("参数缺失：method");
        }
        $method = self::fun($value, $rule['method']);
        if (!is_callable($method)) {
            throw new Exception("参数method不是可调用的结构：" . var_export($method, true));
        }
        $isEmpty = self::isEmpty(self::value($data, $field), $rule);
        return $keyExist && $isEmpty ? $value : $method($value);
    }

    protected static function setValidator($data, $field, $rule)
    {
        if (!array_key_exists('value', $rule)) {
            throw new Exception("参数缺失：value");
        }
        return $rule['value'];
    }

    protected static function encodeValidator($data, $field, $rule)
    {
        $value = self::value($data, $field);
        $isEmpty = self::isEmpty($value, $rule);
        return !$isEmpty ? CHtml::encode($value) : null;
    }

    protected static function unsetValidator($data, $field, $rule)
    {
        $isUnset = !!self::filterValidator($data, $field, $rule);
        return true === $isUnset ? null : self::defaultValue($data, $field, $rule);
    }

}
