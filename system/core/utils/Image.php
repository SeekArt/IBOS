<?php

/**
 * 图像操作类库文件。
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 图像操作类库，提供生成缩略图，添加水印等一系列操作
 *
 * @package application.core.utils
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$
 */

namespace application\core\utils;

class Image
{

    /**
     * 取得图像信息
     * @param string $image 图像文件名
     * @return mixed
     */
    public static function getImageInfo($img)
    {
        $imageInfo = File::imageSize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = File::fileSize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 为图片添加水印
     * @param string $source 原文件名
     * @param string $water 水印图片
     * @param string $saveName 添加水印后的图片名
     * @param integer $pos 位置，用九宫格表示
     * @param string $alpha 水印的透明度
     * @param integer $quality 图片质量
     * @return void
     */
    public static function water($source, $water, $saveName = null, $pos = 0, $alpha = 80, $quality = 100)
    {
        // 检查文件是否存在
        if (!File::fileExists($source) || !File::fileExists($water)) {
            return false;
        }
        // 图片信息
        $sInfo = self::getImageInfo($source);
        $wInfo = self::getImageInfo($water);
        // 如果图片小于水印图片，不生成图片
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height']) {
            return false;
        }
        // 建立图像
        $sCreateFunction = "imagecreatefrom" . $sInfo['type'];
        $sImage = $sCreateFunction($source);
        $wCreateFunction = "imagecreatefrom" . $wInfo['type'];
        $wImage = $wCreateFunction($water);
        // 设定图像的混色模式
        imagealphablending($wImage, true);
        list($posX, $posY) = self::getPos($sInfo, $wInfo, $pos);
        // 生成混合图像
        if ($wInfo['type'] == 'png') {
            imageCopy($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height']);
        } else {
            imageAlphaBlending($wImage, true);
            imagecopymerge($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $alpha);
        }

        //输出图像
        $imageFun = 'image' . $sInfo['type'];
        //如果没有给出保存文件名，默认为原图像名
        if (!$saveName) {
            $saveName = $source;
            @unlink($source);
        }
        //保存图像
        if ($sInfo['mime'] == 'image/jpeg') {
            $imageFun($sImage, $saveName, $quality);
        } else {
            $imageFun($sImage, $saveName);
        }
        imagedestroy($sImage);
        return true;
    }

    /**
     * 生成缩略图
     * @param string $image 原图
     * @param string $thumbName 缩略图文件名
     * @param string $maxWidth 宽度
     * @param string $maxHeight 高度
     * @param integer $quality 图像质量
     * @param string $type 图像格式
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    public static function thumb($image, $thumbName, $maxWidth = 200, $maxHeight = 50, $quality = 100, $type = '', $interlace = true)
    {
        // 获取原图信息
        $info = self::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $mime = $info['mime'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int)($srcWidth * $scale);
                $height = (int)($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if (!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
                $thumbImg = imagecreatetruecolor($width, $height);
            } else {
                $thumbImg = imagecreate($width, $height);
            }
            // png和gif的透明处理
            if ('png' == $type) {
                imagealphablending($thumbImg, false); //取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($thumbImg, true); //设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
            } elseif ('gif' == $type) {
                $trnprt_indx = imagecolortransparent($srcImg);
                if ($trnprt_indx >= 0) {
                    //its transparent
                    $trnprt_color = imagecolorsforindex($srcImg, $trnprt_indx);
                    $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($thumbImg, 0, 0, $trnprt_indx);
                    imagecolortransparent($thumbImg, $trnprt_indx);
                }
            }
            // 复制图片
            if (function_exists("ImageCopyResampled")) {
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            } else {
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type) {
                imageinterlace($thumbImg, $interlace);
            }

            // 生成图片
            $imageFunc = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            if ($mime == 'image/jpeg') {
                $imageFunc($thumbImg, $thumbName, $quality);
            } else {
                $imageFunc($thumbImg, $thumbName);
            }
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbName;
        }
        return false;
    }

    /**
     * 生成特定尺寸缩略图 解决原版缩略图不能满足特定尺寸的问题 PS：会裁掉图片不符合缩略图比例的部分
     * @param string $image 原图
     * @param string $type 图像格式
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth 宽度
     * @param string $maxHeight 高度
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    public static function thumb2($image, $thumbname, $type = '', $maxWidth = 200, $maxHeight = 50, $interlace = true)
    {
        // 获取原图信息
        $info = self::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = max($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            //判断原图和缩略图比例 如原图宽于缩略图则裁掉两边 反之..
            if ($maxWidth / $srcWidth > $maxHeight / $srcHeight) {
                //高于
                $srcX = 0;
                $srcY = ($srcHeight - $maxHeight / $scale) / 2;
                $cutWidth = $srcWidth;
                $cutHeight = $maxHeight / $scale;
            } else {
                //宽于
                $srcX = ($srcWidth - $maxWidth / $scale) / 2;
                $srcY = 0;
                $cutWidth = $maxWidth / $scale;
                $cutHeight = $srcHeight;
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
                $thumbImg = imagecreatetruecolor($maxWidth, $maxHeight);
            } else {
                $thumbImg = imagecreate($maxWidth, $maxHeight);
            }

            // 复制图片
            if (function_exists("ImageCopyResampled")) {
                imagecopyresampled($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $maxWidth, $maxHeight, $cutWidth, $cutHeight);
            } else {
                imagecopyresized($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $maxWidth, $maxHeight, $cutWidth, $cutHeight);
            }
            if ('gif' == $type || 'png' == $type) {
                //imagealphablending($thumbImg, false);//取消默认的混色模式
                //imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color = imagecolorallocate($thumbImg, 0, 255, 0);  //  指派一个绿色
                imagecolortransparent($thumbImg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type) {
                imageinterlace($thumbImg, $interlace);
            }

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }

    /**
     * 根据给定的字符串生成水印
     * @param string $string 字符串
     * @param integer $size 大小，像素表示
     * @param string $source 原图位置
     * @param string $saveName 保存的名称，如果为空将替换原图
     * @param integer $pos 水印的位置
     * @param integer $quality 生成图片的质量
     * @param array $rgb 颜色值
     * @param string $fontpath 字体路径
     * @return void
     */
    public static function waterMarkString($string, $size, $source, $saveName = null, $pos = 0, $quality = 100, $rgb = array(), $fontPath = '')
    {
        $sInfo = self::getImageInfo($source);
        switch ($sInfo['type']) {
            case 'jpg':
            case 'jpeg':
                $createFun = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
                $imageFunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
                break;
            case 'gif':
                $createFun = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
                $imageFunc = function_exists('imagegif') ? 'imagegif' : '';
                break;
            case 'png':
                $createFun = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
                $imageFunc = function_exists('imagepng') ? 'imagepng' : '';
                break;
        }
        $im = $createFun($source);
        // -----------
        // todo :: 以下为可扩展选项
        //角度制表示的角度，0 度为从左向右读的文本。
        $angle = 0;
        // -----------
        $box = imagettfbbox($size, $angle, $fontPath, $string);
        $wInfo['height'] = max($box[1], $box[3]) - min($box[5], $box[7]);
        $wInfo['width'] = max($box[2], $box[4]) - min($box[0], $box[6]);
        $ax = min($box[0], $box[6]) * -1;
        $ay = min($box[5], $box[7]) * -1;
        list($posX, $posY) = self::getPos($sInfo, $wInfo, $pos);
        if ($sInfo['mime'] != 'image/png') {
            $colorPhoto = imagecreatetruecolor($sInfo['width'], $sInfo['height']);
        }
        imagealphablending($im, true);
        imagesavealpha($im, true);
        if ($sInfo['mime'] != 'image/png') {
            imageCopy($colorPhoto, $im, 0, 0, 0, 0, $sInfo['width'], $sInfo['height']);
            $im = $colorPhoto;
        }
        $color = imagecolorallocate($im, $rgb['r'], $rgb['g'], $rgb['b']);
        imagettftext($im, $size, 0, $posX + $ax, $posY + $ay, $color, $fontPath, $string);
        clearstatcache();
        //如果没有给出保存文件名，默认为原图像名
        if (!$saveName) {
            $saveName = $source;
            @unlink($source);
        }
        if ($sInfo['mime'] == 'image/jpeg') {
            $imageFunc($im, $saveName, $quality);
        } else {
            $imageFunc($im, $saveName);
        }
    }

    /**
     * 获取水印位置
     * @param array $sInfo 原图宽高数组。
     * @param array $wInfo 水印图宽高数组
     * @param integer $pos 位置代码
     * @return array 水印位置的坐标
     */
    private static function getPos($sInfo, $wInfo, $pos = 9)
    {
        // 水印位置
        switch ($pos) {
            case 1://1为顶端居左
                $posX = 5;
                $posY = 5;
                break;
            case 2://2为顶端居中
                $posX = ($sInfo['width'] - $wInfo["width"]) / 2;
                $posY = 5;
                break;
            case 3://3为顶端居右
                $posX = $sInfo['width'] - $wInfo["width"] - 5;
                $posY = 5;
                break;
            case 4://4为中部居左
                $posX = 5;
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 5://5为中部居中
                $posX = ($sInfo['width'] - $wInfo["width"]) / 2;
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 6://6为中部居右
                $posX = $sInfo['width'] - $wInfo["width"];
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 7://7为底端居左
                $posX = 5;
                $posY = $sInfo['height'] - $wInfo['height'] - 5;
                break;
            case 8://8为底端居中
                $posX = ($sInfo['width'] - $wInfo["width"]) / 2;
                $posY = $sInfo['height'] - $wInfo['height'] - 5;
                break;
            case 9://9为底端居右
            default:
                $posX = $sInfo["width"] - $wInfo["width"] - 5;
                $posY = $sInfo["height"] - $wInfo["height"] - 5;
                break;
        }
        return array($posX, $posY);
    }

}
