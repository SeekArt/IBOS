<?php

/**
 * 单页图文工具类文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 单页图文工具类
 * @package application.modules.main.utils
 * @version $Id: SinglePage.php 3623 2014-06-11 07:12:47Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\main\utils;

use application\core\utils\Ibos;

class SinglePage
{

    /**
     * 切换内容模板的内容,模板内一定要有id=page_content的div，否则抛出错误
     * @param string $html 模板的html
     * @param string $replace 要替换page_content的html
     * @return object
     */
    public static function parse($html, $replace)
    {
        // 加载parser类库
        Ibos::import('application\extensions\simple_html_dom', true);
        $doc = \application\extensions\str_get_html($html);
        if (!$doc) {
            return null;
        }
        $e = $doc->find('div[id=page_content]', 0);
        if (!$e) {
            return null;
        }
        $e->innertext = $replace;
        return $doc;
    }

    /**
     * 根据模板文件路径获取模板中id=page_content的div的内容
     * @param string $file 模板文件路径
     * @return object
     */
    public static function getTplEditorContent($file)
    {
        // 加载parser类库
        Ibos::import('application\extensions\simple_html_dom', true);
        $doc = \application\extensions\str_get_html($file);
        if (!$doc) {
            return null;
        }
        $e = $doc->find('div[id=page_content]', 0);
        if (!$e) {
            return null;
        }
        return $e->innertext;
    }

    /**
     * 获取模板
     */
    public static function getAllTpls()
    {
        $tplPath = "data/page/"; // 单页图文模板目录
        $allowExt = array('php'); // 允许的单页图文文件后缀
        // 遍历单页图文所有模板
        $dir = opendir($tplPath);
        $tpls = array();
        while (($file = readdir($dir)) !== false) {
            if ($file != "." && $file != ".." && in_array(pathinfo($file, PATHINFO_EXTENSION), $allowExt)) {
                $tpls[] = $file;
            }
        }
        closedir($dir);
        return self::handleFileName($tpls);
    }

    /**
     * 处理模板名称输出，返回结果格式：array('index'=>'首页模板', 'print'=>'打印模板', 'subfield'=>'左右分栏模板', 'custom'=>'Page_custom')
     * @param array $files 处理的文件数组
     * @return array
     */
    public static function handleFileName($files)
    {
        $ret = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $info = pathinfo($file);
                $filename = $info['filename'];
                $ret[$filename] = Ibos::lang('Page_' . $filename);
            }
        }
        return $ret;
    }

}
