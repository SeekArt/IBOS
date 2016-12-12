<?php

/*
 * PHPExcel 操作类
 * To change this template file, choose Tools | Templates
 * @author Sam <gzxgs@ibos.com.cn>
 * @update 2015-9-10 11:06:27
 */

namespace application\core\utils;

class PHPExcel
{

    /**
     * 导出 excel 文件
     *
     * @param string $filename 需要导出文件名（带有后缀xls、xlsx）
     * @param array $header 导出头信息
     * @param array $body 导出数据
     * @see http://www.zedwood.com/article/php-excel-writer-performance-comparison
     * @return bool
     */
    public static function exportToExcel($filename, $header, $body)
    {
        set_time_limit(0);

        // 需要填充的 excel 数据
        $excelData = array_merge(array($header), $body);

        require_once PATH_ROOT . '/system/extensions/PHP_XLSXWriter/xlsxwriter.class.php';
        $xlsWriter = new \XLSXWriter();
        $xlsWriter->writeSheet($excelData);
        Ibos::app()->request->sendFile($filename, $xlsWriter->writeToString());

        return true;
    }

    /**
     * 读取excel数据转换成数组
     * @param string $filePath 文件路径
     * @param mixed $header 去掉excel开头的行数,0表示第一行，依次类推，false表示不去掉
     * @return array
     */
    public static function excelToArray($filePath, $except = array(0))
    {
        require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
        $fileType = \PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
        $objReader = \PHPExcel_IOFactory::createReader($fileType);
        $objPHPExcel = $objReader->load($filePath);
        $sheet = $objPHPExcel->getActiveSheet(); //活动工作簿
        $data = $sheet->ToArray(); //直接转换成数组，带有头信息
        if (false !== $except) {
            foreach ($except as $line) {
                unset($data[$line]);
            }
        }
        return $data;
    }

}
