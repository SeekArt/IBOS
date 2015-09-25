<?php

/*
 * PHPExcel 操作类
 * To change this template file, choose Tools | Templates
 * @author Sam <gzxgs@ibos.com.cn>
 * @update 2015-9-10 11:06:27
 */

namespace application\core\utils;

class PHPExcel {

	/**
	 * 导出excel文件
	 * @param type $filename 需要导出文件名（带有后缀xls、xlsx）
	 * @param type $header  导出头信息
	 * @param type $body 导出数据
	 */
	public static function exportToExcel( $filename, $header, $body ) {
		set_time_limit( 0 );
		require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
		$objPHPExcel = new \PHPExcel();
		$column = 1;
		if ( !empty( $header ) ) {
			//设置表头
			$key = ord( "A" );
			foreach ( $header as $v ) {
				$colum = chr( $key );
				$objPHPExcel->setActiveSheetIndex( 0 )->setCellValue( $colum . '1', $v );
				$key +=1;
			}
			$column = 2;
		}
		$objActSheet = $objPHPExcel->setActiveSheetIndex( 0 );
		foreach ( $body as $key => $rows ) { //行写入
			$span = ord( "A" );
			$keynew = ord( "@" );
			foreach ( $rows as $keyName => $value ) {// 列写入
				if ( $span > ord( "Z" ) ) {
					$keynew += 1;
					$span = ord( "A" );
					$row = chr( $keynew ) . chr( $span ); //超过26个字母时才会启用  
				} else {
					$row = chr( $span );
				}
				$objActSheet->setCellValue( $row . $column, $value );
				$span++;
			}
			$column++;
		}
		$fileName = iconv( 'utf-8', 'gbk', $filename );
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( "Content-Disposition: attachment; filename=\"$fileName\"" );
		header( 'Cache-Control: max-age=0' );
		$objWriter = \PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
		$objWriter->save( 'php://output' ); //文件通过浏览器下载
		exit;
	}

	/**
	 * 读取excel数据转换成数组
	 * @param type $filePath 文件路径
	 * @param type $header  是否带有头信息（默认带有头信息）
	 * @return array
	 */
	public static function excelToArray( $filePath, $header = true ) {
		require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
		$fileType = \PHPExcel_IOFactory::identify( $filePath ); //文件名自动判断文件类型
		$objReader = \PHPExcel_IOFactory::createReader( $fileType );
		$objPHPExcel = $objReader->load( $filePath );
		$sheet = $objPHPExcel->getActiveSheet(); //活动工作簿
		$data = $sheet->ToArray(); //直接转换成数组，带有头信息
		if ( $header === true ) {
			unset($data[0]); //去掉头信息
		}
		return $data;
	}

}
