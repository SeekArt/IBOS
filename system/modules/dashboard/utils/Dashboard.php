<?php

/**
 * 后台模块静态函数库类文件。
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台模块函数库类，提供全局静态方法调用
 * @package application.modules.dashboard.utils
 * @version $Id: Dashboard.php 7564 2016-07-16 03:53:02Z gzhyj $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\utils;

use application\core\utils\File;
use application\core\utils\Ibos;
use CException;

class Dashboard
{

    /**
     * 获取字体文件夹列表
     * @param string $path 要读取的字体文件夹目录
     * @return array 字体文件名列表数组
     */
    public static function getFontPathlist($path)
    {
        $fonts = (array) glob($path . '*.ttf');
        $fontList = array();
        foreach ($fonts as $font) {
            if (is_file($font) && is_readable($font)) {
                $fontList[] = basename($font);
            }
        }
        return $fontList;
    }

    /**
     * 移动临时文件到指定目录
     * @param string $file 当前临时目录的文件
     * @param string $path 指定文件夹地址
     * @param boolean $delete 是否删除原文件
     * @return string 文件名
     * @throws CException 系统环境异常：无法移动文件
     */
    public static function moveTempFile($file, $path, $delete = false)
    {
        $fileName = File::copyToDir($file, $path, $delete);
        if (!$fileName) {
            throw new CException(Ibos::lang('Move file failed', 'error', array('file' => $file, 'path' => $path)));
        } else {
            return $fileName;
        }
    }

    /**
     * 用于转换POST上来的数组， 如 source[title][] 之类
     * @param array $arr
     * @return array
     */
    public static function arrayFlipKeys($arr)
    {
        $arr2 = array();
        $arrKeys = @array_keys($arr);
        list(, $first) = @each(array_slice($arr, 0, 1));
        if ($first) {
            foreach ($first as $k => $v) {
                foreach ($arrKeys as $key) {
                    $arr2[$k][$key] = $arr[$key][$k];
                }
            }
        }
        return $arr2;
    }

    /**
     * 检查表达式语法
     * @param string $formula 公式
     * @param array $operators 操作符
     * @param array $tokens 出现的项目
     * @return boolean
     */
    public static function checkFormulaSyntax($formula, $operators, $tokens)
    {
        $var = implode('|', $tokens);
        $operator = implode('', $operators);
        $operator = str_replace(
            array('+', '-', '*', '/', '(', ')', '\''), array('\+', '\-', '\*', '\/', '\(', '\)', '\\\''), $operator
        );
        if (!empty($formula)) {
            //Debug::eval有错误
            if (!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(@eval(preg_replace("/($var)/", "\$\\1", $formula) . ';'))) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查积分公式
     * @param string $formula 积分公式
     * @return boolean 通过与否
     */
    public static function checkFormulaCredits($formula)
    {
        return self::checkFormulaSyntax(
                $formula, array('+', '-', '*', '/', ' '), array('extcredits[1-5]')
        );
    }

    /**
     * 取得后台配置数据
     *
     * @param $fields array 需要获取的配置字段
     * @return array 配置信息
     */
    public static function getDashboardConfig($fields)
    {
        $result = array();
        foreach ($fields as $field) {
            $result[$field] = Ibos::app()->setting->get('setting/' . $field);
        }
        return $result;
    }

}
