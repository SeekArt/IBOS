<?php

/*
 * PHPExcel 操作类
 * To change this template file, choose Tools | Templates
 * @author Sam <gzxgs@ibos.com.cn>
 */

namespace application\core\utils;

/**
 * Description of PHPExcel
 *
 * @author Sam
 */
class PHPExcel {

	//导出excel
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

	//读取excel数据
	public static function excelToArray( $filePath, $startRow = 2 ) {
		require_once PATH_ROOT . '/system/extensions/PHPExcel/PHPExcel.php';
		$fileType = \PHPExcel_IOFactory::identify( $filePath ); //文件名自动判断文件类型
		$objReader = \PHPExcel_IOFactory::createReader( $fileType );
		$objPHPExcel = $objReader->load( $filePath );
		$sheet = $objPHPExcel->getActiveSheet( 0 ); //第一个工作簿
		$highestRow = $sheet->getHighestRow();	 //取得总行数 
		$highestColumn = $sheet->getHighestColumn(); //取得总列数
		$data = array();
		$row = $startRow;
		for ( $row; $row <= $highestRow; $row++ ) {
			for ( $column = 'A'; $column <= $highestColumn; $column++ ) {
				$data[$row][] = trim( $sheet->getCell( $column . $row )->getValue() );
			}
		}
		return $data;
	}

}
