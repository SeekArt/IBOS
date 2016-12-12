<?php
/**
 * 请求参数验证器
 *
 * @namespace application\modules\vote\utils
 * @filename Validator.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/21 9:15
 */

namespace application\modules\vote\utils;

use application\modules\vote\extensions\valitron\src\Valitron\Validator as V;


/**
 * Class Validator
 *
 * @package application\modules\vote\utils
 */
class Validator
{
    /**
     * 返回一个新的验证器
     *
     * @param array $data 待验证请求参数
     * @param array $fields 需要验证的字段，为空时，验证所有字段
     * @return V
     */
    public static function create(array $data, $fields = array())
    {
        // 备注：V 是一个验证器类，当前类主要用于封装该验证器类。
        // 这里统一设置语言包，如果将来有改动，也便于维护。
        V::langDir(__DIR__ . '/../extensions/valitron/lang');
        V::lang('zh-cn');

        $v = new V($data, $fields);

        return $v;
    }

    /**
     * 验证用户请求参数
     *
     * @param array $data 待验证请求参数
     * @param array $rules 验证规则
     * @param array $fields 需要验证的字段，为空时，验证所有字段
     * @return bool
     * @throws \Exception
     */
    public static function validate(array $data, array $rules, $fields = array())
    {
        $validator = self::create($data, $fields);
        $validator->rules($rules);

        if ($validator->validate()) {
            return true;
        }

        // 验证不通过，输出错误
        $errorArr = $validator->errors();
        $errorStr = 'Errors: ';
        foreach ($errorArr as $errorField => $errors) {
            foreach ($errors as $error) {
                $errorStr .= sprintf('[%s]', $error);
            }
        }

        // 非终端模式下，换行符改为 <br >
        if (php_sapi_name() !== 'cli') {
            $errorStr = nl2br($errorStr);
        }

        throw new \Exception($errorStr);
    }
}
