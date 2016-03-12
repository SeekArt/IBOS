<?php

/**
 * 微博模块 通用静态工具类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author banyan <banyan@ibos.com.cn>
 */
/**
 * @package application.modules.weibo.components
 * @version $Id: Common.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\weibo\utils;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Image;
use application\core\utils\String;
use application\modules\main\model\Setting;
use CJSON;

class Common {

    /**
     * 获取缩略图地址，无则生成
     * @param array $attach 附件数组
     * @param integer $width 缩略图之宽
     * @param integer $height 缩略图之高
     * @return string 缩略图地址
     */
    public static function getThumbImageUrl( $attach, $width, $height ) {
        $attachUrl = File::getAttachUrl();
        $thumbName = self::getThumbName( $attach, $width, $height );
        $thumbUrl = File::fileName( $thumbName );
        if ( File::fileExists( $thumbUrl ) ) {
            return $thumbName;
        } else {
            $attachment = $attach['attachment'];
            $file = $attachUrl . '/' . $attachment;
            $imgext = Attach::getCommonImgExt();
            if ( File::fileExists( $file ) ) {
                $info = Image::getImageInfo( File::fileName( $file ) );
                $infoCorrect = is_array( $info ) && in_array( $info['type'], $imgext );
                $sizeCorrect = $infoCorrect && ($info['width'] > $width || $info['height'] > $height);
                if ( $infoCorrect && $sizeCorrect ) {
                    $returnUrl = self::makeThumb( $attach, $width, $height );
                } else {
                    $returnUrl = $file;
                }
            } else {
                $returnUrl = File::fileName( $file );
            }
            return $returnUrl;
        }
    }

    /**
     * 获取缩略图的名称
     * @param array $attach 附件数组
     * @param integer $width 缩略图之宽
     * @param integer $height 缩略图之高
     * @return string 缩略图地址
     */
    public static function getThumbName( $attach, $width, $height ) {
        $attachUrl = File::getAttachUrl();
        list($module, $year, $day, $name) = explode( '/', $attach['attachment'] );
        $thumbName = sprintf( "%s/%s/%s/%s/%dX%d.%s", $attachUrl, $module, $year, $day, $width, $height, $name );
        return $thumbName;
    }

    /**
     * 生成缩略图
     * @param array $attach 附件数组
     * @param integer $width 宽度
     * @param integer $height 高度
     * @return string 生成的缩略图名称
     */
    public static function makeThumb( $attach, $width, $height ) {
        $attachUrl = File::getAttachUrl();
        $file = sprintf( '%s/%s', $attachUrl, $attach['attachment'] );
        $fileext = String::getFileExt( $file );
        $thumbName = self::getThumbName( $attach, $width, $height );
        if ( LOCAL ) {
            $res = Image::thumb2( $file, $thumbName, '', $width, $height );
        } else {
            $tempFile = File::getTempPath() . 'tmp.' . $fileext;
            $orgImgname = IBOS::engine()->io()->file()->fetchTemp( File::fileName( $file ), $fileext );
            Image::thumb2( $orgImgname, $tempFile, '', $width, $height );
            File::createFile( $thumbName, file_get_contents( $tempFile ) );
        }
        return $thumbName;
    }

    /**
     * 检查一张图片是否有被调整过
     * @param string $imageName 图片地址
     * @return boolean
     */
    public static function isResize( $imageName ) {
        if ( preg_match( '/(\d*X\d*)/', $imageName ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取微博配置
     * @param string $loadcache 是否返回已在内存里的缓存值
     * @return mixed
     */
    public static function getSetting( $loadcache = false ) {
        $keys = array(
            'wbmovement', 'wbnums', 'wbpostfrequency',
            'wbposttype', 'wbwatermark', 'wbwcenabled'
        );
        $serializeKeys = array( 'wbmovement', 'wbposttype' );
        if ( $loadcache ) {
            $allkeys = array_merge( $keys, $serializeKeys );
            $setting = IBOS::app()->setting->toArray();
            $values = array();
            foreach ( $allkeys as $key ) {
                $values[$key] = $setting['setting'][$key];
            }
        } else {
            $values = Setting::model()->fetchSettingValueByKeys( implode( ',', $keys ), true, $serializeKeys );
        }
        return $values;
    }

    /**
     * 获取所有可用产生动态的模块
     * @return array 模块数组
     */
    public static function getMovementModules() {
        $modules = IBOS::app()->getEnabledModule();
        $movementModules = array();
        foreach ( $modules as $module => $configs ) {
            $config = CJSON::decode( $configs['config'], true );
            if ( isset( $config['param']['pushMovement'] ) ) {
                $movementModules[] = array( 'module' => $module, 'name' => $configs['name'] );
            }
        }
        return $movementModules;
    }

}
